<?php

namespace App\Entity;

use App\Repository\PriceLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PriceLogRepository::class)
 * @ORM\Table(name="price_log",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="price_log_listing_id_date_idx", columns={"listing_id", "date"})
 *     },
 *     indexes={
 *          @ORM\Index(name="price_log_listing_id_idx", columns={"listing_id"}),
 *     }
 * )
 */
class PriceLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Listing::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $listing;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $cityAverage;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $subdivisionAverage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getListing(): ?Listing
    {
        return $this->listing;
    }

    public function setListing(?Listing $listing): self
    {
        $this->listing = $listing;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCityAverage(): ?float
    {
        return $this->cityAverage;
    }

    public function setCityAverage(?float $cityAverage): self
    {
        $this->cityAverage = $cityAverage;

        return $this;
    }

    public function getSubdivisionAverage(): ?float
    {
        return $this->subdivisionAverage;
    }

    public function setSubdivisionAverage(?float $subdivisionAverage): self
    {
        $this->subdivisionAverage = $subdivisionAverage;

        return $this;
    }
}
