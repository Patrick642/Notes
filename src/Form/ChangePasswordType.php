<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Passwords are not the same',
                'first_options' => [
                    'label' => 'Password',
                    'label_attr' => [
                        'class' => 'form-label'
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Password',
                        'minlength' => User::MIN_PASSWORD_LENGTH
                    ]
                ],
                'second_options' => [
                    'label' => 'Password repeat',
                    'label_attr' => [
                        'class' => 'form-label'
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Password repeat',
                        'minlength' => User::MIN_PASSWORD_LENGTH
                    ]
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => User::MIN_PASSWORD_LENGTH,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
