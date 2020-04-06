<?php

namespace App\Presentation\Api\Rest\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Nyholm\Psr7\Response as Psr7Response;

final class AuthController
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var AuthCodeGrant
     */
    protected $authCodeGrant;

    /**
     * @var PasswordGrant
     */
    private $passwordGrant;

    /**
     * @var RefreshTokenGrant
     */
    protected $refreshTokenGrant;

    /**
     * AuthController constructor.
     *
     * @param AuthorizationServer $authorizationServer
     * @param RefreshTokenGrant $refreshTokenGrant
     * @param AuthCodeGrant $authCodeGrant
     * @param PasswordGrant $passwordGrant
     */
    public function __construct(AuthorizationServer $authorizationServer, RefreshTokenGrant $refreshTokenGrant, AuthCodeGrant $authCodeGrant, PasswordGrant $passwordGrant)
    {
        $this->authorizationServer = $authorizationServer;
        $this->authCodeGrant = $authCodeGrant;
        $this->passwordGrant = $passwordGrant;
        $this->refreshTokenGrant = $refreshTokenGrant;
    }

    /**
     * @Route("accessToken", name="api_get_access_token", methods={"POST"})
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function accessTokenAction(Request $request): Response
    {
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrRequest = $psrHttpFactory->createRequest($request)->withAttribute('client_id', $request->query->getAlnum('client_id', null));
        $psrResponse = $this->getAccessToken($psrRequest);
        if (null !== $psrResponse) {
            $httpFoundationFactory = new HttpFoundationFactory();
            return $httpFoundationFactory->createResponse($psrResponse);
        }
        throw new BadRequestHttpException();
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Psr7Response|null
     * @throws \Exception
     */
    protected function getAccessToken(ServerRequestInterface $request): ?Psr7Response
    {
        $this->passwordGrant->setRefreshTokenTTL(new \DateInterval('P1M'));

        return $this->withErrorHandling(function() use ($request) {
            $this->passwordGrant->setRefreshTokenTTL(new \DateInterval('P1M'));
            $this->authorizationServer->enableGrantType($this->authCodeGrant, new \DateInterval('PT1H'));
            $this->authorizationServer->enableGrantType($this->passwordGrant, new \DateInterval('PT1H'));
            $this->authorizationServer->enableGrantType($this->refreshTokenGrant, new \DateInterval('PT1H'));
            return $this->authorizationServer->respondToAccessTokenRequest($request, new Psr7Response());
        });
    }

    private function withErrorHandling($callback): ?Psr7Response
    {
        try {
            return $callback();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (OAuthServerException $e) {
            return $this->convertResponse($e->generateHttpResponse(new Psr7Response()));
        } catch (\Exception $e) {
            return new Psr7Response(Response::HTTP_INTERNAL_SERVER_ERROR, [], $e->getMessage());
        } catch (\Throwable $e) {
            return new Psr7Response(Response::HTTP_INTERNAL_SERVER_ERROR, [], $e->getMessage());
        }
    }

    private function convertResponse(ResponseInterface $psrResponse): Psr7Response
    {
        return new Psr7Response($psrResponse->getStatusCode(), $psrResponse->getHeaders(), $psrResponse->getBody());
    }
}