<?php
namespace App\Enum;

enum TypeAccessibilite : string{
    case GEST_UTILISATEUR = 'Gest_Utitilisateur';
    case GEST_PRODUIT='Gest_Produit';
    case ADMIN_COMPLET='Admin_Complet';
}
?>