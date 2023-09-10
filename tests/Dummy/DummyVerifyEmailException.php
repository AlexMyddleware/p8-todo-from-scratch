<?php

namespace App\Tests\Dummy;

use Exception;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class DummyVerifyEmailException extends Exception implements VerifyEmailExceptionInterface
{
    public function getReason(): string
    {
        return 'Dummy reason for testing.';
    }

    public function __toString(): string
    {
        return 'Dummy string';
    }

}
