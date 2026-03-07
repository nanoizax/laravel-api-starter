<?php

namespace App\Exceptions\Domain;

class AccountDisabledException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Account is disabled.');
    }
}
