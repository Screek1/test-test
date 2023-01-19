<?php


namespace App\Model\ContactUs;


use Symfony\Component\Validator\Constraints as Assert;

class ContactUsModel
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
     * @Assert\NotBlank
     */
    public string $contact_method;

    /**
     * @Assert\NotBlank
     */
    public ?string $comment = '';

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

    /**
     * @return string
     */
    public function getContactMethod(): string
    {
        return $this->contact_method;
    }

    /**
     * @param string $contact_method
     */
    public function setContactMethod(string $contact_method): void
    {
        $this->contact_method = $contact_method;
    }

    /**
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
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


}