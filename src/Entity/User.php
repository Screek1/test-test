<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="user_email_idx", columns={"email"})
 *     }
 * )
 */
class User implements UserInterface
{
    public const GOOGLE_OAUTH = 'Google';
    public const FACEBOOK_OAUTH = 'Facebook';

    public const ROLE_USER = 'ROLE_USER';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $googleId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $facebookId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $oauthType;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     *
     * @Ignore
     */
    private $password;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Viewing::class, mappedBy="user")
     *
     * @Ignore
     */
    private $viewings;

    /**
     * @ORM\ManyToMany(targetEntity=Listing::class, inversedBy="users")
     * @JoinTable(name="favorite_listings")
     *
     * @Ignore
     */
    private $favoriteListings;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $postal;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $about;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={ "default": "home_buyer" })
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=SavedSearch::class, mappedBy="user")
     *
     * @Ignore
     */
    private $savedSearches;

    public function __construct()
    {
        $this->viewings = new ArrayCollection();
        $this->favoriteListings = new ArrayCollection();
        $this->savedSearches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOauthType(): ?string
    {
        return $this->oauthType;
    }

    /**
     * @return string
     */
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    /**
     * @return string
     */
    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
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
            $viewing->setUser($this);
        }

        return $this;
    }

    public function removeViewing(Viewing $viewing): self
    {
        if ($this->viewings->removeElement($viewing)) {
            // set the owning side to null (unless already changed)
            if ($viewing->getUser() === $this) {
                $viewing->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Listing[]
     */
    public function getFavoriteListings(): Collection
    {
        return $this->favoriteListings;
    }

    public function addFavoriteListing(Listing $favoriteListing): self
    {
        if (!$this->favoriteListings->contains($favoriteListing)) {
            $this->favoriteListings[] = $favoriteListing;
        }

        return $this;
    }

    public function removeFavoriteListing(Listing $favoriteListing): self
    {
        $this->favoriteListings->removeElement($favoriteListing);

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

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

    public function getPostal(): ?string
    {
        return $this->postal;
    }

    public function setPostal(?string $postal): self
    {
        $this->postal = $postal;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = $about;

        return $this;
    }

    /**
     * @return Collection|SavedSearch[]
     */
    public function getSavedSearches(): Collection
    {
        return $this->savedSearches;
    }

    public function addSavedSearch(SavedSearch $savedSearch): self
    {
        if (!$this->savedSearches->contains($savedSearch)) {
            $this->savedSearches[] = $savedSearch;
            $savedSearch->setUser($this);
        }

        return $this;
    }

    public function removeSavedSearch(SavedSearch $savedSearch): self
    {
        if ($this->savedSearches->removeElement($savedSearch)) {
            // set the owning side to null (unless already changed)
            if ($savedSearch->getUser() === $this) {
                $savedSearch->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @param string $clientId
     * @param string $email
     * @param string $username
     *
     * @return User
     */
    public static function fromGoogleRequest(
        string $clientId,
        string $email,
        string $username
    ): User
    {
        $user = new User();
        $user->googleId = $clientId;
        $user->email = $email;
        $user->name = $username;
        $user->oauthType = self::GOOGLE_OAUTH;
        $user->roles = [self::ROLE_USER];
        return $user;
    }

    /**
     * @param string $clientId
     * @param string $email
     * @param string $username
     *
     * @return User
     */
    public static function fromFacebookRequest(
        string $clientId,
        string $email,
        string $username
    ): User
    {
        $user = new User();
        $user->facebookId = $clientId;
        $user->email = $email;
        $user->name = $username;
        $user->oauthType = self::FACEBOOK_OAUTH;
        $user->roles = [self::ROLE_USER];
        return $user;
    }
}
