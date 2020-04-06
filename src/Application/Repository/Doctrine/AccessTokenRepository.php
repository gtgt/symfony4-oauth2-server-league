<?php

namespace App\Application\Repository\Doctrine;

use App\Domain\Model\AccessToken;
use App\Domain\Repository\AccessTokenRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    private const ENTITY = AccessToken::class;

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

    public function find(string $accessTokenId): ?AccessToken
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->entityRepository->find($accessTokenId);
    }

    public function save(AccessToken $accessToken): void
    {
        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();
    }
}