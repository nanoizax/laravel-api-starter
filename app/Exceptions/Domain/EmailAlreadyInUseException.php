<?php

namespace App\Exceptions\Domain;

class EmailAlreadyInUseException extends \RuntimeException
{
    public function __construct(string $email = '')
    {
        parent::__construct($email ? "Email already in use: {$email}" : 'Email already in use');
    }
}
