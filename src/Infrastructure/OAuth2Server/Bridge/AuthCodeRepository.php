<?php

namespace App\Infrastructure\OAuth2Server\Bridge;

use App\Domain\Model\AuthCodeId;
use App\Domain\Model\ClientId;
use App\Domain\Repository\AuthCodeRepositoryInterface as AppAuthCodeRepositoryInterface;
use App\Domain\Repository\ClientRepositoryInterface as AppClientRepositoryInterface;
use App\Domain\Model\AuthCode as AppAuthCode;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * @var AppAuthCodeRepositoryInterface
     */
    private $appAuthCodeRepository;

    /**
     * @var AppClientRepositoryInterface
     */
    private $appClientRepository;

    /**
     * ClientRepository constructor.
     *
     * @param AppAuthCodeRepositoryInterface $appAuthCodeRepository
     * @param AppClientRepositoryInterface $appClientRepository
     */
    public function __construct(AppAuthCodeRepositoryInterface $appAuthCodeRepository, AppClientRepositoryInterface $appClientRepository)
    {
        $this->appAuthCodeRepository = $appAuthCodeRepository;
        $this->appClientRepository = $appClientRepository;
    }

    /**
     * @inheritDoc
     */
    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCode();
    }

    /**
     * @inheritDoc
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $appAuthCode = new AppAuthCode(
            AuthCodeId::fromString($authCodeEntity->getIdentifier()),
            ClientId::fromString($authCodeEntity->getClient()->getIdentifier()),
            false,
            new \DateTime(),
            new \DateTime(),
            $authCodeEntity->getExpiryDateTime()
        );
        $this->appAuthCodeRepository->save($appAuthCode);
    }

    /**
     * @inheritDoc
     */
    public function revokeAuthCode($codeId)
    {
        $authCodePersistEntity = $this->appAuthCodeRepository->find($codeId);
        if ($authCodePersistEntity === null) {
            return;
        }
        $authCodePersistEntity->revoke();
        $this->appAuthCodeRepository->save($authCodePersistEntity);
    }

    /**
     * @inheritDoc
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        $authCodePersistEntity = $this->appAuthCodeRepository->find($codeId);
        if ($authCodePersistEntity === null || $authCodePersistEntity->isRevoked()) {
            return true;
        }
        return (bool)$this->appClientRepository->findActive($authCodePersistEntity->getClientId());
    }
}
