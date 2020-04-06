<?php
namespace App\Domain\Model;

use Assert\Assertion;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractId
{
    /**
     * @var UuidInterface
     */
    private $uuid;

    public function __construct(UuidInterface $uuid)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        Assertion::uuid($uuid);
        $this->uuid = $uuid;
    }

    public static function fromString(string $id): self
    {
        return new static(Uuid::fromString($id));
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }

    public function equals($other): bool
    {
        return $other instanceof self && $this->uuid->equals($other->uuid);
    }
}