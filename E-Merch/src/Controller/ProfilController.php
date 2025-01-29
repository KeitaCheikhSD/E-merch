<?php

namespace App\Controller;

use App\Form\EditProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur gérant le profil utilisateur dans l'application E-Merch.
 * 
 * Ce contrôleur gère :
 * - L'affichage des informations du profil
 * - La modification des données personnelles
 * - La gestion des préférences utilisateur
 * - L'historique des commandes
 */
class ProfilController extends AbstractController
{
    /**
     * Affiche la page de profil de l'utilisateur.
     *
     * @param Request $request La requête HTTP
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP contenant la vue du profil
     */
    #[Route('/profil', name: 'app_profil')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('profil/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Modifie les données personnelles de l'utilisateur.
     *
     * @param Request $request La requête HTTP
     * @param UserPasswordHasherInterface $passwordHasher Hasher de mot de passe utilisateur
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response La réponse HTTP contenant la vue de modification du profil
     */
    #[Route('/profil/modifier', name: 'app_profil_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier le mot de passe actuel
            $currentPassword = $form->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Mot de passe actuel incorrect');
                return $this->redirectToRoute('app_profil_edit');
            }

            // Si un nouveau mot de passe est fourni, le hasher et le définir
            $newPassword = $form->get('plainPassword')->getData();
            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}