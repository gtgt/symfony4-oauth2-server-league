<?php

namespace App\Application\Repository\Doctrine;

use App\Domain\Model\AuthCode;
use App\Domain\Repository\AuthCodeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    private const ENTITY = AuthCode::class;

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

    public function find(string $accessTokenId): ?AuthCode
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->entityRepository->find($accessTokenId);
    }

    public function save(AuthCode $accessToken): void
    {
        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();
    }
}
