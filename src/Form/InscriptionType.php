<?php

namespace App\Form;

use App\Entity\Employe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class)
        ->add('prenom', TextType::class)
        ->add('email', TextType::class)
        ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'mapped' => false,
            'invalid_message' => 'Le mot de passe ne correspond pas.',
            'required' => true,
            'first_options'  => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Confirmation mot de passe'],
            'constraints' => [
                new Length([
                    'min' => 8,
                    'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères.',
                    'max' => 4096,
                ]),
                new Regex([
                    'pattern' => '/[A-Z]/',
                    'message' => 'Votre mot de passe doit contenir au moins une lettre majuscule.',
                ]),
                new Regex([
                    'pattern' => '/[0-9]/',
                    'message' => 'Votre mot de passe doit contenir au moins un chiffre.',
                ]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employe::class,
        ]);
    }
}
