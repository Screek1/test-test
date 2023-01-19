<?php

namespace App\Entity;

use App\Repository\CityPhoneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CityPhoneRepository::class)
 * @ORM\Table(name="city_phone")
 */
class CityPhone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $country;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $city;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $state_or_province;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $phone;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStateOrProvince(): ?string
    {
        return $this->state_or_province;
    }

    public function setStateOrProvince(string $state_or_province): self
    {
        $this->state_or_province = $state_or_province;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
