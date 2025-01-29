<?php 

namespace App\DesignPattern;

use App\Entity\Paiement ;

class PaiementPaypal extends Paiement {
    
    private String $Email;
    private String $Token;

    public function preparerPaiement() : Bool {

    }

    public function verifierPaiement() : Bool {

    }

    public function authentifierPayPal() : Bool {
        
    }

    public function genererRecu() : Bool {
        
    }

}