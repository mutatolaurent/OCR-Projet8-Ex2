# Documentation Technique et Fonctionnelle - Application de Gestion de Projets

Ce document présente une vue d'ensemble détaillée de l'application à partir de l'analyse du code source (`src/`) et des templates Twig (`templates/`).

---

## 1. Fonctionnalités Principales

L'application est une plateforme de gestion de projets et de suivi de tâches collaboratives destinées aux employés d'une entreprise.

### 🏢 Gestion des Projets
* **Tableau de bord & Liste des projets** (`ProjetController::index`) :
  * **Pour les administrateurs (`ROLE_ADMIN`)** : Accès global à tous les projets non archivés de l'entreprise.
  * **Pour les employés (`ROLE_USER`)** : Affichage restreint aux seuls projets auxquels l'employé est explicitement rattaché.
* **Détails d'un projet & Kanban de tâches** (`ProjetController::show`) :
  * Vue détaillée du projet présentant la liste des employés associés et l'organisation des tâches selon leur statut.
* **Création, édition et archivage** (`ProjetController::add`, `edit`, `archive`) :
  * Fonctionnalités réservées aux administrateurs.
  * **Archivage ("Soft Delete")** : Un projet peut être archivé sans être supprimé physiquement de la base de données (`archive = 1`).
  * **Règle métier d'édition** : Impossibilité de retirer un employé d'un projet s'il est encore affecté à au moins une tâche active au sein de ce projet.

### 📋 Gestion des Tâches
* **Création et édition de tâches** (`TacheController::add`, `edit`) :
  * Attribution des tâches aux employés travaillant sur le projet.
  * Gestion des statuts de tâches basés sur un enum PHP (`TO_DO`, `DOING`, `DONE`, etc.).
  * Possibilité de pré-remplir le statut d'une tâche directement via l'URL (`default_status`).
* **Suppression de tâches** (`TacheController::remove`) :
  * Retrait d'une tâche avec redirection dynamique vers la page du projet concerné.

### 👥 Gestion des Employés
* **Annuaire des employés** (`EmployeController::index`) :
  * Consultation de la liste des employés triés par ordre alphabétique.
* **Administration des membres** (`EmployeController::add`, `edit`, `remove`) :
  * Création et modification des profils employés (nom, prénom, email, type de contrat : CDI, CDD, etc.).
  * Suppression d'un employé avec nettoyage préalable et sécurisé de ses affectations aux projets et tâches.

### 🔑 Inscription et Accueil
* **Accueil public** (`AccesController::accueil`) : Page de présentation.
* **Inscription en ligne** (`AccesController::register`) :
  * Formulaire d'auto-inscription (`InscriptionType`).
  * Attribution automatique du contrat CDI et de la date d'entrée du jour.
  * **Connexion automatique immédiate** après inscription grâce au service `Security::login()`.

---

## 2. Mécanismes d'Authentification et Sécurité

L'application intègre plusieurs niveaux de sécurité natifs Symfony et de mécanismes sur-mesure.

### 🔐 Authentification & Gestion de Session
* **User Provider** : Basé sur l'entité `Employe` identifiée par son adresse email (`config/packages/security.yaml`).
* **Hachage des mots de passe** : Hachage fort via `UserPasswordHasherInterface` (algorithme `auto` / Argon2id / bcrypt avec paramétrage optimisé en environnement de test).
* **Limitation des tentatives de connexion (Login Throttling)** : Restriction automatique à **3 tentatives toutes les 10 minutes** pour contrer les attaques de force brute.
* **Protection CSRF** : Activée sur le formulaire de connexion (`enable_csrf: true`) ainsi que sur l'ensemble des formulaires de l'application via les Form Types Symfony.
* **Sécurisation de la sérialisation de session** : Redéfinition de `__serialize()` dans l'entité `Employe` avec hachage CRC32C du mot de passe en session (fonctionnalité Symfony 7.3+).

### 🛡️ Authentification à Deux Facteurs (2FA / MFA)
* **Bundle utilisé** : Integration de `scheb/2fa-bundle`.
* **2FA par Email** : Implémentation de `TwoFactorInterface` (`Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface`) sur l'entité `Employe`.
* **2FA par Google Authenticator (TOTP)** : Support des applications TOTP via `endroid/qr-code` pour la génération et l'affichage d'un QR Code SVG vectoriel dans le profil utilisateur (`SecurityController::enable2fa`).
* **Pare-feu 2FA** : Redirection des utilisateurs en cours de validation 2FA vers l'espace dédié (`IS_AUTHENTICATED_2FA_IN_PROGRESS`).

### 👮 Contrôle d'Accès et Autorisations (RBAC & Voters)
* **Système de Rôles** :
  * `ROLE_USER` : Rôle de base pour tous les employés connectés.
  * `ROLE_ADMIN` : Rôle d'administration autorisant les opérations de création, modification et suppression globales.
* **Voter Personnalisé (`ProjetVoter.php`)** :
  * Attribut de sécurité : `PROJET_ACCESS`.
  * Règle métier : Accès accordé si l'utilisateur possède le rôle `ROLE_ADMIN` **OU** si l'utilisateur est un employé explicitement rattaché au projet (`$projet->getEmployes()->contains($user)`).
  * Sécurisation granulaire appliquée sur la consultation des projets ainsi que sur la création, l'édition et la suppression des tâches associées.

### 📝 Traçabilité et Audit
* **Écouteur d'événements de routage (`RouteLogListener.php`)** :
  * Intercepte chaque requête `kernel.request` (hors profiler et sous-requêtes).
  * Enregistre dans un canal Monolog dédié la route consultée, la méthode HTTP, l'URI et l'adresse IP du client.

---

## 3. Pile Technique (Tech Stack)

| Composant | Technologie / Bundle | Rôle / Description |
| :--- | :--- | :--- |
| **Langage** | **PHP 8.2+** | Langage de programmation backend avec typage strict et Enums |
| **Framework Web** | **Symfony 7.4** | Framework MVC PHP (FrameworkBundle, SecurityBundle, Form, Validator, Mailer, RateLimiter) |
| **ORM & Base de données** | **Doctrine ORM 3.6** | Mapping objet-relationnel, Doctrine Bundle 2.18, Doctrine Migrations 3.7 |
| **Templating** | **Twig 3** | Moteur de rendu HTML côté serveur avec extensions Twig Extra |
| **Frontend & Asset Management** | **Symfony AssetMapper** | Gestion des assets front-end sans Node.js/Webpack, avec **Symfony UX Stimulus** et **UX Turbo** (2.36) |
| **Sécurité & 2FA** | **Scheb TwoFactorBundle 7.14** | Module 2FA (Email & Google Authenticator TOTP) |
| **Génération QR Code** | **Endroid QR Code 6.0** | Génération de QR codes au format SVG pour la configuration 2FA |
| **Tests & Fixtures** | **PHPUnit 11.5 / Foundry 2.10** | Tests unitaires/fonctionnels et génération de fixtures de données (`zenstruck/foundry`) |
| **Qualité & Analyse** | **PHPStan & PHP CS** | Analyse statique et respect des standards de code PHP |

---

## 4. Principaux Dossiers et Structure du Projet

```text
.
├── src/                        # Code source principal PHP de l'application
│   ├── Controller/             # Contrôleurs HTTP de l'application
│   │   ├── AccesController.php     # Accueil et inscription des utilisateurs
│   │   ├── EmployeController.php   # Gestion du CRUD des employés
│   │   ├── ProjetController.php    # Gestion du CRUD, archivage et détails des projets
│   │   ├── SecurityController.php  # Authentification, déconnexion et configuration 2FA
│   │   └── TacheController.php     # Gestion des tâches (ajout, édition, suppression)
│   ├── Entity/                 # Entités Doctrine (modèles de données)
│   │   ├── Employe.php             # Utilisateur / Employé avec contrat et sécurités 2FA
│   │   ├── Projet.php              # Projet avec statut d'archivage et membres
│   │   └── Tache.php               # Tâche rattachée à un projet et un employé
│   ├── Enum/                   # Énumérations typées PHP 8.1+
│   │   ├── ContratEmploye.php      # CDI, CDD, etc.
│   │   └── StatutTache.php         # Statuts de suivi des tâches
│   ├── EventListener/          # Écouteurs d'événements système
│   │   └── RouteLogListener.php    # Logger de traçabilité des requêtes HTTP
│   ├── Form/                   # Formulaires Symfony
│   │   ├── EmployeType.php         # Formulaire employé
│   │   ├── InscriptionType.php     # Formulaire d'inscription
│   │   ├── ProjetType.php          # Formulaire projet
│   │   └── TacheType.php           # Formulaire tâche
│   ├── Repository/             # Requêtes personnalisées Doctrine ORM
│   ├── Security/               # Composants de sécurité
│   │   └── Voter/
│   │       └── ProjetVoter.php     # Voter pour l'autorisation d'accès aux projets
│   ├── DataFixtures/           # Jeux de données pour le développement
│   ├── Factory/ & Story/       # Factories Zenstruck Foundry pour les tests
│   └── Kernel.php              # Noyau de l'application Symfony
│
├── templates/                  # Vues et templates Twig
│   ├── base.html.twig          # Layout principal (avec barre de navigation et alertes)
│   ├── base-logout.html.twig   # Layout épuré pour la connexion / déconnexion
│   ├── acces/                  # Vues d'accueil (`home`) et d'inscription (`register`)
│   ├── employe/                # Vues de listing et d'édition des employés
│   ├── projet/                 # Vues du tableau de bord des projets et du Kanban
│   ├── security/               # Formulaires de connexion et de configuration 2FA
│   └── tache/                  # Formulaires de gestion des tâches
│
├── config/                     # Configuration de l'application (packages, security, routes)
├── migrations/                 # Fichiers de migrations de la base de données SQL
├── assets/                     # Fichiers CSS et contrôleurs JavaScript Stimulus
├── public/                     # Point d'entrée web (`index.php`) et ressources publiques
└── tests/                      # Tests unitaires et d'intégration PHPUnit
```
