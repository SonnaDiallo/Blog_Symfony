<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('pages/mentions_legales.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        $success = false;
        $error = null;

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $subject = $request->request->get('subject');
            $message = $request->request->get('message');

            if ($name && $email && $subject && $message) {
                $contact = new Contact();
                $contact->setName($name);
                $contact->setEmail($email);
                $contact->setSubject($subject);
                $contact->setMessage($message);

                $entityManager->persist($contact);
                $entityManager->flush();

                $this->addFlash('success', 'Votre message a été envoyé avec succès !');
                $success = true;
            } else {
                $error = 'Veuillez remplir tous les champs.';
            }
        }

        return $this->render('pages/contact.html.twig', [
            'success' => $success,
            'error' => $error,
        ]);
    }

    #[Route('/a-propos', name: 'a_propos')]
    public function aPropos(): Response
    {
        return $this->render('pages/a_propos.html.twig');
    }
}
