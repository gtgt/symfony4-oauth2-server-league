<?php

namespace App\Domain\Model;

class AuthCode
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $redirect;

    /**
     * @var bool
     */
    private $revoked;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     */
    private $updatedAt;

    /**
     * @var \DateTimeInterface
     */
    private $expiresAt;

    /**
     * Client constructor.
     *
     * @param AuthCodeId $authCodeId
     * @param ClientId $clientId
     * @param bool $revoked
     * @param \DateTimeInterface $createdAt
     * @param \DateTimeInterface $updatedAt
     * @param \DateTimeInterface $expiresAt
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(AuthCodeId $authCodeId, ClientId $clientId, bool $revoked = false, \DateTimeInterface $createdAt = null, \DateTimeInterface $updatedAt = null, \DateTimeInterface $expiresAt = null)
    {
        $this->id = $authCodeId->toString();
        $this->clientId = $clientId->toString();
        $this->revoked = $revoked;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
        $this->expiresAt = $expiresAt;
    }

    public function getId(): AuthCodeId
    {
        return AuthCodeId::fromString($this->id);
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getRedirect(): string
    {
        return $this->redirect;
    }

    /**
     * @param string $redirect
     */
    public function setRedirect(string $redirect): void
    {
        $this->redirect = $redirect;
    }

    /**
     * @return bool
     */
    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function revoke(): void
    {
        $this->revoked = true;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface $updatedAt
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }
}