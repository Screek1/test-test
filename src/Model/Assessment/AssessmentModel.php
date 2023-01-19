<?php


namespace App\Model\Assessment;


use Symfony\Component\Validator\Constraints as Assert;

class AssessmentModel
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


    public string $selectedAddress;

    public string $userSearch;

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
     * @return string
     */
    public function getSelectedAddress(): string
    {
        return $this->selectedAddress;
    }

    /**
     * @param string $selectedAddress
     */
    public function setSelectedAddress(string $selectedAddress): void
    {
        $this->selectedAddress = $selectedAddress;
    }

    /**
     * @return string
     */
    public function getUserSearch(): string
    {
        return $this->userSearch;
    }

    /**
     * @param string $userSearch
     */
    public function setUserSearch(string $userSearch): void
    {
        $this->userSearch = $userSearch;
    }

}