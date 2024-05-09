<?php
namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NotesController extends AbstractController
{
    private $entityManager;
    private $noteRepository;

    public function __construct(EntityManagerInterface $entityManager, NoteRepository $noteRepository)
    {
        $this->entityManager = $entityManager;
        $this->noteRepository = $noteRepository;
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/notes', name: 'app_notes')]
    public function index(Request $request): Response
    {
        $allNotes = $this->noteRepository->findBy(['userId' => $this->getUser()->getId()]);

        $formAddNote = $this->createForm(NoteType::class, options: [
            'action' => $this->generateUrl('app_notes_add'),
            'method' => 'POST'
        ]);

        $formEditNote = $this->createForm(NoteType::class, options: [
            'method' => 'POST'
        ]);

        return $this->render('notes/index.html.twig', [
            'form_add_note' => $formAddNote,
            'form_edit_note' => $formEditNote,
            'notes' => $allNotes
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/notes/add', name: 'app_notes_add')]
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

    #[IsGranted('ROLE_USER')]
    #[Route('/notes/get_note/{id}', name: 'notes_get')]
    public function get(Request $request, int $id): Response
    {
        $note = $this->noteRepository->find($id);

        if ($note->getUserId() !== $this->getUser()->getId()) {
            $this->addFlash('error', 'You cannot perform this action.');
            return $this->redirectToRoute('app_notes');
        }

        $form = $this->createForm(NoteType::class, $note);

        return $this->json(
            $this->renderView(
                'notes/edit.html.twig',
                [
                    'form_edit_note' => $form,
                    'note_id' => $note->getId(),
                    'bg_color' => $note->getColor()
                ]
            )
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/notes/edit/{id}', name: 'app_notes_edit')]
    public function edit(Request $request, int $id): Response
    {
        $note = $this->noteRepository->find($id);

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($note->getUserId() !== $this->getUser()->getId()) {
                $this->addFlash('error', 'You cannot perform this action.');
                return $this->redirectToRoute('app_notes');
            }

            $note->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
            $this->addFlash('success', 'Note has been edited!');
        }

        return $this->redirectToRoute('app_notes');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/notes/delete/{id}', name: 'app_notes_delete')]
    public function delete(Request $request, int $id): Response
    {
        $note = $this->noteRepository->find($id);

        if ($note->getUserId() !== $this->getUser()->getId()) {
            $this->addFlash('error', 'You cannot perform this action.');
            return $this->redirectToRoute('app_notes');
        }

        $this->entityManager->remove($note);
        $this->entityManager->flush();

        $this->addFlash('success', 'Note has been deleted!');

        return $this->redirectToRoute('app_notes');
    }
}