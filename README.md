# Application de gestion de projet

Cette application est un outil de gestion de projet construit avec Symfony, conçu pour gérer les employés, les projets et les tâches. Il fournit une interface web permettant aux utilisateurs d'interagir avec ces entités.

**Fonctionnalités principales :**

- **Gestion des employés :**
    - Créer de nouveaux employés.
    - Lister tous les employés existants.
    - Modifier les détails des employés.
    - Les employés peuvent avoir différents types de contrats (énumération `ContratEmploye`).

- **Gestion de projet :**
    - Créer de nouveaux projets.
    - Lister tous les projets.
    - Afficher les détails d'un projet spécifique, y compris les tâches associées.

- **Gestion des tâches :**
    - Créer de nouvelles tâches, qui peuvent être liées à un projet et assignées à un employé.
    - Les tâches ont un statut (par exemple, "À faire", "En cours", "Terminé") défini par l'énumération `StatutTache`.
    - Les tâches sont affichées sous forme de "cartes" dans les détails du projet.

**Authentification et Sécurité :**

L'application intègre un système d'authentification robuste avec plusieurs fonctionnalités de sécurité :

- **Formulaire d'inscription :** Permet aux nouveaux utilisateurs de créer un compte.
- **Formulaire de connexion :** Gère l'authentification des utilisateurs existants.
- **Gestion des rôles et des accès :** Implémente un système de rôles pour contrôler l'accès aux différentes routes et ressources de l'application. Un [Voter Symfony](https://symfony.com/doc/current/security/voters.html) est utilisé pour des contrôles d'accès granulaires.
- **Authentification à double facteur (2FA) :** Sécurise davantage les comptes utilisateurs.
- **Protection contre les attaques par force brute :** Limite le nombre de tentatives de connexion échouées pour un utilisateur.
- **Politique de complexité des mots de passe :** Des règles strictes sont appliquées pour assurer la robustesse des mots de passe utilisateurs.

**Authentification à double facteur (2FA):**

L'application intègre deux systèmes d'authentification à double facteur :

- **2fa par Google Authenticator :**
- **2fa par email :**

Les deux s'appuient sur le bundle SchebTwoFactorBundle (scheb/2fa-bundle) avec en complèment :

- Le bundle scheb/2fa-google-authenticator et
- le bundle scheb/2fa-email

Pour passer de l'un à l'autre il faut intervenir dans les fichiers :

- config/packages/security.yaml
- config/packages/scheb_2fa.yaml
- config/routes/scheb_2fa.yaml
- src/Entity/Employe.php

**Pile technique :**

- **Framework :** Symfony
- **Moteur de templates :** Twig
- **ORM (Object-Relational Mapping) :** Doctrine (pour les interactions avec la base de données)
- **Base de données :** (Implicitement, via les entités et les migrations Doctrine)
- **Formulaires :** Composant de formulaire Symfony
- **Sécurité :** Composant de sécurité Symfony (gestion des utilisateurs, authentification, autorisation, 2FA)
- **Énumérations :** Énumérations PHP pour `ContratEmploye` et `StatutTache`

**Structure de l'application :**

L'application suit une structure Symfony standard :

- `src/Controller` : Contient les contrôleurs pour gérer les requêtes et réponses HTTP pour les entités `Employe`, `Projet`, `Tache`, ainsi que les contrôleurs de sécurité (`SecurityController`, `AccesController`).
- `src/Entity` : Définit les modèles de données (`Employe`, `Projet`, `Tache`, `User`) qui correspondent aux tables de la base de données.
- `src/Form` : Contient les définitions de formulaires (`EmployeType`, `ProjetType`, `TacheType`, `RegistrationFormType`) utilisés pour créer et modifier des entités.
- `src/Repository` : Fournit des classes pour les requêtes de base de données et les opérations de persistance pour chaque entité.
- `src/Enum` : Définit les énumérations personnalisées utilisées dans l'application.
- `src/Security` : Contient les classes liées à la sécurité, y compris les Voters et les mécanismes 2FA.
- `templates/` : Stocke les templates Twig pour le rendu de l'interface utilisateur.
    - `base.html.twig` : Le fichier de mise en page principal.
    - `base-logout.html.twig` : Template pour les pages nécessitant une mise en page spécifique après déconnexion.
    - `security/` : Répertoire contenant les templates pour l'inscription, la connexion et la gestion de la 2FA.
    - `employe/`, `projet/`, `tache/`, `acces/` : Répertoires contenant des templates spécifiques à chaque module (par exemple, `index` pour les listes, `new` pour la creation, `edit` pour les modifications, `show` pour les vues détaillées).

Ce `README.md` fournit un aperçu concis des fonctionnalités de l'application et de son implémentation technique sous-jacente, basé sur les dossiers `src` et `templates` fournis.
