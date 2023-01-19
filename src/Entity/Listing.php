<?php

namespace App\Entity;

use App\Model\Listing\ListingStatus;
use App\Repository\ListingRepository;
use App\Service\Geo\Point;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity(repositoryClass=ListingRepository::class)
 * @ORM\Table(name="listing",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="listing_mls_num_feed_id_state_or_province_idx", columns={"mls_num", "feed_id", "state_or_province"}, options={"where": "((state_or_province IS NOT NULL) AND ((status)::text = 'live'::text) AND (mls_num IS NOT NULL) AND (deleted_date IS NULL))"}),
 *          @ORM\UniqueConstraint(name="listing_feed_id_feed_listing_id_idx", columns={"feed_id", "feed_listing_id"}, options={"where": "(deleted_date IS NULL)"})
 *     },
 *     indexes={
 *          @ORM\Index(name="listing_status_fee_id_idx", columns={"status", "feed_id"}, options={"where": "(deleted_date IS NULL)"}),
 *          @ORM\Index(name="listing_type_idx", columns={"type"}, options={"where": "(deleted_date IS NULL)"}),
 *          @ORM\Index(name="listing_state_or_province_idx", columns={"state_or_province"}, options={"where": "(deleted_date IS NULL)"})
 *     }
 * )
 */
class Listing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $mlsNum;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $feedListingID;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $classID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $feedID;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $listPrice;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $photosCount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $unparsedAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=20, options={"default":"new"})
     */
    private $status = 'new';

    /**
     * @ORM\Column(type="string", length=20, options={"default":"none"})
     */
    private $processingStatus = 'none';

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUpdateFromFeed;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb":true})
     */
    private $imagesData = [];

    /**
     * @ORM\Column(type="point", nullable=true)
     * @var Point
     */
    private $coordinates;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb":true})
     */
    private $rawData = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $stateOrProvince;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $ownershipType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bedrooms;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $livingArea;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lotSize;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $yearBuilt;

    /**
     * @ORM\OneToMany(targetEntity=Viewing::class, mappedBy="listing")
     */
    private $viewings;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $originalPrice;

    /**
     * @ORM\Column(type="boolean")
     */
    private $selfListing = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $contractDate;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="favoriteListings")
     *
     * @Ignore
     */
    private $users;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $streetName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $subdivision;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb":true})
     */
    private $schoolsData = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bathrooms;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $taggedForDeletion = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isManualRemoved = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $error;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $errorTrace;


    public function __construct()
    {
        $this->viewings = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMlsNum(): ?string
    {
        return $this->mlsNum;
    }

    public function setMlsNum(string $mlsNum): self
    {
        $this->mlsNum = $mlsNum;

        return $this;
    }

    public function getFeedListingID(): ?string
    {
        return $this->feedListingID;
    }

    public function setFeedListingID(string $feedListingID): self
    {
        $this->feedListingID = $feedListingID;

        return $this;
    }

    public function getFeedID(): ?string
    {
        return $this->feedID;
    }

    public function setFeedID(string $feedID): self
    {
        $this->feedID = $feedID;

        return $this;
    }

    public function getListPrice(): ?float
    {
        return $this->listPrice;
    }

    public function setListPrice(?float $listPrice): self
    {
        $this->listPrice = $listPrice;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getPhotosCount(): ?int
    {
        return $this->photosCount;
    }

    public function setPhotosCount(?int $photosCount): self
    {
        $this->photosCount = $photosCount;

        return $this;
    }

    public function getUnparsedAddress(): ?string
    {
        return rtrim($this->unparsedAddress, ',');
    }

    public function setUnparsedAddress(?string $unparsedAddress): self
    {
        $this->unparsedAddress = $unparsedAddress;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getProcessingStatus(): ?string
    {
        return $this->processingStatus;
    }

    public function setProcessingStatus(string $processingStatus): self
    {
        $this->processingStatus = $processingStatus;

        return $this;
    }

    public function getLastUpdateFromFeed(): ?\DateTimeInterface
    {
        return $this->lastUpdateFromFeed;
    }

    public function setLastUpdateFromFeed(?\DateTimeInterface $lastUpdateFromFeed): self
    {
        $this->lastUpdateFromFeed = $lastUpdateFromFeed;

        return $this;
    }

    public function getImagesData(): ?array
    {
        return $this->imagesData;
    }

    public function setImagesData(?array $imagesData): self
    {
        $this->imagesData = $imagesData;

        return $this;
    }

    public function getCoordinates(): Point
    {
        return $this->coordinates;
    }

    public function setCoordinates(Point $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public function setRawData(?array $rawData): self
    {
        $this->rawData = $rawData;

        return $this;
    }

    public function getDeletedDate(): ?\DateTimeInterface
    {
        return $this->deletedDate;
    }

    public function setDeletedDate(?\DateTimeInterface $deletedDate): self
    {
        $this->deletedDate = $deletedDate;

        return $this;
    }

    public function getStateOrProvince(): ?string
    {
        return $this->stateOrProvince;
    }

    public function setStateOrProvince(?string $stateOrProvince): self
    {
        $this->stateOrProvince = $stateOrProvince;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getFullAddress(bool $forGeoCoder = false): ?string
    {
        if ($forGeoCoder && empty($this->getUnparsedAddress())) {
            return null;
        }
        $addressArray = [];
        $unparsedAddress = rtrim($this->getUnparsedAddress(), ",");
        if (!empty($unparsedAddress)) {
            $addressArray[] = $unparsedAddress;
        }
        $city = rtrim($this->getCity(), ",");
        if (!empty($city)) {
            $addressArray[] = $city;
        }
        $stateOrProvince = rtrim($this->getStateOrProvince(), ",");
        if (!empty($stateOrProvince)) {
            $addressArray[] = $stateOrProvince;
        }
        $postalCode = rtrim($this->getPostalCode(), ",");
        if (!empty($postalCode)) {
            $addressArray[] = $postalCode;
        }
        if (count($addressArray) > 0) {
            return implode(", ", $addressArray);
        } else {
            return null;
        }
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOwnershipType(): ?string
    {
        return $this->ownershipType;
    }

    public function setOwnershipType(?string $ownershipType): self
    {
        $this->ownershipType = $ownershipType;

        return $this;
    }

    public function getBedrooms(): ?int
    {
        return $this->bedrooms;
    }

    public function setBedrooms(?int $bedrooms): self
    {
        $this->bedrooms = $bedrooms;

        return $this;
    }

    public function getLivingArea(): ?int
    {
        return $this->livingArea;
    }

    public function setLivingArea(?int $livingArea): self
    {
        $this->livingArea = $livingArea;

        return $this;
    }

    public function getLotSize(): ?int
    {
        return $this->lotSize;
    }

    public function setLotSize(?int $lotSize): self
    {
        $this->lotSize = $lotSize;

        return $this;
    }

    public function getYearBuilt(): ?int
    {
        return $this->yearBuilt;
    }

    public function setYearBuilt(?int $yearBuilt): self
    {
        $this->yearBuilt = $yearBuilt;

        return $this;
    }

    /**
     * @return Collection|Viewing[]
     */
    public function getViewings(): Collection
    {
        return $this->viewings;
    }

    public function addViewing(Viewing $viewing): self
    {
        if (!$this->viewings->contains($viewing)) {
            $this->viewings[] = $viewing;
            $viewing->setListing($this);
        }

        return $this;
    }

    public function removeViewing(Viewing $viewing): self
    {
        if ($this->viewings->removeElement($viewing)) {
            // set the owning side to null (unless already changed)
            if ($viewing->getListing() === $this) {
                $viewing->setListing(null);
            }
        }

        return $this;
    }

    public function getOriginalPrice(): ?float
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(?float $originalPrice): self
    {
        $this->originalPrice = $originalPrice;

        return $this;
    }

    public function getSelfListing(): ?bool
    {
        return $this->selfListing;
    }

    public function setSelfListing(bool $selfListing): self
    {
        $this->selfListing = $selfListing;

        return $this;
    }

    public function getContractDate(): ?\DateTimeInterface
    {
        return $this->contractDate;
    }

    public function setContractDate(?\DateTimeInterface $contractDate): self
    {
        $this->contractDate = $contractDate;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addFavoriteListing($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeFavoriteListing($this);
        }

        return $this;
    }

    public function getSubdivision(): ?string
    {
        return $this->subdivision;
    }

    public function setSubdivision(?string $subdivision): self
    {
        $this->subdivision = $subdivision;

        return $this;
    }

    public function getSchoolsData(): ?array
    {
        return $this->schoolsData;
    }

    public function setSchoolsData(?array $schoolsData): self
    {
        $this->schoolsData = $schoolsData;

        return $this;
    }

    public function getBathrooms(): ?int
    {
        return $this->bathrooms;
    }

    public function setBathrooms(?int $bathrooms): self
    {
        $this->bathrooms = $bathrooms;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTaggedForDeletion(): bool
    {
        return $this->taggedForDeletion;
    }

    /**
     * @param bool $taggedForDeletion
     * @return Listing
     */
    public function setTaggedForDeletion(bool $taggedForDeletion): Listing
    {
        $this->taggedForDeletion = $taggedForDeletion;

        return $this;
    }

    public function getListingRouteParams(): array
    {
        $fullAddress = $this->getFullAddress();
        if (empty($fullAddress)) {
            $fullAddress = '--';
        } else {
            $fullAddress = strtolower(preg_replace("/[^a-zA-Z0-9]/", "-", $fullAddress));
        }

        return [
            'id' => $this->getId(),
            'mlsNum' => $this->getMlsNum(),
            'fullAddress' => $fullAddress,
        ];
    }

    public function isActive(): bool
    {
        return in_array($this->getStatus(), ListingStatus::VisibleStatuses) && is_null($this->getDeletedDate()) && !$this->isTaggedForDeletion();
    }

    public function setStreetName(?string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function getClassID(): ?string
    {
        return $this->classID;
    }

    public function setClassID(string $classId): void
    {
        $this->classID = $classId;
    }

    public function isManualRemoved(): bool
    {
        return $this->isManualRemoved;
    }

    public function setIsManualRemoved(bool $isManualRemoved): void
    {
        $this->isManualRemoved = $isManualRemoved;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($error): void
    {
        $this->error = $error;
    }

    public function getErrorTrace()
    {
        return $this->errorTrace;
    }

    public function setErrorTrace($errorTrace): void
    {
        $this->errorTrace = $errorTrace;
    }
}