<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginFormType extends AbstractType
{
    public function __construct(
        private AuthenticationUtils $authenticationUtils,
        private RouterInterface $router
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $loginCheckUrl = $this->router->generate('app_security_logincheck');

        $builder
            ->setMethod('POST')
            ->setAction($loginCheckUrl)
            ->add('username', null, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'autofocus' => 'autofocus',
                    'placeholder' => 'Email',
                    'value' => $this->authenticationUtils->getLastUsername(),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Password',
                ],
            ])
            ->add('_remember_me', CheckboxType::class, [
                'label' => 'Remember me',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'checked' => 'checked',
                ],
            ])
            ->add('signIn', SubmitType::class, [
                'label' => 'Sign In',
                'attr' => [
                    'class' => 'btn btn-primary btn-block',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_token_id' => 'authenticate',
        ]);
    }
}
