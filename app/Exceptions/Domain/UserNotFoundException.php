<?php

namespace App\Exceptions\Domain;

class UserNotFoundException extends \RuntimeException
{
    public function __construct(string $identifier = '')
    {
        parent::__construct($identifier ? "User not found: {$identifier}" : 'User not found');
    }
}
