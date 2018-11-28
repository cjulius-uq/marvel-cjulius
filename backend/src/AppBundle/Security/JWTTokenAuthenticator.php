<?php

namespace AppBundle\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


/**
 * JWTTokenAuthenticator (Guard implementation).
 *
 * @see http://knpuniversity.com/screencast/symfony-rest4/jwt-guard-authenticator
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTTokenAuthenticator implements GuardAuthenticatorInterface
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtManager;

    /**
     * @var TokenExtractorInterface
     */
    private $tokenExtractor;

    /**
     * @var TokenStorageInterface
     */
    private $preAuthenticationTokenStorage;

    /**
     * @param JWTTokenManagerInterface $jwtManager
     * @param TokenExtractorInterface  $tokenExtractor
     */
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        TokenExtractorInterface $tokenExtractor
    ) {
        $this->jwtManager                    = $jwtManager;
        $this->tokenExtractor                = $tokenExtractor;
        $this->preAuthenticationTokenStorage = new TokenStorage();
    }

    /**
     * Returns a decoded JWT token extracted from a request.
     *
     * {@inheritdoc}
     *
     * @return PreAuthenticationJWTUserToken
     *
     * @throws InvalidTokenException If an error occur while decoding the token
     * @throws ExpiredTokenException If the request token is expired
     */
    public function getCredentials(Request $request)
    {
        $tokenExtractor = $this->getTokenExtractor();

        if (!$tokenExtractor instanceof TokenExtractorInterface) {
            throw new \RuntimeException(sprintf('Method "%s::getTokenExtractor()" must return an instance of "%s".', __CLASS__, TokenExtractorInterface::class));
        }

        if (false === ($jsonWebToken = $tokenExtractor->extract($request))) {
            return;
        }

        $preAuthToken = new PreAuthenticationJWTUserToken($jsonWebToken);

        try {
            if (!$payload = $this->jwtManager->decode($preAuthToken)) {
                throw new InvalidTokenException('Invalid JWT Token');
            }

            $preAuthToken->setPayload($payload);
        } catch (JWTDecodeFailureException $e) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                throw new ExpiredTokenException();
            }

            throw new InvalidTokenException('Invalid JWT Token', 0, $e);
        }

        return $preAuthToken;
    }

    /**
     * Returns an user object loaded from a JWT token.
     *
     * {@inheritdoc}
     *
     * @param PreAuthenticationJWTUserToken Implementation of the (Security) TokenInterface
     *
     * @throws \InvalidArgumentException If preAuthToken is not of the good type
     * @throws InvalidPayloadException   If the user identity field is not a key of the payload
     * @throws UserNotFoundException     If no user can be loaded from the given token
     */
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {
        if (!$preAuthToken instanceof PreAuthenticationJWTUserToken) {
            throw new \InvalidArgumentException(
                sprintf('The first argument of the "%s()" method must be an instance of "%s".', __METHOD__, PreAuthenticationJWTUserToken::class)
            );
        }

        $payload       = $preAuthToken->getPayload();
        $identityField = $this->jwtManager->getUserIdentityField();

        if (!isset($payload[$identityField])) {
            throw new InvalidPayloadException($identityField);
        }

        $identity = $payload[$identityField];

        try {
            $user = $this->loadUser($userProvider, $payload, $identity);
        } catch (UsernameNotFoundException $e) {
            throw new UserNotFoundException($identityField, $identity);
        }

        $this->preAuthenticationTokenStorage->setToken($preAuthToken);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $authException)
    {
        $response = new JWTAuthenticationFailureResponse($authException->getMessageKey());

        throw new BadCredentialsException("Please log in again.", Response::HTTP_UNAUTHORIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse([
            'error' => true,
            'message' => $authException->getMessage()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException If there is no pre-authenticated token previously stored
     */
    public function createAuthenticatedToken(UserInterface $user, $providerKey)
    {
        $preAuthToken = $this->preAuthenticationTokenStorage->getToken();

        if (null === $preAuthToken) {
            throw new \RuntimeException('Unable to return an authenticated token since there is no pre authentication token.');
        }

        $authToken = new JWTUserToken($user->getRoles(), $user, $preAuthToken->getCredentials(), $providerKey);

        $this->preAuthenticationTokenStorage->setToken(null);

        return $authToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Gets the token extractor to be used for retrieving a JWT token in the
     * current request.
     *
     * Override this method for adding/removing extractors to the chain one or
     * returning a different {@link TokenExtractorInterface} implementation.
     *
     * @return TokenExtractorInterface
     */
    protected function getTokenExtractor()
    {
        return $this->tokenExtractor;
    }

    /**
     * Loads the user to authenticate.
     *
     * @param UserProviderInterface $userProvider An user provider
     * @param array                 $payload      The token payload
     * @param string                $identity     The key from which to retrieve the user "username"
     *
     * @return UserInterface
     */
    protected function loadUser(UserProviderInterface $userProvider, array $payload, $identity)
    {
        if ($userProvider instanceof JWTUserProvider) {
            return $userProvider->loadUserByUsername($identity, $payload);
        }

        return $userProvider->loadUserByUsername($identity);
    }
}
