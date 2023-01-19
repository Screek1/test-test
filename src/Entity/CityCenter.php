<?php


namespace App\Entity;

use App\Repository\CityCenterRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CityCenterRepository::class)
 * @ORM\Table(name="city_center")
 */
class CityCenter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $stateOrProvince;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private float $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private float $longitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private float $zoom;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getStateOrProvince(): string
    {
        return $this->stateOrProvince;
    }

    public function setStateOrProvince(string $stateOrProvince): void
    {
        $this->stateOrProvince = $stateOrProvince;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude($latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude($longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getZoom(): float
    {
        return $this->zoom;
    }

    public function setZoom(int $zoom): void
    {
        $this->zoom = $zoom;
    }
}