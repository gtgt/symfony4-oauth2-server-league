<?php

namespace App\Infrastructure\OAuth2Server\Bridge;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface as AppUserRepositoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserRepository implements UserRepositoryInterface
{
    /**
     * @var AppUserRepositoryInterface
     */
    private $appUserRepository;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    /**
     * UserRepository constructor.
     * @param AppUserRepositoryInterface $appUserRepository
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(
        AppUserRepositoryInterface $appUserRepository,
        UserPasswordEncoderInterface $userPasswordEncoder
    ) {
        $this->appUserRepository = $appUserRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        $appUser = $this->appUserRepository->findOneByEmail($username);
        if ($appUser === null) {
            return null;
        }

        $encodedPassword = $this->userPasswordEncoder->encodePassword($appUser, $password);
        $isPasswordValid = $this->userPasswordEncoder->isPasswordValid($appUser, $encodedPassword);
        if ($isPasswordValid) {
            return null;
        }

        return new User($appUser->getId()->toString());
    }
}
