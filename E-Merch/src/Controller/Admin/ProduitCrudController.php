<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use App\Enum\TypeTaille;

class ProduitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    public function configureFields(string $pageName): iterable {
    return [
        TextField::new('libelle')->setLabel('Libellé'),
        TextField::new('Description'),
        MoneyField::new('Prix')
        ->setCurrency('EUR'),
        NumberField::new('Quantite'),
        ChoiceField::new('taille', 'Taille')
        ->setChoices(array_combine(
            ['XS', 'S', 'M', 'L', 'XL', 'Taille Unique'],
            [TypeTaille::XS, TypeTaille::S, TypeTaille::M, TypeTaille::L, TypeTaille::XL, TypeTaille::TAILLE_UNIQUE]
        )),
    ]; 
    }
}
