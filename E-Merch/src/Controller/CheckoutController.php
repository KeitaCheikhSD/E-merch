<?php

namespace App\Controller;
use App\Entity\Produit;
use App\Form\CheckoutType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le panier de la session
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        // Récupérer l'adresse de la session si elle existe
        $rue = $session->get('rue', '');
        $ville = $session->get('ville', '');
        $codePostal = $session->get('codePostal', '');
        $pays = $session->get('pays', '');
        
        // Si le panier est vide, rediriger vers le panier
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_panier');
        }

        // Calculer le total et récupérer les items
        $cartWithData = [];
        $total = 0;
        $totalQuantity = 0;
        
        foreach ($cart as $id => $quantity) {
            $product = $entityManager->getRepository(Produit::class)->find($id);
            if ($product) {
                $cartWithData[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product->getPrix() * $quantity;
                $totalQuantity += $quantity;
            }
        }

        // Créer le formulaire avec les données de session si elles existent
        $form = $this->createForm(CheckoutType::class, [
            'rue' => $rue,
            'ville' => $ville,
            'code_postal' => $codePostal,
            'pays' => $pays
        ]);
        
        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Sauvegarder l'adresse en session
            $session->set('rue', $data['rue']);
            $session->set('ville', $data['ville']);
            $session->set('codePostal', $data['code_postal']);
            $session->set('pays', $data['pays']);
            
            $this->addFlash('success', 'Votre adresse a été enregistrée. Vous allez être redirigé vers le paiement.');
            return $this->redirectToRoute('app_payment');
        }

        return $this->render('checkout/index.html.twig', [
            'form' => $form->createView(),
            'items' => $cartWithData,
            'total' => $total,
            'totalQuantity' => $totalQuantity
        ]);
    }
}
