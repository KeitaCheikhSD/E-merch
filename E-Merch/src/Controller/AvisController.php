<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class AvisController extends AbstractController
{
    #[Route('/avis/add/{id}', name: 'app_avis_add', methods: ['POST'])]
    public function addAvis(Request $request, EntityManagerInterface $entityManager, Produit $produit): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour laisser un avis.');
            return $this->redirectToRoute('app_login');
        }

        $note = $request->request->get('note');
        $description = $request->request->get('description');

        if ($note === null || $description === null) {
            $this->addFlash('error', 'La note et la description sont requises.');
            return $this->redirectToRoute('app_produit', ['id' => $produit->getId()]);
        }

        // Vérifier si l'utilisateur a déjà laissé un avis
        $existingAvis = $entityManager->getRepository(Avis::class)->findOneBy([
            'utilisateur' => $user,
            'id_produit' => $produit
        ]);

        if ($existingAvis) {
            $this->addFlash('error', 'Vous avez déjà laissé un avis pour ce produit.');
            return $this->redirectToRoute('app_produit', ['id' => $produit->getId()]);
        }

        $avis = new Avis();
        $avis->setNote($note);
        $avis->setDescription($description);
        $avis->setIdProduit($produit);
        $avis->setUtilisateur($user);

        $entityManager->persist($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Votre avis a été ajouté avec succès !');
        return $this->redirectToRoute('app_produit', ['id' => $produit->getId()]);
    }
}
