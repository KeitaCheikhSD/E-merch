<?php

namespace App\Entity;

use App\Repository\PaiementCarteBancaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementCarteBancaireRepository::class)]
class PaiementCarteBancaire extends Paiement
{
    #[ORM\Column(length: 16)]
    private ?string $numeroCarte = null;

    #[ORM\Column(length: 255)]
    private ?string $DateExpiration = null;

    #[ORM\Column(length: 255)]
    private ?string $cvv = null;

    
    public function getNumeroCarte(): ?string
    {
        return $this->numeroCarte;
    }

    public function setNumeroCarte(string $numeroCarte): static
    {
        $this->numeroCarte = $numeroCarte;

        return $this;
    }

    public function getDateExpiration(): ?string
    {
        return $this->DateExpiration;
    }

    public function setDateExpiration(string $DateExpiration): static
    {
        $this->DateExpiration = $DateExpiration;

        return $this;
    }

    public function getCvv(): ?string
    {
        return $this->cvv;
    }

    public function setCvv(string $cvv): static
    {
        $this->cvv = $cvv;

        return $this;
    }
    public function preparerPaiement(): bool {
        echo "Préparation du paiement par carte bancaire.\n";
        return true;
    }

    public function verifierPaiement(): bool {
        echo "Vérification des informations de la carte.\n";
        return true;
    }

    public function genererRecu(): string {
        return "Paiement par carte bancaire effectué";
    }
}
