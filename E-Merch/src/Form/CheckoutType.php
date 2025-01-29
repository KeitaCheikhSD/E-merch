<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white',
                    'placeholder' => 'Numéro et nom de rue'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer votre rue'
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage' => 'L\'adresse doit contenir au moins {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white',
                    'placeholder' => 'Nom de la ville'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer votre ville'
                    ])
                ]
            ])
            ->add('code_postal', TextType::class, [
                'label' => 'Code Postal',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white',
                    'placeholder' => 'Code postal'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer votre code postal'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]{5}$/',
                        'message' => 'Le code postal doit contenir 5 chiffres'
                    ])
                ]
            ])
            ->add('pays', CountryType::class, [
                'label' => 'Pays',
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white'
                ],
                'preferred_choices' => ['FR'],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner votre pays'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
