<?php

namespace App\Security\Voter;

use App\Entity\Projet;
use App\Entity\Employee;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ProjetVoter extends Voter
{
    // On définit les permissions supportées par ce Voter
    public const ACCESS = 'PROJET_ACCESS';

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    // Étape A : On indique si ce Voter doit intervenir
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Le voter s'active uniquement si l'attribut est 'PROJET_ACCESS' et que le sujet est un Projet
        return $attribute === self::ACCESS && $subject instanceof Projet;
    }

    // Étape B : Si "supports" renvoie true, on vote !
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // 1. Les admins ont toujours accès
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // 2. On récupère l'utilisateur actuellement connecté
        $user = $token->getUser();
        // if (!$user instanceof Employee) {
        //     return false; // Non connecté -> Accès refusé
        // }

        /** @var Projet $projet */
        $projet = $subject;

        // 3. On applique la règle métier
        return $projet->getEmployes()->contains($user);
    }
}
