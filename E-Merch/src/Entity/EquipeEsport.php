<?php

namespace App\Entity;

use App\Enum\TypePays;
use App\Repository\EquipeEsportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeEsportRepository::class)]
class EquipeEsport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'Equipe', orphanRemoval: true)]
    private Collection $produits;
    #[ORM\Column(type: 'string', enumType: TypePays::class)]
    private ?TypePays $Pays = null;


    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setEquipe($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getEquipe() === $this) {
                $produit->setEquipe(null);
            }
        }

        return $this;
    }
    public function getPays(): ?TypePays
    {
        return $this->Pays;
    }

    public function setPays(TypePays $Pays): self
    {
        $this->Pays = $Pays;

        return $this;
    }
}
