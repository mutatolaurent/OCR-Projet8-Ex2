<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Factory\EmployeFactory;
use App\Factory\ProjetFactory;
use App\Factory\TacheFactory;
use Faker\Factory;
use App\Entity\Projet;

use function Zenstruck\Foundry\lazy;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 1. Création des employés
        $poolEmployes = EmployeFactory::createMany(30);

        // 2. Création des projets (on récupère le tableau de projets créés)
        $projets = ProjetFactory::createMany(10, function () use ($poolEmployes) {
            $clesAleatoires = array_rand($poolEmployes, 5);
            $employesDuProjet = [];
            foreach ($clesAleatoires as $cle) {
                $employesDuProjet[] = $poolEmployes[$cle];
            }
            // C'est ici qu'on initialise la relation Projet Employe
            // Doctrine va créer les enregistrements dans la table intermédiaire
            return ['employe' => $employesDuProjet];
        });

        // 3. On boucle sur les projets créés pour leur ajouter les tâches !
        // À ce stade, les projets ont un ID, ils existent
        foreach ($projets as $projet) {

            // On récupère les employés spécifiquement associés à CE projet
            $employesDuProjet = $projet->getEmployes()->toArray();

            $nombreDeTaches = $faker->numberBetween(3, 4);

            TacheFactory::createMany($nombreDeTaches, function () use ($employesDuProjet, $projet) {
                $cleEmploye = array_rand($employesDuProjet);

                // C'est ici qu'on initialise les relations Tache Employe et Tache Projet
                // Doctrine va créer les clés étrangères
                return [
                    'projet'  => $projet,
                    'employe' => $employesDuProjet[$cleEmploye],
                ];
            });
        }
    }
}
