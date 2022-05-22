<?php

namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class userDTO
{
    /**
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
    //...
}