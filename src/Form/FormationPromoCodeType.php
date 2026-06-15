<?php

namespace App\Form;

use App\Entity\FormationPromoCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationPromoCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code client',
                'help' => 'Ex. BIENVENUE10 (lettres, chiffres, tirets)',
                'attr' => [
                    'placeholder' => 'BIENVENUE10',
                    'style' => 'text-transform: uppercase;',
                ],
            ])
            ->add('discountPercent', IntegerType::class, [
                'label' => 'Réduction (%)',
                'required' => false,
                'help' => 'Ex. 10 pour -10 %. Laissez vide si montant fixe.',
                'attr' => ['min' => 1, 'max' => 100, 'placeholder' => '10'],
            ])
            ->add('discountAmount', MoneyType::class, [
                'label' => 'Réduction (€)',
                'currency' => 'EUR',
                'required' => false,
                'help' => 'Ex. 5 €. Laissez vide si pourcentage.',
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
            ->add('validityDays', IntegerType::class, [
                'label' => 'Durée (jours)',
                'required' => false,
                'help' => 'Nombre de jours de validité à partir de la création. Laissez vide pour un code sans expiration.',
                'attr' => ['min' => 1, 'placeholder' => '30'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormationPromoCode::class,
        ]);
    }
}
