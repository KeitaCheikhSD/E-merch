<?php 

namespace App\DesignPattern;

use App\Entity\Paiement ; 

class PaiementCarteBancaire extends Paiement {

    private String $NumeroCarte;
    private String $DateExpiration;
    private String $CVV;

    public function preparerPaiement() : Bool {
        
    }

    public function verifierPaiement() : Bool {

    }

    public function validerCarte() : Bool {

    }

    public function genererRecu() : Bool {

    }

}