<?php

namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

class userDTO
{
    /**
     * @OA\Schema(
     * title="UserDTO",
     * description="UserDTO"
     * )
     *
     * @Serializer\Type("string")
     * @Assert\Email(message="Email address {{ value }} is not valid")
     */
    public $username;

    /**
     * @Serializer\Type("string")
     * @Assert\Length(
     *     min="6",
     *     minMessage="Your password must be at least {{ limit }} characters",
     * )
     * @Assert\NotBlank()
     */
    public $password;
    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(
     *         type="string"
     *     ),
     *     title="Roles",
     *     description="Roles"
     * )
     * @Serializer\Type("array")
     */
    public $roles = [];

    /**
     * @OA\Property(
     *     type="float",
     *     title="Balance",
     *     description="Balance"
     * )
     * @Serializer\Type("float")
     */
    public $balance;

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function setPassword($password): void
    {
        $this->password = $password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
        $this->email = $username;
    }
}