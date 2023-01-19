<?php


namespace App\Model;


use Symfony\Component\Validator\Constraints as Assert;

class UserModel
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


}