<?php

namespace App\Presentation\Api\Rest\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nyholm\Psr7\Response as Psr7Response;

final class AuthController
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * When remote app requests auth code with redirect url, redirects user to login, then gets access token for the authorization code.
     * This is the classic oauth process.
     *
     * grant_type=authorization_code
     *
     * @var AuthCodeGrant
     */
    protected $authCodeGrant;

    /**
     * When remote app wants access token for username/password in one step. This is more simple process, but much less secure.
     *
     * grant_type=password
     *
     * @var PasswordGrant
     */
    private $passwordGrant;

    /**
     * When remote app wants access token for client_id/client_secret. This authenticates backend <-> backend service accounts.
     *
     * grant_type=client_credentials
     *
     * @var ClientCredentialsGrant
     */
    protected $clientCredentialsGrant;

    /**
     * When remote app wants refresh the expiring access token
     *
     * @var RefreshTokenGrant
     */
    protected $refreshTokenGrant;

    /**
     * @var HttpMessageFactoryInterface
     */
    private $psrHttpFactory;

    /**
     * @var HttpFoundationFactoryInterface
     */
    private $httpFoundationFactory;

    /**
     * AuthController constructor.
     *
     * @param AuthorizationServer $authorizationServer
     * @param AuthCodeGrant $authCodeGrant
     * @param PasswordGrant $passwordGrant
     * @param ClientCredentialsGrant $clientCredentialsGrant
     * @param RefreshTokenGrant $refreshTokenGrant
     */
    public function __construct(AuthorizationServer $authorizationServer, AuthCodeGrant $authCodeGrant, PasswordGrant $passwordGrant, ClientCredentialsGrant $clientCredentialsGrant, RefreshTokenGrant $refreshTokenGrant)
    {
        $this->authorizationServer = $authorizationServer;
        $this->authCodeGrant = $authCodeGrant;
        $this->passwordGrant = $passwordGrant;
        $this->clientCredentialsGrant = $clientCredentialsGrant;
        $this->refreshTokenGrant = $refreshTokenGrant;
    }

    protected function getPsrHttpFactory(): HttpMessageFactoryInterface {
        if ($this->psrHttpFactory === null) {
            $psr17Factory = new Psr17Factory();
            $this->psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        }
        return $this->psrHttpFactory;
    }

    protected function getHttpFoundationFactory(): HttpFoundationFactoryInterface {
        if ($this->httpFoundationFactory === null) {
            $this->httpFoundationFactory = new HttpFoundationFactory();
        }
        return $this->httpFoundationFactory;
    }


    /**
     * Query params
     * ------------
     * required: response_type=code, client_id, scope(=*)
     * optional: redirect_uri, state
     *
     * @Route("authCode", name="api_get_auth_code", methods={"GET"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function authCodeAction(Request $request): Response
    {
        $psrRequest = $this->getPsrHttpFactory()->createRequest($request);
        $psrResponse = $this->withErrorHandling(function() use ($psrRequest) {
            $this->authorizationServer->enableGrantType($this->authCodeGrant, new \DateInterval('PT1H'));
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($psrRequest);
            return $this->authorizationServer->completeAuthorizationRequest($authRequest, new Psr7Response());
        });
        return $this->getHttpFoundationFactory()->createResponse($psrResponse);
    }

    /**
     * Post data
     * ------------
     * required: [authorization headers or client_id+client_secret], code
     * optional: code_verifier
     *
     * @Route("accessToken", name="api_get_access_token", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function accessTokenAction(Request $request): Response
    {
        $psrRequest = $this->getPsrHttpFactory()->createRequest($request)->withAttribute('client_id', $request->get('client_id'));
        $psrResponse = $this->withErrorHandling(function() use ($psrRequest) {
            $this->authorizationServer->enableGrantType($this->authCodeGrant, new \DateInterval('PT1H'));
            $this->authorizationServer->enableGrantType($this->passwordGrant, new \DateInterval('PT1H'));
            $this->authorizationServer->enableGrantType($this->clientCredentialsGrant, new \DateInterval('PT1H'));
            $this->authorizationServer->enableGrantType($this->refreshTokenGrant, new \DateInterval('PT1H'));
            return $this->authorizationServer->respondToAccessTokenRequest($psrRequest, new Psr7Response());
        });
        return $this->getHttpFoundationFactory()->createResponse($psrResponse);
    }

    private function withErrorHandling($callback): Psr7Response
    {
        try {
            $response = $callback();
            if (!$response instanceof Psr7Response) {
                throw new \BadMethodCallException('No valid psr7 response generated.');
            }
            return $response;
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