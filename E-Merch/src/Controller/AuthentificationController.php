<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\LoginType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur gérant l'authentification des utilisateurs dans l'application E-Merch.
 * 
 * Ce contrôleur gère l'ensemble des fonctionnalités liées à l'authentification :
 * - Connexion des utilisateurs existants
 * - Inscription des nouveaux utilisateurs
 * - Déconnexion des utilisateurs
 * 
 * Il utilise le système de sécurité de Symfony pour gérer les sessions
 * et la protection des mots de passe.
 */
class AuthentificationController extends AbstractController
{
    /**
     * Gère la connexion des utilisateurs.
     * 
     * Cette méthode :
     * - Affiche le formulaire de connexion
     * - Gère les erreurs d'authentification
     * - Conserve le dernier nom d'utilisateur en cas d'échec
     *
     * @param AuthenticationUtils $authenticationUtils Utilitaire d'authentification Symfony
     * @return Response Page de connexion avec les éventuelles erreurs
     * 
     * @Route("/login", name="app_login")
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('authentification/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Gère l'inscription des nouveaux utilisateurs.
     * 
     * Cette méthode :
     * - Affiche le formulaire d'inscription
     * - Valide les données soumises
     * - Hash le mot de passe de manière sécurisée
     * - Crée le nouvel utilisateur en base de données
     * - Gère les erreurs potentielles
     *
     * @param Request $request La requête HTTP
     * @param UserPasswordHasherInterface $passwordHasher Service de hashage des mots de passe
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Page d'inscription ou redirection vers la connexion
     * 
     * @Route("/register", name="app_register")
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Hash le mot de passe
                    $hashedPassword = $passwordHasher->hashPassword($user, $user->getMotdePasse());
                    $user->setMotdePasse($hashedPassword);
                    
                    // Définir la date d'inscription
                    $user->setDateInscription(new \DateTime());
                    
                    $entityManager->persist($user);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Votre compte a été créé avec succès !');
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la création du compte : ' . $e->getMessage());
                }
            } else {
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('authentification/register.html.twig', [
            'registrationForm' => $form->createView(),
            'errors' => $form->isSubmitted() ? $form->getErrors(true) : []
        ]);
    }

    /**
     * Gère la déconnexion des utilisateurs.
     * 
     * Cette méthode est interceptée par la configuration de sécurité de Symfony.
     * Elle n'a pas besoin de contenir de logique car Symfony gère automatiquement :
     * - La destruction de la session
     * - La redirection vers la page configurée
     *
     * @return void
     * 
     * @Route("/logout", name="app_logout")
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide,
        // elle sera interceptée par la configuration de sécurité
    }
}
