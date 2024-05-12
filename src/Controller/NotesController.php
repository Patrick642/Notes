<?php
namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use App\Service\FlashMessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/notes')]
class NotesController extends AbstractController
{
    private $entityManager;
    private $noteRepository;
    private $flashMessage;

    public function __construct(EntityManagerInterface $entityManager, NoteRepository $noteRepository, FlashMessageService $flashMessage)
    {
        $this->entityManager = $entityManager;
        $this->noteRepository = $noteRepository;
        $this->flashMessage = $flashMessage;
    }

    #[Route('', name: 'app_notes')]
    public function index(Request $request): Response
    {
        $allNotes = $this->noteRepository->findBy(['user' => $this->getUser()]);

        $formAddNote = $this->createForm(NoteType::class);

        return $this->render('notes/index.html.twig', [
            'form_add_note' => $formAddNote,
            'notes' => $allNotes
        ]);
    }

    #[Route('/add', name: 'app_notes_add')]
    public function add(Request $request): Response
    {
        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note
                ->setUser($this->getUser())
                ->setUpdatedAt(new \DateTimeImmutable())
            ;

            $this->entityManager->persist($note);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_notes');
    }

    #[Route('/get_edit_form/{id}', name: 'app_get_edit_form')]
    public function get(Request $request, int $id): Response
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException();

        $note = $this->noteRepository->find($id);

        if ($note->getUser() !== $this->getUser()) {
            return $this->json(['success' => false]);
        }

        $form = $this->createForm(NoteType::class, $note);

        return $this->json([
            'success' => true,
            'edit_form' => $this->renderView(
                'notes/edit.html.twig',
                [
                    'form_edit_note' => $form,
                    'note_id' => $note->getId(),
                    'bg_color' => $note->getColor()
                ]
            )
        ]);
    }

    #[Route('/edit/{id}', name: 'app_notes_edit')]
    public function edit(Request $request, int $id): Response
    {
        $note = $this->noteRepository->find($id);
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($note->getUser() !== $this->getUser())
                throw new AccessDeniedHttpException();

            $note->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
            $this->flashMessage->success('Note has been edited!');
        }

        return $this->redirectToRoute('app_notes');
    }

    #[Route('/delete/{id}', name: 'app_notes_delete')]
    public function delete(Request $request, int $id): Response
    {
        $note = $this->noteRepository->find($id);

        if ($note->getUser() !== $this->getUser())
            throw new AccessDeniedHttpException();

        $this->entityManager->remove($note);
        $this->entityManager->flush();
        $this->flashMessage->success('Note has been deleted!');

        return $this->redirectToRoute('app_notes');
    }
}