<?php

namespace App\Controller;

use App\Entity\Account\User;
use App\JsResponse\JsResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Datatable\DatatableInterface;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
abstract class BaseController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface      $entityManager,
        protected UrlGeneratorInterface       $urlGenerator,
        protected UserPasswordHasherInterface $userPasswordHasher,
        protected JsResponseBuilder           $jsResponseBuilder,
        protected SerializerInterface         $serializer,
        protected TranslatorInterface         $translator,
        protected Security                    $security
    )
    {
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            JsResponseBuilder::class => JsResponseBuilder::class,
            'doctrine' => ManagerRegistry::class,
            'translator' => TranslatorInterface::class,
        ]);
    }

    /**
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        $connectedUser = $this->security->getUser();

        if (!$connectedUser) {
            return null;
        }

        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['email' => $connectedUser->getUserIdentifier()]);
        if (!$user) {
            $user = $repository->findOneBy(['username' => $connectedUser->getUserIdentifier()]);
        }
//        return $repository->findOneBy([
//            'id' => $connectedUser->getId(),
//        ]);
        return $user;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getRepository(string $className, ?string $managerName = null): EntityRepository
    {
        return $this->em($managerName)->getRepository($className);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function em(?string $name = null): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine')->getManager($name);

        return $em;
    }

    protected function js(): JsResponseBuilder
    {
        return $this->jsResponseBuilder;
    }

//    protected function renderDatatable(
//        DatatableInterface $datatable,
//        string             $template = 'common/datatable.html.twig',
//        string             $layout = 'admin/layout/default-base.html.twig',
//        array              $context = []
//    ): Response
//    {
//        return $this->render($template, array_merge($context, [
//            'datatable' => $datatable,
//            'layout' => $layout
//        ]));
//    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->container->get('translator')->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function persistAndFlush($elem): void
    {
        $this->em()->persist($elem);
        $this->em()->flush();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function removeAndFlush($elem): void
    {
        $this->em()->remove($elem);
        $this->em()->flush();
    }

    /**
     * @param string $className
     * @param $id
     * @return object|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function findOrNotFound(string $className, $id): ?object
    {
        $e = $this->em()->find($className, $id);
        $this->throwNotFoundExceptionIfNull($e);

        return $e;
    }

    // Exception helper

    protected function throwNotFoundExceptionIfNull($target, string $message = 'Not Found'): void
    {
        if (null === $target) {
            throw $this->createNotFoundException($message);
        }
    }

    protected function throwAccessDeniedExceptionIfFalse($target, string $message = ''): void
    {
        if (false === $target) {
            throw $this->createAccessDeniedException($message);
        }
    }
}
