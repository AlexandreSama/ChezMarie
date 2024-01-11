<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
    public function apiLogin(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $JWTManager, LoggerInterface $logger): Response
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

        $logger->info('User ' . $user->getEmail() . ' logged in successfully.');

        // Retourner le JWT dans la réponse avec l'email de l'utilisateur
        return $this->json(['token' => $token, 'user' => $user->getEmail()]);
    }
    
    #[Route(path: '/user/delete', name: 'anonymize_account')]
    public function anonymizeUser(UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $userEmail = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneBy(['email' => $userEmail]);
        $user->anonymize();
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }
}
