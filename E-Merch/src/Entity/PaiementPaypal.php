<?php

namespace App\Entity;

use App\Repository\PaiementPaypalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementPaypalRepository::class)]
class PaiementPaypal extends Paiement
{
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }
    public function preparerPaiement(): bool {
        echo "Préparation du paiement via PayPal.\n";
        return true;
    }

    public function verifierPaiement(): bool {
        echo "Authentification avec PayPal.\n";
        return true;
    }

    public function genererRecu(): string {
        return "Paiement par PayPal effectué";
    }
}
