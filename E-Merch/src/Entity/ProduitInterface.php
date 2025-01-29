<?php

namespace App\Entity;

use App\Repository\ProduitInterfaceRepository;
use Doctrine\ORM\Mapping as ORM;

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

interface ProduitInterface {
    public function getPrix(): ?float;
    public function getLibellé(): ?string;
}
