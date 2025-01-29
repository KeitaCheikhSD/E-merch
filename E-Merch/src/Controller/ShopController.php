<?php

namespace App\Controller;
use App\Repository\AvisRepository;
use App\Entity\Avis;
use App\Entity\Produit;
use App\Entity\CategorieProduit;
use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Form\AvisformType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur gérant la boutique en ligne de l'application E-Merch.
 * 
 * Ce contrôleur gère l'affichage et l'interaction avec les produits de la boutique :
 * - Liste des produits disponibles
 * - Détails des produits
 * - Filtrage et recherche de produits
 * - Gestion des catégories
 */
class ShopController extends AbstractController
{
    /**
     * Affiche la liste des produits disponibles dans la boutique.
     *
     * @param string $categorie La catégorie de produits à afficher (facultatif)
     * @param int $page La page de résultats à afficher (facultatif)
     * @param Request $request La requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP contenant la vue de la liste des produits
     */
    #[Route('/produits/{categorie}/{page}', name: 'app_produits', requirements: ['page' => '\d+'], defaults: ['categorie' => 'all', 'page' => 1])]
    public function listeProduits(string $categorie, int $page, Request $request, EntityManagerInterface $entityManager): Response
    {
        $itemsPerPage = 20;

        // Récupérer toutes les catégories pour le filtre
        $categories = $entityManager->getRepository(CategorieProduit::class)->findAll();

        // Créer le QueryBuilder pour les produits
        $queryBuilder = $entityManager->getRepository(Produit::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.categorie', 'c');

        // Filtrer par catégorie si spécifiée
        if ($categorie !== 'all') {
            $queryBuilder
                ->where('c.Nom = :categorie')
                ->setParameter('categorie', $categorie);
        }

        // Compter le nombre total de produits
        $totalProducts = clone $queryBuilder;
        $totalProducts = $totalProducts
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Calculer le nombre total de pages
        $totalPages = ceil($totalProducts / $itemsPerPage);

        // Vérifier si la page demandée existe
        if ($page < 1 || ($page > $totalPages && $totalProducts > 0)) {
            throw new NotFoundHttpException('Page non trouvée');
        }

        // Récupérer les produits pour la page courante
        $products = $queryBuilder
            ->orderBy('p.id', 'DESC')
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();

        return $this->render('shop/produits.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $categorie,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
            'totalProducts' => $totalProducts
        ]);
    }

    /**
     * Gère l'affichage et l'envoi du formulaire d'avis pour un produit.
     *
     * @param Request $request La requête HTTP
     * @param AvisRepository $avisRepository Repository pour accéder aux données des avis
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP contenant la vue du formulaire d'avis
     */
    #[Route('/produits', name: 'app_avis')]
    public function manageAvis(Request $request,  AvisRepository $avisRepository,EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            // Affiche un message ou redirige l'utilisateur
            $this->addFlash('error', 'Vous devez être connecté pour laisser un avis.');
            return $this->redirectToRoute('app_login'); // Redirection vers la page de connexion
        }

        // Création d'une nouvelle instance de l'entité Avis
        $avis = new Avis();

        // Création du formulaire
        $form = $this->createForm(AvisformType::class, $avis);

        // Gestion de la requête (remplissage du formulaire avec les données de la requête)
        $form->handleRequest($request);

        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $avisRepository->save($avis, true);

            // Persistance de l'entité en base de données
            $entityManager->persist($avis);
            $entityManager->flush();

            // Redirection après enregistrement ou affichage d'un message de succès
            $this->addFlash('success', 'Votre avis a été enregistré avec succès.');
            return $this->redirectToRoute('app_avis'); // Redirige vers la même route
        }

        // Rendu de la vue avec le formulaire
        return $this->render('Avis/avis.html.twig', [
            'avisform' => $form->createView(),
        ]);
    }

    /**
     * Affiche les détails d'un produit spécifique.
     *
     * @param int $id L'identifiant du produit à afficher
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP contenant la vue du produit
     */
    #[Route('/produit/{id}', name: 'app_produit')]
    public function produit(int $id, EntityManagerInterface $entityManager): Response
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);
        
        if (!$produit) {
            throw new NotFoundHttpException('Produit non trouvé');
        }

        return $this->render('shop/produit.html.twig', [
            'produit' => $produit
        ]);
    }

    /**
     * Ajoute un produit au panier de l'utilisateur.
     *
     * @param int $id L'identifiant du produit à ajouter au panier
     * @param Request $request La requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP contenant la redirection vers la page du produit
     */
    #[Route('/add-to-cart/{id}', name: 'app_add_to_cart', methods: ['POST'])]
    public function addToCart(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);
        
        if (!$produit) {
            throw new NotFoundHttpException('Produit non trouvé');
        }

        $quantity = $request->request->get('quantity', 1);
        
        // Vérifier si la quantité demandée est disponible
        if ($quantity > $produit->getQuantite()) {
            $this->addFlash('error', 'La quantité demandée n\'est pas disponible');
            return $this->redirectToRoute('app_produit', ['id' => $id]);
        }

        // Récupérer l'utilisateur courant
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter au panier');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer ou créer le panier de l'utilisateur
        $panier = $user->getPanier();
        if (!$panier) {
            $panier = new Panier();
            $panier->setDateCreation(new \DateTime());
            $panier->setUtilisateur($user);
            $entityManager->persist($panier);
        }

        // Chercher si le produit est déjà dans le panier
        $lignePanierRepository = $entityManager->getRepository(LignePanier::class);
        $lignePanier = $lignePanierRepository->findOneBy([
            'panier' => $panier,
            'produit' => $produit
        ]);

        if ($lignePanier) {
            // Mettre à jour la quantité
            $lignePanier->setQuantite($lignePanier->getQuantite() + $quantity);
        } else {
            // Créer une nouvelle ligne de panier
            $lignePanier = new LignePanier();
            $lignePanier->setPanier($panier);
            $lignePanier->setProduit($produit);
            $lignePanier->setQuantite($quantity);
            $entityManager->persist($lignePanier);
        }

        // Sauvegarder les changements
        $entityManager->flush();

        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute('app_produit', ['id' => $id]);
    }
}
