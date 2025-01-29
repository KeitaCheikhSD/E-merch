<?php

namespace App\Entity;

use App\Repository\AdministateurRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\TypeAccessibilite;


#[ORM\Entity(repositoryClass: AdministateurRepository::class)]
class Administateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: TypeAccessibilite::class)]
    private ?TypeAccessibilite $niveauAccessibilite = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNiveauAccessibilite(): ?TypeAccessibilite
    {
        return $this->niveauAccessibilite;
    }

    public function setNiveauAccessibilite(TypeAccessibilite $niveauAccessibilite): self
    {
        $this->niveauAccessibilite = $niveauAccessibilite;

        return $this;
    }
}
