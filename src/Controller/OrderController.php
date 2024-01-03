<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\BasketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Charge;
use Stripe\Exception\CardException;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order/{basketId}/{userId}/{fullPrice}', name: 'app_order')]
    public function index($basketId, $userId, $fullPrice, Request $request, BasketRepository $basketRepository, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        // Utilisez les paramètres récupérés pour construire votre entité Order
        $basket = $basketRepository->find($basketId);
        $user = $userRepository->find($userId);

        if (!$basket || !$user) {
            return $this->redirectToRoute('app_home');
        }

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $stripeToken = $form->get('stripeToken')->getData();

            // Processus de paiement Stripe
            if ($this->processStripePayment($stripeToken, $fullPrice)) {
                // Associez l'entité Order au panier et à l'utilisateur
                $order->setBasket($basket);
                $order->setUserid($user);

                //Indiquez le reste
                $order->setIsPending(false);
                $order->setIsServed(false);
                $order->setIsNotServer(false);
                $order->setDateOrder(new \DateTime());
                $order->setFullPrice($fullPrice);
                $order->setIsPreparing(true);
                $order->setDesiredPickupDateTime($form->get('desiredPickupDateTime')->getData());


                // Persistez l'entité Order en base de données
                $em->persist($order);
                $em->flush();

                // Redirigez l'utilisateur vers la page souhaitée après la soumission du formulaire
                return $this->redirectToRoute('app_basket');
            } else {
                return $this->redirectToRoute('app_basket');
            };
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'OrderController',
            'stripe_key' => $_ENV["STRIPE_KEY"],
            'fullPrice' => $fullPrice
        ]);
    }

    private function processStripePayment($stripetoken, $amount)
    {
        Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

        try {
            Charge::create([
                "amount" => $amount * 100, // Montant en centimes
                "currency" => "eur",
                "source" => $stripetoken,
                "description" => "Test"
            ]);

            // Le paiement a réussi
            return true;
        } catch (CardException $e) {
            // Le paiement a échoué, gérer l'exception ici
            $error = $e->getError();
            $errorMessage = $error->message;
            // Vous pouvez enregistrer ces informations dans les logs ou renvoyer un message à l'utilisateur

            return false;
        }
    }
}
