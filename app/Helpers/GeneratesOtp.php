<?php

namespace App\Helpers;

use Ichtrojan\Otp\Otp;

class GeneratesOtp
{
    public static function generateOtp(string $email): string
    {
        return (new Otp)->generate($email, 'numeric', 6, 15)->token;
    }
}
