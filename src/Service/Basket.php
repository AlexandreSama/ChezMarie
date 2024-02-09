<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class Basket
{

    //Propriété privé de la classe
    private $session;


    //Constructeur indiquant que la propriété session
    //est égale a la session récupéré
    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    //Fonction ajoutant un id de produit et sa quantité choisi
    //dans la propriété panier, qui est un tableau associatif,
    //de la session
    public function ajouterAuPanier($productId, $quantite)
    {
        $panier = $this->session->get('panier', []);
        if (array_key_exists($productId, $panier)) {
            $panier[$productId] += $quantite;
        } else {
            $panier[$productId] = $quantite;
        }
        $this->session->set('panier', $panier);
    }

    //Fonction supprimant un identifiant de produit
    //De la propriété panier dans la session
    public function supprimerDuPanier($productId)
    {
        $panier = $this->session->get('panier', []);
        if (array_key_exists($productId, $panier)) {
            unset($panier[$productId]);
        }
        $this->session->set('panier', $panier);
    }

    //Récupère simplement le tableau associatif "panier"
    public function getPanier()
    {
        return $this->session->get('panier', []);
    }

    //Fonction permettant de changer la quantité du produit
    //en fonction de l'identifiant produit donné dans le 
    //paramètre
    public function changerQuantite($productId, $quantite)
    {
        $panier = $this->session->get('panier', []);
        if (array_key_exists($productId, $panier)) {
            if ($quantite <= 0) {
                unset($panier[$productId]);
            } else {
                $panier[$productId] = $quantite;
            }
        }
        $this->session->set('panier', $panier);
    }

    //Fonction permettant de vider le panier dans la session
    public function viderPanier()
    {
        $this->session->set('panier', []);
    }
}
