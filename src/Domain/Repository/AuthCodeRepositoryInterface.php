<?php

namespace App\Domain\Repository;

use App\Domain\Model\AuthCode;

interface AuthCodeRepositoryInterface
{
    public function find(string $codeId): ?AuthCode;

    public function save(AuthCode $accessToken): void;
}