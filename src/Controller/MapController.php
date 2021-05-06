<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Location;
use App\Entity\User;
use App\Form\Type\PointFormType;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function json_decode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class MapController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $yandexApiKey,
    ) {
    }

    #[Route('/', methods: ['GET'])]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('app_map_map');
    }

    #[Route('/map', methods: ['GET'])]
    public function map(): Response
    {
        $location = new Location();

        $form = $this->createForm(PointFormType::class, $location);

        /** @var User $user */
        $user = $this->getUser();

        $sql = '
            select :county_key, count(distinct countryCode)
            from Location l
            union
            select type, count(*)
            from Location l
            where userId = :user_id
            group by type
        ';

        $stmt = $this->entityManager->getConnection()->executeQuery($sql, [
            'county_key' => 'county',
            'user_id' => $user->getId(),
        ]);

        $visiting = $stmt->fetchAllKeyValue();

        return $this->render('map.html.twig', [
            'form' => $form->createView(),
            'visiting' => $visiting,
        ]);
    }

    /**
     * @throws JsonException
     */
    #[Route('/point', methods: ['POST'])]
    public function addPoint(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_security_login');
        }

        $form = $this->createForm(PointFormType::class);
        $form->handleRequest($request);

        /** @var Location $location */
        $location = $form->getData();
        $location->setUser($user);

        $longitude = $location->getLongitude();
        $latitude = $location->getLatitude();

        $lang = match (true) {
            $user->getLocale() === 'ru' => 'ru_RU',
            $user->getLocale() === 'tr' => 'tr_TR',
            $user->getLocale() === 'be' => 'be_BY',
            $user->getLocale() === 'uk' => 'uk_UA',
            default => 'en_US',
        };

        $response = $this->httpClient->request('GET', 'https://geocode-maps.yandex.ru/1.x', [
            RequestOptions::QUERY => [
                'apikey' => $this->yandexApiKey,
                'geocode' => "$latitude,$longitude",
                'sco' => 'latlong',
                'kind' => 'locality',
                'format' => 'json',
                'lang' => $lang,
            ],
        ]);

        $response = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $geoObject = $response['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'] ?? null;
        $countryNameCode = $geoObject['metaDataProperty']['GeocoderMetaData']['Address']['country_code'] ?? null;
        $components = $geoObject['metaDataProperty']['GeocoderMetaData']['Address']['Components'] ?? [];
        $localityName = null;
        foreach ($components as $component) {
            if ($component['kind'] === 'locality') {
                $localityName = $component['name'];
            }
        }

        $location->setCountryCode($countryNameCode);
        $location->setLocality($localityName);

        $this->entityManager->persist($location);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_map_index');
    }

    /**
     * @throws JsonException
     */
    #[Route('/pointList', methods: ['GET'])]
    public function getPointList(): Response
    {
        $user = $this->getUser();

        $locationRepository = $this->entityManager->getRepository(Location::class);

        /** @var Location[] $locationList */
        $locationList = $locationRepository->findBy([
            'user' => $user,
        ]);

        $result = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($locationList as $location) {
            if (!$location->getEventDateFrom() && !$location->getEventDateTo()) {
                $balloonContentFooter = null;
            } else {
                $balloonContentFooter = sprintf(
                    '%s - %s',
                    $location->getEventDateFrom() ? $location->getEventDateFrom()->format('d.m.Y') : '?',
                    $location->getEventDateTo() ? $location->getEventDateTo()->format('d.m.Y') : '?',
                );
            }

            $result['features'][] = [
                'type' => 'Feature',
                'id' => (int) $location->getId(),
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $location->getLatitude(),
                        (float) $location->getLongitude(),
                    ],
                ],
                'properties' => [
                    'balloonContentHeader' => $location->getName(),
                    'balloonContentBody' => $location->getDescription(),
                    'balloonContentFooter' => $balloonContentFooter,
                    'clusterCaption' => $location->getName(),
                    'hintContent' => $location->getName(),
                ],
            ];
        }

        return $this->json($result);
    }

    #[Route('/coordinatesByIp', methods: ['GET'])]
    public function coordinatesByIp(Request $request): JsonResponse
    {
        try {
            $response = $this->httpClient->request('GET', sprintf(
                'http://ip-api.com/json/%s',
                $request->getClientIp()
            ));

            $response = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            $latitude = $response['lat'];
            $longitude = $response['lon'];

            return new JsonResponse([
                'center' => [$latitude, $longitude],
                'zoom' => 9,
            ]);
        } catch (Throwable $exception) {
            $this->logger->critical(__METHOD__, [$exception]);
        }

        return new JsonResponse([
            'center' => [0, 0],
            'zoom' => 2,
        ]);
    }
}
