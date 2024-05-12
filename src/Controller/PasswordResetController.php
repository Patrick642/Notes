<?php
namespace App\Controller;

use App\Entity\PasswordReset;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\PasswordResetType;
use App\Repository\PasswordResetRepository;
use App\Repository\UserRepository;
use App\Service\FlashMessageService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/password_reset')]
class PasswordResetController extends AbstractController
{
    private $entityManager;
    private $passwordResetRepository;
    private $flashMessage;
    // The number of minutes that the password reset link is valid. For security reasons, this value should not be too high.
    private const TIME_VALID = 15;

    public function __construct(EntityManagerInterface $entityManager, PasswordResetRepository $passwordResetRepository, FlashMessageService $flashMessage)
    {
        $this->entityManager = $entityManager;
        $this->passwordResetRepository = $passwordResetRepository;
        $this->flashMessage = $flashMessage;
    }

    #[Route('', name: 'app_password_reset')]
    public function index(Request $request, MailerInterface $mailer, ManagerRegistry $registry): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('app_notes');

        $passwordReset = new PasswordReset();
        $user = new UserRepository($registry);
        $form = $this->createForm(PasswordResetType::class, $passwordReset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            if ($user->findOneBy(['email' => $email]) !== null) {
                $resetKey = substr(bin2hex(random_bytes(128)), 0, 255);

                $passwordReset->setEmail($email)
                    ->setResetKey($resetKey)
                    ->setExpire(new \DateTimeImmutable('+' . self::TIME_VALID . ' minutes'))
                ;

                $resetLink = $request->getSchemeAndHttpHost() . $this->generateUrl('app_password_reset_reset', [
                    'key' => $resetKey
                ]);

                $this->entityManager->persist($passwordReset);
                $this->entityManager->flush();

                $email = (new TemplatedEmail)
                    ->to($email)
                    ->subject('Reset your password')
                    ->textTemplate('emails/password_reset.txt.twig')
                    ->htmlTemplate('emails/password_reset.html.twig')
                    ->context([
                        'base_url' => $request->getSchemeAndHttpHost(),
                        'reset_link' => $resetLink,
                        'time_valid' => self::TIME_VALID
                    ])
                ;

                $mailer->send($email);
                return $this->redirectToRoute('app_check_email');
            }

            $form->addError(new FormError('The email address provided is not associated with any account.'));
        }

        return $this->render('password_reset/index.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/sent', name: 'app_check_email')]
    public function emailSentConfirmation(Request $request): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('app_notes');

        return $this->render('password_reset/check_email.html.twig');
    }

    #[Route('/reset/{key}', name: 'app_password_reset_reset')]
    public function reset(Request $request, string $key, ManagerRegistry $registry, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('app_notes');

        $passwordReset = $this->passwordResetRepository->findOneBy(['reset_key' => $key]);

        if ($passwordReset === NULL) {
            $this->flashMessage->error('The password reset link is invalid.');
            return $this->redirectToRoute('app_password_reset');
        }

        if ($passwordReset->getExpire() < new \DateTimeImmutable()) {
            $this->flashMessage->error('The password reset link has expired.');
            return $this->redirectToRoute('app_password_reset');
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $passwordReset->getEmail(),
        ]);

        if ($user === NULL) {
            $this->flashMessage->error('The email is not assigned to any account.');
            return $this->redirectToRoute('app_password_reset');
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($passwordReset->getExpire() < new \DateTimeImmutable()) {
                $this->flashMessage->error('The password reset link has expired.');
                return $this->redirectToRoute('app_password_reset');
            }

            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            $this->entityManager->remove($passwordReset);
            $this->entityManager->flush();
            $this->flashMessage->success('The password has been successfully changed! You can now log in to your account with your new password.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('password_reset/reset.html.twig', [
            'form' => $form,
            'email' => $passwordReset->getEmail()
        ]);
    }
}