<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class Location
{
    use IdAwareEntityTrait;
    use TimestampableEntity;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=16)
     */
    private string $latitude;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=16)
     */
    private string $longitude;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?DateTime $eventDateFrom = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?DateTime $eventDateTo = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $countryCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $locality;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="locationList")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getEventDateFrom(): ?DateTime
    {
        return $this->eventDateFrom;
    }

    public function setEventDateFrom(?DateTime $eventDateFrom): void
    {
        $this->eventDateFrom = $eventDateFrom;
    }

    public function getEventDateTo(): ?DateTime
    {
        return $this->eventDateTo;
    }

    public function setEventDateTo(?DateTime $eventDateTo): void
    {
        $this->eventDateTo = $eventDateTo;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(?string $locality): void
    {
        $this->locality = $locality;
    }
}
