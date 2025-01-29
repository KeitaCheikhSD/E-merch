<?php
namespace App\Enum;

enum TypeStatutCommande : string {
    case EN_ATTENTE = 'Attente';
    case PAYEE = 'Payee';
    case LIVRE = 'Livre';
    case ANNULEE = 'Annulee';
    case EXPEDIE  = 'Expedie';
}
