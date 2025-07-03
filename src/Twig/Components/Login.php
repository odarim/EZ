<?php

namespace App\Twig\Components;

use App\Controller\BaseController;
use App\Form\LoginType;
use App\JsResponse\JsResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('app-login', template: 'components/login.html.twig')]
class Login extends BaseController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: false)]
    public ?string $last_username = null;

    #[LiveProp(writable: false)]
    public ?string $error = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(LoginType::class, null, [
            'action' => $this->generateUrl('app.login'),
        ]);
    }
}
