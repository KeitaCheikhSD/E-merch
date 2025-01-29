<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Commande;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant les paiements dans l'application E-Merch.
 * 
 * Ce contrôleur gère :
 * - Le traitement des paiements
 * - La validation des transactions
 * - La gestion des erreurs de paiement
 * - Le suivi des statuts de paiement
 */
class PaymentController extends AbstractController
{
    /**
     * Affiche la page de paiement.
     *
     * @param Request $request La requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP
     */
    #[Route('/payment', name: 'app_payment')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les données de session
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $rue = $session->get('rue');
        $ville = $session->get('ville');
        $codePostal = $session->get('codePostal');
        $pays = $session->get('pays');

        // Vérifier si l'adresse est complète
        if (!$rue || !$ville || !$codePostal || !$pays) {
            $this->addFlash('error', 'Veuillez d\'abord renseigner votre adresse de livraison');
            return $this->redirectToRoute('app_checkout');
        }

        // Calculer le total de la commande
        $total = 0;
        $cartWithData = [];
        
        foreach ($cart as $id => $quantity) {
            $product = $entityManager->getRepository(Produit::class)->find($id);
            if ($product) {
                $cartWithData[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product->getPrix() * $quantity;
            }
        }

        return $this->render('payment/index.html.twig', [
            'total' => $total,
            'items' => $cartWithData,
            'adresse' => [
                'rue' => $rue,
                'ville' => $ville,
                'code_postal' => $codePostal,
                'pays' => $pays
            ]
        ]);
    }

    /**
     * Confirme le paiement.
     *
     * @param Request $request La requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP
     */
    #[Route('/payment/confirm', name: 'app_payment_confirm')]
    public function confirmPayment(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);

        // Vérifier si le panier n'est pas vide
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_panier');
        }

        try {
            // Créer l'adresse
            $adresse = new Adresse();
            $adresse->setRue($session->get('rue'));
            $adresse->setVille($session->get('ville'));
            $adresse->setCodePostal($session->get('codePostal'));
            $adresse->setPays($session->get('pays'));
            
            // Créer la commande
            $commande = new Commande();
            $commande->setUtilisateur($user);
            $commande->setAdresse($adresse);
            $commande->setDateCommande(new \DateTime());
            
            // Calculer le total
            $total = 0;
            foreach ($cart as $id => $quantity) {
                $product = $entityManager->getRepository(Produit::class)->find($id);
                if ($product) {
                    $total += $product->getPrix() * $quantity;
                    // TODO: Ajouter les produits à la commande selon votre structure
                    // $commandeProduit = new CommandeProduit();
                    // $commandeProduit->setCommande($commande);
                    // $commandeProduit->setProduit($product);
                    // $commandeProduit->setQuantite($quantity);
                    // $entityManager->persist($commandeProduit);
                }
            }
            
            $commande->setTotal($total);
            
            // Sauvegarder en base de données
            $entityManager->persist($adresse);
            $entityManager->persist($commande);
            $entityManager->flush();
            
            // Vider la session
            $session->remove('rue');
            $session->remove('ville');
            $session->remove('codePostal');
            $session->remove('pays');
            $session->remove('cart');
            
            $this->addFlash('success', 'Votre commande a été validée avec succès !');
            return $this->redirectToRoute('app_confirmation');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la validation de votre commande');
            return $this->redirectToRoute('app_payment');
        }
    }
}
