<?php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;

class NotificationService
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function notifyNewComment(User $author, string $postTitle, string $commenterName): void
    {
        $this->logger->info('Notification: Nouveau commentaire', [
            'recipient' => $author->getEmail(),
            'post' => $postTitle,
            'commenter' => $commenterName,
        ]);
        
        // TODO: Implémenter l'envoi d'email avec Symfony Mailer
        // $this->mailer->send(...);
    }

    public function notifyNewLike(User $author, string $postTitle, string $likerName): void
    {
        $this->logger->info('Notification: Nouveau like', [
            'recipient' => $author->getEmail(),
            'post' => $postTitle,
            'liker' => $likerName,
        ]);
    }

    public function notifyCommentApproved(User $commenter, string $postTitle): void
    {
        $this->logger->info('Notification: Commentaire approuvé', [
            'recipient' => $commenter->getEmail(),
            'post' => $postTitle,
        ]);
    }

    public function notifyWelcome(User $user): void
    {
        $this->logger->info('Notification: Bienvenue', [
            'recipient' => $user->getEmail(),
            'name' => $user->getFullName(),
        ]);
    }
}
