<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="uniqEmail", columns={"email"}),
 *     },
 * )
 * @ORM\Entity()
 */
class User implements UserInterface
{
    use IdAwareEntityTrait;
    use TimestampableEntity;

    /**
     * @ORM\Column(type="string")
     */
    private string $email;

    /**
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="simple_array")
     */
    private array $roles = ['ROLE_USER'];

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="user")
     */
    private Collection $locationList;

    /**
     * @ORM\Column(type="string")
     */
    private string $locale;

    public function __construct()
    {
        $this->locationList = new ArrayCollection();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }

    public function getLocationList(): Collection
    {
        return $this->locationList;
    }

    public function setLocationList(Collection $locationList): void
    {
        $this->locationList = $locationList;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
