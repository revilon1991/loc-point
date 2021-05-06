<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Location;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PointFormType extends AbstractType
{
    public function __construct(
        private RouterInterface $router,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $action = $this->router->generate('app_map_addpoint');

        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            $labelSaveText = 'Save';
            $fieldNameIsRequired = true;
        } else {
            $labelSaveText = 'Please sign in';
            $fieldNameIsRequired = false;
        }

        $builder
            ->setMethod('POST')
            ->setAction($action)
            ->add('name', TextType::class, [
                'label' => false,
                'required' => $fieldNameIsRequired,
                'attr' => [
                    'placeholder' => 'Name',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Ground' => 'ground',
                    'Village' => 'village',
                    'City' => 'city',
                ],
            ])
            ->add('description', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description',
                ],
            ])
            ->add('eventDateFrom', DateType::class, [
                'widget' => 'single_text',
                'label' => false,
                'required' => false,
                'attr' => [
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'yyyy/mm/dd',
                    'placeholder' => 'Event date from',
                ],
            ])
            ->add('eventDateTo', DateType::class, [
                'widget' => 'single_text',
                'label' => false,
                'required' => false,
                'attr' => [
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'yyyy/mm/dd',
                    'placeholder' => 'Event date to',
                ],
            ])
            ->add('latitude', HiddenType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Latitude',
                    'readonly' => 'readonly',
                ],
            ])
            ->add('longitude', HiddenType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Longitude',
                    'readonly' => 'readonly',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => $labelSaveText,
                'attr' => [
                    'class' => 'btn-primary btn-block',
                ]
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
