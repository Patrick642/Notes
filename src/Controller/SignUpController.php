<?php

namespace App\Controller;

use App\Entity\EmailVerification;
use App\Entity\User;
use App\Form\SignUpType;
use App\Service\EmailSendingService;
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
    // The number of seconds that the email verification link is valid.
    private const TIME_VALID = 48 * 60 * 60;

    public function __construct(
        private EntityManagerInterface $em,
        private EmailSendingService $emailSending,
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

            $this->emailSending->registerVerificationRequest(
                $request,
                $user,
                self::TIME_VALID
            );

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('signup/index.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/email_sent', name: 'app_signup_email_sent')]
    public function emailSentConfirmation(Request $request): Response
    {
        $user = $this->getUser();

        if ($user->getIsVerified())
            return $this->redirectToRoute('app_notes');

        return $this->render('signup/email_sent.html.twig', [
            'email' => $user->getEmail()
        ]);
    }

    #[Route('/email_resend', name: 'app_signup_email_resend')]
    public function resendEmail(Request $request): Response
    {
        $user = $this->getUser();

        if ($user->getIsVerified())
            return $this->redirectToRoute('app_notes');

        $this->emailSending->registerVerificationRequest(
            $request,
            $user,
            self::TIME_VALID
        );

        return $this->redirectToRoute('app_signup_email_sent');
    }

    #[Route('/verify/{authKey}', name: 'app_signup_verify')]
    public function verify(Request $request, string $authKey): Response
    {
        $emailVerification = $this->em->getRepository(EmailVerification::class)->findOneBy(['authKey' => $authKey]);

        if ($emailVerification->getUser()->getIsVerified())
            return $this->redirectToRoute('app_notes');

        if ($emailVerification === NULL) {
            $this->flashMessage->error('The verification link is invalid');
            return $this->redirectToRoute('app_home');
        }

        if ($emailVerification->getExpiresAt() < new \DateTimeImmutable()) {
            $this->flashMessage->error('The verification link has expired');
            return $this->redirectToRoute('app_home');
        }

        $user = $emailVerification->getUser();

        $user->setIsVerified(true);

        $this->em->persist($user);
        $this->em->remove($emailVerification);
        $this->em->flush();

        if ($this->getUser() ?? null == $user)
            $this->em->refresh($user);

        $this->flashMessage->success('Email address has been verified successfully!');
        return $this->redirectToRoute('app_notes');
    }
}