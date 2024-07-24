<?php
namespace App\Service;

class AuthKeyService
{
    public function generate(): string
    {
        return substr(bin2hex(random_bytes(128)), 0, 255);
    }
}