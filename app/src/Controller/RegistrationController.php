<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): Response
    {
        // Si déjà connecté, rediriger
        if ($this->getUser()) {
            return $this->redirectToRoute('app_blog_index');
        }

        $errors = [];

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email', ''));
            $plainPassword = $request->request->get('password', '');
            $confirmPassword = $request->request->get('confirm_password', '');
            
            // Vérifier si l'email existe déjà
            if ($userRepository->findOneBy(['email' => $email])) {
                $errors[] = 'Cette adresse email est déjà utilisée.';
            }
            
            // Vérifier la confirmation du mot de passe
            if ($plainPassword !== $confirmPassword) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }
            
            // Vérifier la force du mot de passe
            if (strlen($plainPassword) < 8) {
                $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }
            
            $user = new User();
            $user->setFirstName(trim($request->request->get('first_name', '')));
            $user->setLastName(trim($request->request->get('last_name', '')));
            $user->setEmail($email);
            $user->setPhone(trim($request->request->get('phone', '')) ?: null);
            
            // Valider l'entité avec les contraintes Assert
            $violations = $validator->validate($user);
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            
            if (empty($errors)) {
                // Hasher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                
                // Rôle par défaut : ROLE_USER
                $user->setRoles(['ROLE_USER']);

                try {
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');

                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la création du compte.');
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'errors' => $errors,
        ]);
    }
}