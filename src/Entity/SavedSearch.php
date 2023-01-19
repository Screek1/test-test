<?php

namespace App\Entity;

use App\Model\SavedSearch\SavedSearchFrequency;
use App\Repository\SavedSearch\SavedSearchRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SavedSearchRepository::class)
 * @ORM\Table(name="saved_search",
 *     indexes={
 *          @ORM\Index(name="saved_search_user_id_idx", columns={"user_id"})
 *     }
 * )
 */
class SavedSearch
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastRun;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="savedSearches")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     */
    private $frequency = SavedSearchFrequency::None;

    /**
     * @ORM\Column(type="json", nullable=false, options={"jsonb":true})
     */
    private $criteria = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastRun(): ?\DateTimeInterface
    {
        return $this->lastRun;
    }

    public function setLastRun(?\DateTimeInterface $lastRun): self
    {
        $this->lastRun = $lastRun;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getCriteria(): ?array
    {
        return $this->criteria;
    }

    public function setCriteria(array $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }
}
