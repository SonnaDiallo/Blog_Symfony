# Architecture du projet - Symfony FrankenPHP

Ce document décrit l'organisation du dépôt et les composants principaux.

## Vue d'ensemble

Racine du dépôt
```
Caddyfile
Dockerfile
docker-compose.yml
.env.docker
README.md
ARCHITECTURE.md
app/ (monorepo Symfony)
config/ (config de l'environnement ou CI)
```

## Services Docker
- app: image construite depuis `Dockerfile` basée sur `dunglas/frankenphp:1-php8.3-alpine`
- db: MySQL 8.0
- phpmyadmin: interface d'administration
- mailpit: mail testing

## Dossier `app/`
Structure principale de l'application Symfony contenue dans `app/`.

- `bin/` : scripts CLI (console)
- `config/` : configuration Symfony (packages, services, routes)
- `src/` : code PHP de l'application (Controller, Entity, Form, Repository)
- `public/` : point d'entrée HTTP (`index.php`, assets)
- `templates/` : templates Twig
- `migrations/` : migrations Doctrine
- `var/` : cache et logs
- `vendor/` : dépendances Composer

## Caddyfile
Fichier de configuration du serveur Caddy utilisé par FrankenPHP pour:
- Servir les fichiers statiques
- Déléguer les requêtes PHP à FrankenPHP

## Dockerfile
Basé sur une image FrankenPHP, installe des extensions PHP essentielles, copie le `Caddyfile` et expose le service.

## Notes
- La configuration docker expose le service PHP sur le port 8080 redirigé vers 8001 en local.
- Xdebug est optionnel et activable via la variable `INSTALL_XDEBUG`.

