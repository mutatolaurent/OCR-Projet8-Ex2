<?php

namespace App\Form;

use App\Entity\Employe;
use App\Entity\Projet;
use App\Entity\Tache;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Enum\StatutTache;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // 1. On récupère le projet passé en option depuis le contrôleur
        $projet = $options['projet'];

        $builder
            ->add('titre', TextType::class)
            ->add('description', TextareaType::class)
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('statut', EnumType::class, ['class' => StatutTache::class])
            // ->add('Projet', EntityType::class, [
            //     'class' => Projet::class,
            //     'choice_label' => 'id',
            // ])
            ->add('employe', EntityType::class, [
                'class' => Employe::class,
                // On utilise une fonction anonyme qui reçoit chaque objet Employe
                'choice_label' => function (Employe $employe) {
                    return $employe->getPrenom() . ' ' . $employe->getNom();
                },
                'multiple' => false,
                'query_builder' => function (EntityRepository $er) use ($projet) {
                    return $er->createQueryBuilder('e')
                        ->join('e.projets', 'p') // On suppose que la relation dans l'entité Employe s'appelle 'projets'
                        ->where('p.id = :projetId')
                        ->setParameter('projetId', $projet ? $projet->getId() : null)
                        ->orderBy('e.nom', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
            'projet' => null,
        ]);

        // Optionnel : on peut restreindre le type attendu pour cette option
        $resolver->setAllowedTypes('projet', [Projet::class, 'null']);
    }
}
