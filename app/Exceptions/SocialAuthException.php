<?php

namespace App\Exceptions;

use Exception;

class SocialAuthException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $status = 422,
        public readonly string $field = 'email',
    ) {
        parent::__construct($message);
    }
}
