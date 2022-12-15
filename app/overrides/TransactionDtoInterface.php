<?php

declare(strict_types=1);

namespace App\overrides;

use DateTimeImmutable;

interface TransactionDtoInterface
{
    public function getUuid(): string;

    public function getPayableType(): string;

    public function getPayableId(): string; //20220714 edit

    public function getWalletId(): int;

    public function getType(): string;

    public function getAmount(): string;

    public function isConfirmed(): bool;

    public function getMeta(): ?array;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;
}
