<?php

namespace App\Entity;

use App\Repository\ProduitDecoratorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitDecoratorRepository::class)]
class ProduitDecorator implements ProduitInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    protected ProduitInterface $produit ; 
    
    public function __construct(ProduitInterface $produit) {
        $this -> produit = $produit ; 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibellé() : ?String {
        return $this -> produit -> getLibellé() ; 
    }

    public function getPrix() : ?Float {
        $prixBase = $this -> produit -> getPrix() ; 
        if ($prixBase === null) {
            return null;
        }

        // Appliquer une majoration de 20% pour les commandes internationales
        return $prixBase * 1.20 ; 
    }
}
