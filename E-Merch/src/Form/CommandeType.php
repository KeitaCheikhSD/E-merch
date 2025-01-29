<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-xl bg-white/[0.03] border border-white/[0.1] text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-xl bg-white/[0.03] border border-white/[0.1] text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-xl bg-white/[0.03] border border-white/[0.1] text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors'
                ]
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-xl bg-white/[0.03] border border-white/[0.1] text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors'
                ]
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-xl bg-white/[0.03] border border-white/[0.1] text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors'
                ]
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-xl bg-white/[0.03] border border-white/[0.1] text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors'
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-xl bg-white/[0.03] border border-white/[0.1] text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors'
                ]
            ])
            ->add('pays', TextType::class, [
                'label' => 'Pays',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-xl bg-white/[0.03] border border-white/[0.1] text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors'
                ]
            ])
            ->add('methodePaiement', ChoiceType::class, [
                'label' => 'Méthode de paiement',
                'choices' => [
                    'PayPal' => 'paypal',
                    'Carte bancaire' => 'carte'
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'hidden peer'
                ]
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Instructions de livraison',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'class' => 'w-full px-4 py-2 rounded-xl bg-white/[0.03] border border-white/[0.1] text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
