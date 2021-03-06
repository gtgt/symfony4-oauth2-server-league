<?php

namespace App\Application\Repository\Doctrine;

use App\Domain\Model\RefreshToken;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private const ENTITY = RefreshToken::class;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ObjectRepository
     */
    private $entityRepository;

    /**
     * UserRepository constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository(self::ENTITY);
    }

    public function find(string $accessTokenId): ?RefreshToken
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->entityRepository->find($accessTokenId);
    }

    public function save(RefreshToken $accessToken): void
    {
        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();
    }
}
