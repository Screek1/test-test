<?php


namespace App\Model;


use Symfony\Component\Validator\Constraints as Assert;

class ViewingRequestModel
{

    /**
     * @Assert\NotBlank
     */
    public string $name;

    /**
     * @Assert\NotBlank
     * @Assert\Email
     */
    public string $email;

    /**
     * @Assert\NotBlank
     */
    public string $phone;

    /**
     * @Assert\PositiveOrZero
     */
    public bool $agree;

    /**
     * @Assert\NotBlank
     * @Assert\Positive
     */
    public int $listingId;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return bool
     */
    public function isAgree(): bool
    {
        return $this->agree;
    }

    /**
     * @param bool $agree
     */
    public function setAgree(bool $agree): void
    {
        $this->agree = $agree;
    }

    /**
     * @return int
     */
    public function getListingId(): int
    {
        return $this->listingId;
    }

    /**
     * @param int $listingId
     */
    public function setListingId(int $listingId): void
    {
        $this->listingId = $listingId;
    }


}