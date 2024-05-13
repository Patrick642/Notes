<?php
namespace App\Form;

use App\Entity\Note;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'w-100 m-0 h5 fw-bold bg-transparent border-0 outline-none',
                    'placeholder' => 'Type title here...',
                    'maxlength' => Note::MAX_TITLE_LENGTH
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => Note::MAX_TITLE_LENGTH
                    ])
                ]
            ])
            ->add('text', TextareaType::class, [
                'attr' => [
                    'class' => 'note-textarea w-100 bg-transparent border-0 outline-none',
                    'rows' => '7',
                    'placeholder' => 'Type text here...',
                    'maxlength' => Note::MAX_TEXT_LENGTH
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => Note::MAX_TEXT_LENGTH
                    ])
                ]
            ])
            ->add('color', ChoiceType::class, [
                'choices' => [
                    'bg-info' => 'bg-info',
                    'bg-primary' => 'bg-primary',
                    'bg-secondary' => 'bg-secondary',
                    'bg-success' => 'bg-success',
                    'bg-danger' => 'bg-danger',
                    'bg-warning' => 'bg-warning'
                ],
                'expanded' => true,
                'multiple' => false,
                'choice_attr' => function ($choice, $key, $value) use ($options) {
                    if (empty ($options['data']->color) && 'bg-info' == $value) {
                        return ['checked' => 'checked'];
                    }
                    return [];
                },
                'constraints' => [
                    new NotBlank()
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Note::class
        ]);
    }
}
