<?php

namespace App\Entity;

use App\Enum\TypeTaille;
use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit implements ProduitInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Libellé = null;

    #[ORM\Column(length: 255)]
    private ?string $Description = null;

    #[ORM\Column]
    private ?float $Prix = null;

    #[ORM\Column]
    private ?int $Quantite = null;

    #[ORM\ManyToOne(inversedBy: 'Categorie')]
    private ?CategorieProduit $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EquipeEsport $Equipe = null;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'id_produit')]
    private Collection $avis;

    #[ORM\Column(type: 'string', enumType: TypeTaille::class)]
    private ?TypeTaille $taille = null;


    public function __construct()
    {
        $this->avis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibellé(): ?string
    {
        return $this->Libellé;
    }

    public function setLibellé(string $Libellé): static
    {
        $this->Libellé = $Libellé;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->Prix;
    }

    public function setPrix(float $Prix): static
    {
        $this->Prix = $Prix;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->Quantite;
    }

    public function setQuantite(int $Quantite): static
    {
        $this->Quantite = $Quantite;

        return $this;
    }

    public function getCategorie(): ?CategorieProduit
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieProduit $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getEquipe(): ?EquipeEsport
    {
        return $this->Equipe;
    }

    public function setEquipe(?EquipeEsport $Equipe): static
    {
        $this->Equipe = $Equipe;
        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvis(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setIdProduit($this);
        }

        return $this;
    }

    public function removeAvis(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getIdProduit() === $this) {
                $avi->setIdProduit(null);
            }
        }

        return $this;
    }

    public function getTaille(): ?TypeTaille
    {
        return $this->taille;
    }

    public function setTaille(?TypeTaille $taille): static
    {
        $this->taille = $taille;
        return $this;
    }

    // Getter et setter  sans accent pour le admin et pas erreur dcp
    public function getLibelle(): ?string
    {
        return $this->Libellé;
    }

    public function setLibelle(string $libelle): self
    {
        $this->Libellé = $libelle;
        return $this;
    }
}
