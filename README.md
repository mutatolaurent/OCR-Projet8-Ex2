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

**Pile technique :**

- **Framework :** Symfony
- **Moteur de templates :** Twig
- **ORM (Object-Relational Mapping) :** Doctrine (pour les interactions avec la base de données)
- **Base de données :** (Implicitement, via les entités et les migrations Doctrine)
- **Formulaires :** Composant de formulaire Symfony
- **Énumérations :** Énumérations PHP pour `ContratEmploye` et `StatutTache`

**Structure de l'application :**

L'application suit une structure Symfony standard :

- `src/Controller` : Contient les contrôleurs pour gérer les requêtes et réponses HTTP pour les entités `Employe`, `Projet` et `Tache`.
- `src/Entity` : Définit les modèles de données (`Employe`, `Projet`, `Tache`) qui correspondent aux tables de la base de données.
- `src/Form` : Contient les définitions de formulaires (`EmployeType`, `ProjetType`, `TacheType`) utilisés pour créer et modifier des entités.
- `src/Repository` : Fournit des classes pour les requêtes de base de données et les opérations de persistance pour chaque entité.
- `src/Enum` : Définit les énumérations personnalisées utilisées dans l'application.
- `templates/` : Stocke les templates Twig pour le rendu de l'interface utilisateur.
    - `base.html.twig` : Le fichier de mise en page principal.
    - `employe/`, `projet/`, `tache/` : Répertoires contenant des templates spécifiques à chaque module (par exemple, `index` pour les listes, `new` pour la création, `edit` pour les modifications, `show` pour les vues détaillées).

Ce `README.md` fournit un aperçu concis des fonctionnalités de l'application et de son implémentation technique sous-jacente, basé sur les dossiers `src` et `templates` fournis.
