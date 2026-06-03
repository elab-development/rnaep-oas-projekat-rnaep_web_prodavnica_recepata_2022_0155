<?php

namespace App\Infrastructure;

use Illuminate\Support\Facades\Hash;

class PasswordHasher
{
    public function hash(string $plainText): string
    {
        return bcrypt($plainText);
    }

    public function check(string $plainText, string $hashed): bool
    {
        return Hash::check($plainText, $hashed);
    }
}