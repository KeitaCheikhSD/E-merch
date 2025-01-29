<?php
namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\PaiementCarteBancaire;
use App\Entity\PaiementPaypal;
use App\Entity\Paiement;
use App\Entity\Panier;
use App\Entity\ProduitDecorator;
use App\Enum\TypePaiement;
use App\Enum\TypeStatutPaiement;
use App\Entity\Utilisateur;
use App\Entity\Adresse;
use App\Enum\TypeAdresse;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur gérant le processus de commande dans l'application E-Merch.
 * 
 * Ce contrôleur gère l'ensemble du processus de commande, de la création à la confirmation,
 * en passant par la gestion des adresses de livraison et de facturation.
 */
class CommandeController extends AbstractController
{
    /**
     * Affiche la page de commande avec le récapitulatif du panier.
     * 
     * Cette méthode :
     * - Vérifie que l'utilisateur est connecté
     * - Vérifie que le panier n'est pas vide
     * - Calcule les prix en fonction du pays (normal ou international)
     * - Prépare les données pour l'affichage
     *
     * @param Request $request La requête HTTP
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
     * @return Response La réponse HTTP
     * 
     * @Route("/commander", name="app_commander")
     */
    #[Route('/commander', name: 'app_commander')]
    public function commander(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour passer une commande');
            return $this->redirectToRoute('app_login');
        }
        // Vérifier si le panier n'est pas vide
        $panier = $user->getPanier();
        if (!$panier || $panier->getLignesPanier()->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_panier');
        }

        // Vérifier les quantités disponibles
        foreach ($panier->getLignesPanier() as $lignePanier) {
            if ($lignePanier->getQuantite() > $lignePanier->getProduit()->getQuantite()) {
                $this->addFlash('error', sprintf(
                    'Le produit "%s" n\'est plus disponible en quantité suffisante',
                    $lignePanier->getProduit()->getLibellé()
                ));
                return $this->redirectToRoute('app_panier');
            }
        }

        // Récupérer le pays de livraison
        $session = $request->getSession();
        $paysLivraison = $session->get('pays', 'FR');

        // Calculer les prix en fonction du pays
        $lignesPanierDecorées = [];
        $total = 0;
        foreach ($panier->getLignesPanier() as $lignePanier) {
            $produit = $lignePanier->getProduit();
            // utilisation du decorator
            // Calculer le prix en fonction du pays
            // Si le produit est international, appliquer le prix international
            // Sinon, appliquer le prix normal
            if ($paysLivraison === 'INT') {
                $produitDecore = new ProduitDecorator($produit);
                $prix = $produitDecore->getPrix();
            } else {
                $prix = $produit->getPrix();
            }
            $sousTotal = $prix * $lignePanier->getQuantite();
            $total += $sousTotal;
            $lignesPanierDecorées[] = [
                'ligne' => $lignePanier,
                'prixUnitaire' => $prix,
                'sousTotal' => $sousTotal
            ];
        }
        dump($lignesPanierDecorées);

        return $this->render('commande/index.html.twig', [
            'panier' => $panier,
            'lignesPanierDecorées' => $lignesPanierDecorées,
            'total' => $total,
            'paysActuel' => $paysLivraison
        ]);
    }

    /**
     * Traite la confirmation de la commande.
     * 
     * Cette méthode :
     * - Récupère les données du formulaire (adresse, paiement, etc.)
     * - Vérifie la disponibilité des produits
     * - Calcule le prix total en fonction du pays
     * - Crée et persiste la commande
     * - Gère le paiement
     * - Vide le panier après confirmation
     *
     * @param Request $request La requête HTTP
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
     * @return Response La réponse HTTP
     * @throws \Exception Si le total est invalide
     * 
     * @Route("/commande/confirmer", name="app_commander_confirmer", methods={"POST"})
     */
    #[Route('/commande/confirmer', name: 'app_commander_confirmer', methods: ['POST'])]
    public function confirmer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les données du formulaire (adresse, paiement, etc.)  
        $email = $request->request->get('email_paypal');
        $adresse = $request->request->get('adresse');
        $codePostal = $request->request->get('code_postal');
        $ville = $request->request->get('ville');
        $pays = $request->request->get('paysAdresse');
        $commentaire = $request->request->get('commentaire');
        $methodePaiement = $request->request->get('methode_paiement');

        // Stocker les données dans la session
        $session = $request->getSession();
        $session->set('adresse', $adresse);
        $session->set('code_postal', $codePostal);
        $session->set('ville', $ville);
        $session->set('paysAdresse', $pays);

        // adresse de Facturation
        $session->set('adresseFacturation', $adresse);
        $session->set('code_postalFacturation', $codePostal);
        $session->set('villeFacturation', $ville);
        $session->set('paysFacturation', $pays);

        // Récupérer le pays pour le calcul des prix
        $paysLivraison = $session->get('pays', 'FR');
        dump('Pays de livraison:', $paysLivraison);

        // Récupérer le panier
        $panier = $user->getPanier();
        dump('Contenu du panier:', $panier->getLignesPanier()->toArray());

        // Calculer le total et créer les lignes de commande
        $total = 0;
        $lignesCommande = [];
        foreach ($panier->getLignesPanier() as $lignePanier) {
            $produit = $lignePanier->getProduit();
            
            // Appliquer le décorateur si livraison internationale
            if ($paysLivraison === 'INT') {
                $produitDecore = new ProduitDecorator($produit);
                $prix = $produitDecore->getPrix();
                dump('Prix international pour ' . $produit->getLibellé() . ':', $prix);
            } else {
                $prix = $produit->getPrix();
                dump('Prix normal pour ' . $produit->getLibellé() . ':', $prix);
            }
            
            $quantite = $lignePanier->getQuantite();
            $sousTotal = $prix * $quantite;
            $total += $sousTotal;
            
            // Stocker les informations pour créer la ligne de commande plus tard
            $lignesCommande[] = [
                'produit' => $produit,
                'quantite' => $quantite,
                'prix' => $prix,
                'sousTotal' => $sousTotal
            ];
            
            dump('Ligne commande pour ' . $produit->getLibellé() . ':', [
                'prix unitaire' => $prix,
                'quantité' => $quantite,
                'sous-total' => $sousTotal
            ]);
        }
        dump('Total de la commande:', $total);
        
        if ($total <= 0) {
            throw new \Exception("Le total est invalide: " . $total);
        }

        // Gérer le paiement de l'utilisateur
        if ($methodePaiement === 'paypal') {
            $typePaiement = TypePaiement::PAYPAL;
        } else {
            $typePaiement = TypePaiement::CB;
        }

        // Créer le paiement en utilisant la factory 
        
        if ($typePaiement === TypePaiement::PAYPAL) {
            $paiement = Paiement::creerPaiement($typePaiement);
            $paiement->setEmail($email);
            $paiement->setDatePaiement(new \DateTime());
            $paiement->setPaiement($typePaiement);
            $paiement->setStatut(TypeStatutPaiement::ACCEPTE);
            $paiement->setMontant($total);
            $entityManager->persist($paiement);
        } else {
            $paiement = Paiement::creerPaiement($typePaiement);
            $numeroCarte = str_replace(' ', '', $request->request->get('numero_carte'));
            $paiement->setNumeroCarte($numeroCarte);
            $paiement->setDateExpiration($request->request->get('date_expiration'));
            $paiement->setCvv($request->request->get('cvv'));
            $paiement->setDatePaiement(new \DateTime());
            $paiement->setPaiement($typePaiement);
            $paiement->setStatut(TypeStatutPaiement::ACCEPTE);
            $paiement->setMontant($total);
            $entityManager->persist($paiement);
        }
        
        $this->addFlash('success', $paiement->genererRecu());

        try {
            // Start transaction
            $entityManager->beginTransaction();
            
            // Créer la commande
            $commande = new Commande();
            $commande->setUtilisateur($user)
                    ->setDateCommande(new \DateTime())
                    ->setPaiement($paiement)
                    ->setMontantTotal($total);
            $entityManager->persist($commande);
            
            // Créer Adresse de Livraison
            $adresse = new Adresse();
            $adresse->setRue($session->get('adresse'))
                   ->setVille($session->get('ville'))
                   ->setCodePostal($session->get('code_postal'))
                   ->setPays($pays)
                   ->setTypeAdresse(TypeAdresse::LIVRAISON)
                   ->setCommande($commande);
            $entityManager->persist($adresse);
            // créer  Adresse de Facturation
            $adresseFacturation = new Adresse();
            $adresseFacturation->setRue($session->get('adresseFacturation'))
                   ->setVille($session->get('villeFacturation'))
                   ->setCodePostal($session->get('code_postalFacturation'))
                   ->setPays($session->get('paysFacturation'))
                   ->setTypeAdresse(TypeAdresse::FACTURATION)
                   ->setCommande($commande);
            $entityManager->persist($adresseFacturation);
            // Créer les lignes de commande
           
            foreach ($lignesCommande as $ligne) {
                $ligneCommande = new LigneCommande();
                $ligneCommande->setCommande($commande);
                $ligneCommande->setProduit($ligne['produit']);
                $ligneCommande->setQuantite($ligne['quantite']);
                
                // Déterminer le prix en fonction du pays
                if ($paysLivraison === 'INT') {
                    $produitDecore = new ProduitDecorator($ligne['produit']);
                    $prix = $produitDecore->getPrix();
                    dump('Prix international sauvegardé pour ' . $ligne['produit']->getLibellé() . ':', $prix);
                } else {
                    $prix = $ligne['produit']->getPrix();
                    dump('Prix normal sauvegardé pour ' . $ligne['produit']->getLibellé() . ':', $prix);
                }
                $ligneCommande->setPrix($prix);
                
                $entityManager->persist($ligneCommande);
                
                // Mettre à jour le stock
                $produit = $ligne['produit'];
                $produit->setQuantite($produit->getQuantite() - $ligne['quantite']);
                $entityManager->persist($produit);
            }
            dump($commande->getLignesCommande());
            
            // Vider le panier
            foreach ($panier->getLignesPanier() as $lignePanier) {
                $entityManager->remove($lignePanier);
            }

            $entityManager->flush();
            $entityManager->commit();
        } catch (\Exception $e) {
            $entityManager->rollback();
            throw $e;
        }

        //return $this->redirectToRoute('app_commande_confirmation', ['id' => $commande->getId()]);
        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
            'adresse' => $adresse ,
            'total' => $total,
            'paysActuel' => $paysLivraison,
            'adresseFacturation' => $adresseFacturation
        ]);
    }

    /**
     * Affiche la page de confirmation après une commande réussie.
     * 
     * @param Commande $commande La commande à afficher
     * @return Response La réponse HTTP
     * 
     * @Route("/commande/{id}/confirmation", name="app_commande_confirmation")
     */
    #[Route('/commande/confirmation/{id}', name: 'app_commande_confirmation')]
    public function confirmation(Commande $commande): Response
    {
        // Vérifier que l'utilisateur est bien le propriétaire de la commande
        if ($commande->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer l'adresse de livraison (la première adresse de la collection)
        $adresse = $commande->getAdresse()->first();
        if (!$adresse) {
            throw $this->createNotFoundException('Adresse de livraison non trouvée');
        }

        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
            'adresse' => $adresse ,
            'lignesPanierDecorées' => $lignesPanierDecorées,
            'total' => $total,
            'paysActuel' => $pays
        ]);
    }

    /**
     * Affiche la liste des commandes de l'utilisateur connecté.
     * 
     * @param CommandeRepository $commandeRepository Le repository des commandes
     * @return Response La réponse HTTP
     * 
     * @Route("/mes-commandes", name="app_mes_commandes")
     */
    #[Route('/mes-commandes', name: 'app_mes_commandes')]
    public function mesCommandes(CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $commandes = $commandeRepository->findBy(['user' => $user], ['dateCommande' => 'DESC']);

        return $this->render('commande/mes_commandes.html.twig', [
            'commandes' => $commandes
        ]);
    }
}