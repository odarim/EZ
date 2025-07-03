<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class LoginType extends AbstractType
{
    public function __construct(private CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    public function buildForm($builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter your username',
                    'autocomplete' => 'username',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter your password',
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('remember_me', CheckboxType::class, [
                'label' => 'Remember Me',
                'required' => false,
                'mapped' => false,
            ])
            ->add('_csrf_token', HiddenType::class, [
                'mapped' => false,
                'data' => $this->csrfTokenManager->getToken('authenticate')->getValue(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => Request::METHOD_POST,
            'csrf_protection' => true,
            'csrf_token_id' => 'authenticate',
            'attr' => [
                'novalidate' => 'novalidate',
                'class' => 'form w-100',
            ],
        ]);
    }
}
