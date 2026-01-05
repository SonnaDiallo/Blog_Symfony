# Mon Blog - Documentation

## 📋 Description
Application Symfony (FrankenPHP) de blog avec système d'authentification, gestion d'articles et interface d'administration.

## 🚀 Installation

### Prérequis
- Docker et Docker Compose
- FrankenPHP (utilisé via l'image `dunglas/frankenphp` dans le Dockerfile)
- PHP 8.3 (géré dans l'image)
- Composer (fourni dans l'image Docker)

> Note: Ce projet est configuré pour être exécuté via Docker/FrankenPHP. Les instructions locales sont fournies pour le développement sans Docker.

### Lancer avec Docker (recommandé)
```bash
# Construire et démarrer les services
docker compose up --build -d

# Accéder à l'app (par défaut)
# http://localhost:8001 -> redirigé vers le service sur le port 8080
```

### Lancer en local (sans Docker)
1. Installer les dépendances
```bash
cd app
composer install
npm install # si vous avez des assets front
```

2. Configurer l'environnement
```bash
cp .env .env.local
# puis ajuster DATABASE_URL et APP_SECRET dans .env.local
```

3. Base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load # optionnel
```

4. Lancer le serveur de développement Symfony
```bash
symfony server:start
```

### Variables d'environnement via Docker
Le fichier `.env.docker` contient les variables utilisées par `docker-compose.yml`. Modifiez-le si nécessaire pour adapter les ports et credentiels.


## 🏗️ Architecture

### Structure des dossiers (racine `app/`)
```
app/
├── bin/                        # Scripts CLI (console)
├── config/                     # Config Symfony (packages, routes, services)
├── public/                     # Entrée HTTP (index.php, assets)
├── src/                        # Code applicatif (Controllers, Entities, Forms, Repositories)
│   ├── Controller/
│   │   ├── BlogController.php
│   │   ├── HelloController.php
│   │   ├── RegistrationController.php
│   │   ├── SecurityController.php
│   │   ├── UserController.php
│   │   └── Admin/
│   ├── Entity/
│   │   ├── Article.php
│   │   ├── BlogPost.php
│   │   ├── Category.php
│   │   ├── Post.php
│   │   └── User.php
│   ├── Form/
│   ├── Repository/
│   └── ...
├── templates/                  # Twig templates (base, admin, blog, security, user)
├── migrations/                 # Doctrine migrations
├── var/                        # Cache & logs
└── vendor/                     # Dépendances composer
```

### Fichiers racine importants
- `Dockerfile` : image basée sur FrankenPHP
- `Caddyfile` : configuration du serveur Caddy pour FrankenPHP
- `docker-compose.yml` : orchestration des services (app, db, phpmyadmin, mailpit)
- `.env.docker` : variables d'environnement pour Docker

### Base de données (Doctrine / Migrations)
Les entités se trouvent dans `app/src/Entity`. Les migrations sont dans `app/migrations`.

### Notes sur l'architecture
- FrankenPHP (via `dunglas/frankenphp`) permet d'utiliser Caddy comme serveur HTTP intégré pour Symfony.
- Les assets sont servis depuis `app/public` et gérés localement (npm, webpack encore ou vite selon configuration).

### Base de données

#### Table `post`
```sql
CREATE TABLE post (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    reading_time INT NULL,
    image VARCHAR(255) NULL,
    user_id INT NOT NULL,
    category_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (category_id) REFERENCES category(id)
);
```

#### Table `user`
```sql
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) UNIQUE NOT NULL,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NULL,
    created_at DATETIME NOT NULL
);
```

#### Table `category`
```sql
CREATE TABLE category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL
);
```

##  Authentification

### Rôles disponibles
- `ROLE_USER` : Utilisateur standard
- `ROLE_ADMIN` : Administrateur

### Routes d'authentification (exemples)
- `/login` : Connexion (formulaire géré par `SecurityController`)
- `/register` : Inscription (`RegistrationController`)
- `/logout` : Déconnexion

> Note: Vérifiez `config/packages/security.yaml` et `src/Controller/SecurityController.php` pour les détails d'implémentation.

##  Fonctionnalités

### Pour les visiteurs
- Consultation des articles
- Recherche d'articles
- Filtrage par catégories
- Inscription/connexion

### Pour les utilisateurs connectés
- Création d'articles (si ADMIN)
- Modification de ses articles
- Suppression de ses articles
- Consultation de ses articles

### Pour les administrateurs
- Tableau de bord statistiques
- Gestion de tous les articles
- Gestion des utilisateurs
- Gestion des catégories

##  Interface

### Navigation
- Barre de recherche intégrée
- Menu utilisateur déroulant avec avatar
- Boutons d'action contextuels
- Design responsive

### Thème
- Dégradé bleu-violet
- Cartes avec effet de transparence
- Animations au survol
- Icônes SVG

##  Configuration

### Paramètres de l'application (`config/services.yaml`)
```yaml
parameters:
    uploads_directory: '%kernel.project_dir%/public/images'
    app.max_file_size: 2097152  # 2MB
    app.allowed_mime_types: ['image/jpeg', 'image/png', 'image/gif']
```

### Sécurité (`config/packages/security.yaml`)
```yaml
security:
    firewalls:
        main:
            pattern: ^/
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
                enable_csrf: true
            logout:
                path: app_logout
                target: app_home
```

##  Tests

### Tests unitaires
```bash
cd app
php bin/phpunit tests/Unit/
```

### Tests fonctionnels
```bash
cd app
php bin/phpunit tests/Functional/
```

### Checks de sécurité
```bash
# Exemple d'audit: vendor/bin/security-checker (ou outils similaires)
```

##  Dépannage

### Problèmes courants

#### Erreur 404 sur les articles
1. Vérifier que l'article existe en base :
```bash
php bin/console doctrine:query:sql "SELECT id, title FROM post"
```

2. Vérifier les relations `user_id` :
```bash
php bin/console doctrine:query:sql "SELECT id FROM post WHERE user_id IS NULL"
```

#### Images non chargées
1. Vérifier le dossier d'upload :
```bash
ls -la public/images/
```

2. Vérifier les permissions :
```bash
chmod -R 755 public/images/
```

#### Erreurs de cache
```bash
php bin/console cache:clear
php bin/console cache:warmup
```

### Commandes utiles

#### Gestion de la base
```bash
# Vider et recréer la base
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Status des migrations
php bin/console doctrine:migrations:status
```

#### Développement
```bash
# Lancer le serveur
symfony server:start

# Voir les routes
php bin/console debug:router

# Voir les services
php bin/console debug:container

# Voir les paramètres
php bin/console debug:container --parameters
```

#### Utilisateurs
```bash
# Créer un utilisateur
php bin/console app:create-user email@example.com password ROLE_ADMIN

# Lister les utilisateurs
php bin/console doctrine:query:sql "SELECT email, roles FROM user"
```

##  Performance

### Optimisations recommandées
1. Activer le cache HTTP
2. Utiliser OPcache
3. Optimiser les images
4. Mettre en cache les requêtes fréquentes

### Monitoring
```bash
# Profiler une requête
php bin/console debug:profile /blog

# Voir les logs
tail -f var/log/dev.log
```

##  Sécurité

### Bonnes pratiques implémentées
- Hashage des mots de passe avec bcrypt
- Protection CSRF sur les formulaires
- Validation des données d'entrée
- Gestion des permissions par rôle
- Sécurisation des uploads de fichiers

### À vérifier en production
- Configuration HTTPS
- Restriction des permissions de fichiers
- Surveillance des logs
- Sauvegardes régulières

##  Contribution

### Workflow de développement
1. Fork du projet
2. Créer une branche descriptive (ex: `feature/auth-registration`)
3. Développer et ajouter des tests
4. Faire une PR vers `main` avec description et captures d'écran si nécessaire

### Standards de code
- Respecter PSR-12
- Utiliser `composer fix` / `php-cs-fixer` si configuré
- Ecrire des tests unitaires et fonctionnels pour les nouvelles fonctionnalités
- Commit messages clairs et atomiques

### Revue et CI
- Ajouter un fichier CI (ex: GitHub Actions) pour l'exécution des tests et l'analyse statique
- Vérifier l'absence de secrets dans les commits

## 📄 Licence
Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

##  Support

### Documentation
- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation Doctrine](https://www.doctrine-project.org/projects/orm.html)
- [Documentation Twig](https://twig.symfony.com/doc/)

### Problèmes connus
Consulter la section [Issues](https://github.com/SonnaDiallo/Blog_Symfony) du dépôt GitHub.

### Contact
Pour les questions techniques, ouvrir une issue sur GitHub.
Pour les problèmes urgents, contacter l'administrateur système.

---

**Version** : 1.1.0  
**Dernière mise à jour** : Janvier 2026  
**Statut** : En développement (Docker/FrankenPHP)