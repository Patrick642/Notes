<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SignUpType;
use App\Service\FlashMessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/signup')]
class SignUpController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private FlashMessageService $flashMessage
    ) {
    }

    #[Route('', name: 'app_signup')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('app_notes');

        $user = new User();
        $form = $this->createForm(SignUpType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $user->setRoles([$user::ROLE_USER])
                ->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                )
                ->setJoinedAt(new \DateTimeImmutable())
            ;

            $this->em->persist($user);
            $this->em->flush();

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('signup/index.html.twig', [
            'form' => $form
        ]);
    }
}