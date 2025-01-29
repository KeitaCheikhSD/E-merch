<?php

namespace App\Entity;

use App\Enum\TypePaiement;
use App\Enum\TypeStatutPaiement;
use App\Repository\PaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "type_paiement", type: "string")]
#[ORM\DiscriminatorMap(["cb" => PaiementCarteBancaire::class, "paypal" => "PaiementPaypal"])]
abstract class Paiement {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $DatePaiement = null;

    #[ORM\Column(type: 'string', enumType: TypePaiement::class)]
    private ?TypePaiement $MethodePaiement = null;
    #[ORM\Column(type: 'string', enumType: TypeStatutPaiement::class)]
    private ?TypeStatutPaiement $Statut = null;

    #[ORM\OneToOne(mappedBy: 'Paiement', cascade: ['persist', 'remove'])]
    private ?Commande $commande = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->DatePaiement;
    }

    public function setDatePaiement(\DateTimeInterface $DatePaiement): static
    {
        $this->DatePaiement = $DatePaiement;

        return $this;
    }
    public function getPaiement(): ?TypePaiement
    {
        return $this->MethodePaiement;
    }

    public function setPaiement(TypePaiement $MethodePaiement): self
    {
        $this->MethodePaiement= $MethodePaiement;

        return $this;
    }
    public function getStatut(): ?TypeStatutPaiement
    {
        return $this->Statut;
    }
    public function setStatut(TypeStatutPaiement $statut): static
    {
        $this->Statut = $statut;

        return $this;
    }

    public function setPays(TypeStatutPaiement $statutPaiement ): self
    {
        $this->Statut = $statutPaiement;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(Commande $commande): static
    {
        // set the owning side of the relation if necessary
        if ($commande->getPaiement() !== $this) {
            $commande->setPaiement($this);
        }

        $this->commande = $commande;

        return $this;
    }

    public function procedurePaiement(): bool {
        $this->preparerPaiement();
        if (!$this->verifierPaiement()) {
                $this -> statut = TypeStatutPaiement::REFUSE ;
            return false;
        }
        $this->finaliserPaiement();
        $this->statut = TypeStatutPaiement::ACCEPTE;
        $this->genererRecu();
        return true;
    }

    public abstract function preparerPaiement() ;
    public abstract function verifierPaiement(): bool;
    public abstract function genererRecu() ;


    public static function creerPaiement(TypePaiement $type): Paiement {
        switch ($type) {
            case TypePaiement::CB:
                return new PaiementCarteBancaire();
            case TypePaiement::PAYPAL:
                return new PaiementPaypal();
            default:
                throw new InvalidArgumentException("Type de paiement non supporté.");
        }
    }

}
