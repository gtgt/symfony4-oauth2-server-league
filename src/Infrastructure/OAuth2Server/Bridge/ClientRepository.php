<?php

namespace App\Infrastructure\OAuth2Server\Bridge;

use App\Domain\Repository\ClientRepositoryInterface as AppClientRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

final class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var AppClientRepositoryInterface
     */
    private $appClientRepository;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * ClientRepository constructor.
     * @param AppClientRepositoryInterface $appClientRepository
     */
    public function __construct(AppClientRepositoryInterface $appClientRepository, EncoderFactoryInterface $encoderFactory)
    {
        $this->appClientRepository = $appClientRepository;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientEntity(
        $clientIdentifier,
        $grantType = null,
        $clientSecret = null,
        $mustValidateSecret = true
    ): ?ClientEntityInterface {
        $appClient = $this->appClientRepository->findActive($clientIdentifier);
        if ($appClient === null) {
            return null;
        }

        $encoder = $this->encoderFactory->getEncoder($appClient);

        if ($mustValidateSecret && !$encoder->isPasswordValid($appClient->getSecret(), $clientSecret, null)) {
            return null;
        }

        return new Client($clientIdentifier, $appClient->getName(), $appClient->getRedirect());
    }
}
