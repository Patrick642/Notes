<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SignUpType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

#[Route('/signup')]
class SignUpController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'app_signup')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, AppAuthenticator $appAuthenticator, UserAuthenticatorInterface $userAuthenticator): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('app_notes');

        $user = new User();
        $form = $this->createForm(SignUpType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles([$user::ROLE_USER])
                ->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                )
                ->setJoinedAt(new \DateTimeImmutable())
            ;

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $userAuthenticator->authenticateUser($user, $appAuthenticator, $request);
        }

        return $this->render('signup/index.html.twig', [
            'form' => $form
        ]);
    }
}