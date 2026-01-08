#  Mon Blog - Symfony 7.3

Application de blog moderne développée avec Symfony 7.3 et FrankenPHP, avec un design un peu inspiré du Leboncoin.

![Symfony](https://img.shields.io/badge/Symfony-7.3-orange)
![PHP](https://img.shields.io/badge/PHP-8.3-blue)
![Docker](https://img.shields.io/badge/Docker-Ready-green)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4)

---

##  Aperçu

- **Page d'accueil** avec liste des articles en cards
- **Page "Mes Articles"** style Leboncoin avec sidebar profil
- **Formulaire de création/édition** moderne avec aperçu d'image
- **Système de panier** pour les articles en vente
- **Interface d'administration** pour les admins

---

## Installation

### Prérequis
- Docker et Docker Compose
- Git

### Étapes

1. **Cloner le projet**
```bash
git clone https://github.com/SonnaDiallo/Blog_Symfony.git
cd Blog_Symfony
```

2. **Configurer l'environnement**
```bash
cd app
cp .env.example .env.local
```

3. **Lancer les conteneurs Docker**
```bash
cd ..
docker compose up -d --build
```

4. **Installer les dépendances**
```bash
docker exec -it symfony_app composer install
```

5. **Créer la base de données et exécuter les migrations**
```bash
docker exec -it symfony_app php bin/console doctrine:database:create
docker exec -it symfony_app php bin/console doctrine:migrations:migrate
```

6. **Accéder à l'application**
- Application : http://localhost:8001
- phpMyAdmin : http://localhost:8080
- Mailpit : http://localhost:1025

---

## Architecture

```
symfony-frankenphp/
├── app/                          # Application Symfony
│   ├── config/                   # Configuration
│   ├── public/                   # Fichiers publics (uploads, assets)
│   ├── src/
│   │   ├── Controller/           # Contrôleurs
│   │   ├── Entity/               # Entités Doctrine
│   │   ├── Form/                 # Formulaires
│   │   └── Repository/           # Repositories
│   ├── templates/                # Templates Twig
│   └── migrations/               # Migrations Doctrine
├── docker-compose.yml            # Configuration Docker
├── Dockerfile                    # Image FrankenPHP
└── Caddyfile                     # Configuration Caddy
```

---

##  Rôles et Permissions

### Visiteur (non connecté)
- Voir les articles publics
- Rechercher des articles
- S'inscrire / Se connecter

### Utilisateur (ROLE_USER)
- Créer des articles
- Modifier/supprimer **ses propres** articles
- Voir "Mes articles"
- Liker des articles
- Ajouter au panier

### Administrateur (ROLE_ADMIN)
- Tout ce que peut faire un utilisateur
- Modifier/supprimer **tous** les articles
- Accès au tableau de bord admin
- Gestion des catégories
- Gestion des utilisateurs

---

##  Fonctionnalités

| Fonctionnalité | Description |
|----------------|-------------|
|  **Articles** | CRUD complet avec images, catégories, temps de lecture |
|  **Recherche** | Recherche par titre et contenu |
| **Likes** | Système de likes en AJAX |
| **Panier** | Ajout au panier pour articles en vente |
| **Authentification** | Inscription, connexion, déconnexion |
| **Design** | Interface moderne style Leboncoin (TailwindCSS) |
| **Responsive** | Compatible mobile, tablette, desktop |
| **Sécurité** | CSRF, hashage mots de passe, permissions |

---

##  Base de données

### Entités principales

- **User** : Utilisateurs (email, password, roles, firstName, lastName)
- **Post** : Articles (title, content, image, price, stock, category, user)
- **Category** : Catégories d'articles
- **PostLike** : Likes sur les articles
- **CartItem** : Articles dans le panier
- **Comment** : Commentaires sur les articles

---

##  Commandes utiles

```bash
# Vider le cache
docker exec -it symfony_app php bin/console cache:clear

# Créer une migration
docker exec -it symfony_app php bin/console make:migration

# Exécuter les migrations
docker exec -it symfony_app php bin/console doctrine:migrations:migrate

# Voir les routes
docker exec -it symfony_app php bin/console debug:router

# Accéder au conteneur
docker exec -it symfony_app sh
```

---

##  Variables d'environnement

Fichier `.env.local` (à créer depuis `.env.example`) :

```env
APP_ENV=dev
APP_SECRET=your_secret_key
DATABASE_URL="mysql://root:root@mysql:3306/app"
MAILER_DSN=smtp://mailpit:1025
```

---

##  Services Docker

| Service | Port | Description |
|---------|------|-------------|
| symfony_app | 8001 | Application Symfony (FrankenPHP) |
| mysql | 3306 | Base de données MySQL 8.0 |
| phpmyadmin | 8080 | Interface phpMyAdmin |
| mailpit | 8025 | Serveur mail de test |

---

##  Design

- **Thème** : Dégradé orange (#f97316 → #f59e0b)
- **Framework CSS** : TailwindCSS
- **Style** : Inspiré de Leboncoin
- **Icônes** : SVG inline

---

##  Auteur

**Sonna Diallo**

- GitHub : [@SonnaDiallo](https://github.com/SonnaDiallo)

---

##  Licence

Ce projet est sous licence MIT.

---

**Version** : 2.0.0  
**Dernière mise à jour** : Janvier 2026