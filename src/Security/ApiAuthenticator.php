<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class ApiAuthenticator extends AbstractAuthenticator
{
    private $userRepository;
    private $jwtManager;

    public function __construct(UserRepository $userRepository, JWTTokenManagerInterface $jwtManager)
    {
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
    }

    /**
     * The function checks if the request path is '/api/login' and the request method is 'POST'.
     * 
     * @param Request request The parameter `` is an instance of the `Request` class. It
     * represents an HTTP request made to the server.
     * 
     * @return ?bool a boolean value, either true or false.
     */
    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/api/login' && $request->isMethod('POST');
    }

    /**
     * The function takes a request, extracts the username and password from the request data, and
     * returns a Passport object with a UserBadge and PasswordCredentials.
     * 
     * @param Request request The `` parameter is an instance of the `Request` class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, and body.
     * 
     * @return Passport an instance of the Passport class.
     */
    public function authenticate(Request $request): Passport
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        dd($request);

        return new Passport(
            new UserBadge($username, function($username) {
                $user = $this->userRepository->findOneBy(['email' => $username]);
                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Email not found.');
                }
        
                // Vérifier si l'utilisateur a le rôle [ROLE_EMPLOYE]
                $roles = $user->getRoles();
                if (!in_array('ROLE_EMPLOYE', $roles) && !in_array('ROLE_GERANT', $roles)) {
                    throw new CustomUserMessageAuthenticationException('Access Denied: You do not have the required role.');
                }                
        
                return $user;
            }),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new \LogicException('The user must implement PasswordAuthenticatedUserInterface.');
        }

        $jwt = $this->jwtManager->create($user);
        return new JsonResponse(['token' => $jwt, 'user' => $user->getUserIdentifier()]);
    }

    /**
     * The function returns a JSON response with an unauthorized status code and a message based on the
     * authentication failure exception.
     * 
     * @param Request request The  parameter is an instance of the
     * Symfony\Component\HttpFoundation\Request class. It represents the current HTTP request being
     * handled by the application.
     * @param AuthenticationException exception The  parameter is an instance of the
     * AuthenticationException class. It represents the exception that occurred during the
     * authentication process. It contains information about the error, such as the error message and
     * any additional data associated with the error.
     * 
     * @return ?Response A JsonResponse object is being returned.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = ['message' => strtr($exception->getMessageKey(), $exception->getMessageData())];
        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}