<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\CharacterClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'attr'  => ['class' => 'form-control'],
            ])

            ->add('playerName', TextType::class, [
                'mapped' => false,
                'label'  => 'Hero Name',
                'attr'   => [
                    'class'       => 'form-control',
                    'placeholder' => 'Enter your hero name'
                ],
                'constraints' => [
                    new NotBlank(message: 'Please enter a hero name'),
                ],
            ])

            ->add('characterClass', ChoiceType::class, [
                'mapped'  => false,
                'label'   => 'Character Class',
                'choices' => [
                    'Warrior ' => CharacterClass::WARRIOR,
                    'Mage '     => CharacterClass::MAGE,
                    'Rogue '    => CharacterClass::ROGUE,
                ],
                'attr' => ['class' => 'form-select'],
            ])

            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label'  => 'Password',
                'attr'   => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(message: 'Please enter a password'),
                    new Length(
                        min: 6,
                        minMessage: 'Your password should be at least {{ limit }} characters long.'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}