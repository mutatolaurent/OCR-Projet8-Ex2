<?php

namespace App\Entity;

use App\Enum\StatutTache;
use App\Repository\TacheRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
class CopyTache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le titre doit faire au moins {{ limit }} caractères."
    )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Regex(
        pattern: '/^(\s*\S+\s+){4,}\S+\s*$/',
        message: "La description doit contenir au moins 5 mots."
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La date est obligatoire.")]
    #[Assert\GreaterThanOrEqual('2020-01-01')]
    #[Assert\LessThanOrEqual('2030-12-31')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\ManyToOne(inversedBy: 'taches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Projet $Projet = null;

    #[ORM\ManyToOne(inversedBy: 'taches')]
    private ?Employe $Employe = null;

    #[ORM\Column(enumType: StatutTache::class)]
    private ?StatutTache $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->Projet;
    }

    public function setProjet(?Projet $Projet): static
    {
        $this->Projet = $Projet;

        return $this;
    }

    public function getEmploye(): ?Employe
    {
        return $this->Employe;
    }

    public function setEmploye(?Employe $Employe): static
    {
        $this->Employe = $Employe;

        return $this;
    }

    public function getStatut(): ?StatutTache
    {
        return $this->statut;
    }

    public function setStatut(StatutTache $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
