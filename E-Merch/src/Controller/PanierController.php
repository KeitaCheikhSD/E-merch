<?php

namespace App\Controller;

use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Entity\ProduitDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant les opérations liées au panier dans l'application E-Merch.
 * 
 * Ce contrôleur gère toutes les fonctionnalités liées au panier d'achat :
 * - Affichage du contenu du panier
 * - Ajout/suppression de produits
 * - Modification des quantités
 * - Calcul des totaux
 */
class PanierController extends AbstractController
{
    /**
     * Affiche le contenu du panier de l'utilisateur.
     *
     * @param Request $request La requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP contenant la vue du panier
     */
    #[Route('/panier', name: 'app_panier')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur courant
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à votre panier');
            return $this->redirectToRoute('app_login');
        }
        // Récupérer le pays de la session
        $session = $request->getSession();
        $pays = $session->get('pays', 'FR');
        // Récupérer le panier de l'utilisateur
        $panier = $user->getPanier();
        if (!$panier) {
            $panier = new Panier();
            $panier->setDateCreation(new \DateTime());
            $panier->setUtilisateur($user);
            $entityManager->persist($panier);
            $entityManager->flush();
        }

        // Calculer le prix total
        $prixTotal = 0;
        $lignesPanierDecorées = [];
        // Parcourir les lignes de panier et calculer le prix total
        foreach ($panier->getLignesPanier() as $lignePanier) {
            $produit = $lignePanier->getProduit();
            // utilisation du decorator
            // Calculer le prix en fonction du pays
            // Si le produit est international, appliquer le prix international
            // Sinon, appliquer le prix normal
            if ($pays === 'INT') {
                $produitDecore = new ProduitDecorator($produit);
                $prix = $produitDecore->getPrix();
            } else {
                $prix = $produit->getPrix();
            }
            $prixTotal += $prix * $lignePanier->getQuantite();
            $lignesPanierDecorées[] = [
                'ligne' => $lignePanier,
                'prixUnitaire' => $prix
            ];
        }
        // Afficher le contenu du panier
        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
            'prixTotal' => $prixTotal,
            'lignesPanierDecorées' => $lignesPanierDecorées,
            'paysActuel' => $pays
        ]);
    }
    //*
    /**
     * Change le pays de calcul des prix.
     *
     * @param Request $request La requête HTTP
     * @return Response La réponse HTTP
     *
     * @Route("/panier/pays", name="app_panier_pays", methods={"POST"})
     */
    #[Route('/panier/pays', name: 'app_panier_pays', methods: ['POST'])]
    public function changerPays(Request $request): Response
    {
        // Récupérer le pays depuis la requête
        $pays = $request->request->get('pays', 'FR');
        // Enregistrer le pays dans la session
        $session = $request->getSession();
        $session->set('pays', $pays);
        // Rediriger vers le panier
        return $this->redirectToRoute('app_panier');
    }
    /**
     * Supprime une ligne du panier.
     *
     * @param int $id L'ID de la ligne à supprimer
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP
     *
     * @Route("/panier/supprimer/{id}", name="app_panier_supprimer", methods={"POST"})
     */
    #[Route('/panier/supprimer/{id}', name: 'app_panier_supprimer', methods: ['POST'])]
    public function supprimerLigne(int $id, EntityManagerInterface $entityManager): Response
    {
        $lignePanier = $entityManager->getRepository(LignePanier::class)->find($id);
        // Vérifier si la ligne de panier existe
        if (!$lignePanier) {
            throw $this->createNotFoundException('Ligne de panier non trouvée');
        }

        // Vérifier que l'utilisateur est propriétaire du panier
        if ($lignePanier->getPanier()->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette ligne de panier');
        }
        // Supprimer la ligne de panier
        $entityManager->remove($lignePanier);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé du panier');
        // Rediriger vers la page du panier
        return $this->redirectToRoute('app_panier');
    }
    /**
     * Modifie la quantité d'un produit dans le panier.
     *
     * @param int $id L'ID de la ligne de panier à modifier
     * @param Request $request La requête contenant la nouvelle quantité
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP
     *
     * @Route("/panier/modifier/{id}", name="app_panier_modifier", methods={"POST"})
     */
    #[Route('/panier/modifier/{id}', name: 'app_panier_modifier', methods: ['POST'])]
    public function modifierQuantite(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $lignePanier = $entityManager->getRepository(LignePanier::class)->find($id);
        // Vérifier si la ligne de panier existe
        if (!$lignePanier) {
            throw $this->createNotFoundException('Ligne de panier non trouvée');
        }

        // Vérifier que l'utilisateur est propriétaire du panier
        if ($lignePanier->getPanier()->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette ligne de panier');
        }

        $quantity = $request->request->get('quantity', 1);
        
        // Vérifier si la quantité demandée est disponible
        if ($quantity > $lignePanier->getProduit()->getQuantite()) {
            $this->addFlash('error', 'La quantité demandée n\'est pas disponible');
            return $this->redirectToRoute('app_panier');
        }
        // Supprimer la ligne de panier si la quantité est nulle
        if ($quantity <= 0) {
            // Supprimer la ligne de panier
            $entityManager->remove($lignePanier);
        } else {
            // Mettre à jour la quantité
            $lignePanier->setQuantite($quantity);
        }
        
        $entityManager->flush();
        // Rediriger vers la page du panier
        return $this->redirectToRoute('app_panier');
    }
}
