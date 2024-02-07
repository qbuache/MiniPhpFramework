# Mini framework PHP

Permet de créer des sites facilement

WIP

## Installation

1. Télécharger les dépendances :

   - Développement :

     `composer i`

   - Production :

     `composer i --no-dev`

2. Copier le fichier `.env.example` en `.env`
3. Donner les droits à Apache sur le dossier `storage` :

   `chown -R apache:apache storage`

4. Définir une redirection dans Apache vers le fichier index.php

   ```
   Alias "/mon-site" "/var/www/html/mon-site/public"

   <Location "/mon-site">
     RewriteEngine On
     RewriteCond %{REQUEST_FILENAME} !-f
     RewriteRule .* index.php
   </Location>
   ```
