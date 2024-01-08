<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'controller_name' => 'SecurityController'
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $JWTManager): Response
    {
        // Extraire les données de la requête
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;  // Utilisez 'username' au lieu de 'email'
        $password = $data['password'] ?? null;

        // Ajoutez ici votre logique pour charger l'utilisateur
        $user = $userRepository->findOneBy(['email' => $username]);  // Recherchez par email

        // Vérifiez le mot de passe
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            // Gérer l'authentification échouée avec une réponse plus appropriée
            return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        // Si l'authentification est réussie, générer un JWT
        $token = $JWTManager->create($user);

        // Retourner le JWT dans la réponse avec l'email de l'utilisateur
        return $this->json(['token' => $token, 'user' => $user->getEmail()]);
    }
}
