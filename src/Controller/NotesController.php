<?php
namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use App\Service\FlashMessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notes')]
class NotesController extends AbstractController
{
    private const MAX_NOTES_PER_PAGE = 20;
    private const NOTES_RENDER_ORDER_FIELD = 'updatedAt';
    private const NOTES_RENDER_ORDER_SORT = 'DESC';

    public function __construct(
        private EntityManagerInterface $em,
        private FlashMessageService $flashMessage
    ) {
    }

    #[Route('', name: 'app_notes')]
    public function index(Request $request): Response
    {
        $allNotes = $this->em->getRepository(Note::class)->findBy(['user' => $this->getUser()], [self::NOTES_RENDER_ORDER_FIELD => self::NOTES_RENDER_ORDER_SORT], self::MAX_NOTES_PER_PAGE);

        $formAddNote = $this->createForm(NoteType::class);

        return $this->render('notes/index.html.twig', [
            'formAddNote' => $formAddNote,
            'notes' => $allNotes,
            'userHasMoreNotes' => ($this->getUserNoteCount() > self::MAX_NOTES_PER_PAGE) ? true : false
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

            $this->em->persist($note);
            $this->em->flush();
        }

        return $this->redirectToRoute('app_notes');
    }

    #[Route('/get_edit_form/{id}', name: 'app_notes_get_edit_form', condition: 'request.isXmlHttpRequest()')]
    public function get(Request $request, int $id): Response
    {
        $note = $this->em->getRepository(Note::class)->find($id);

        if ($note->getUser() !== $this->getUser()) {
            return $this->json(['success' => false]);
        }

        $form = $this->createForm(NoteType::class, $note);

        return $this->json([
            'success' => true,
            'formEdit' => $this->renderView(
                'notes/edit.html.twig',
                [
                    'formEditNote' => $form,
                    'noteId' => $note->getId(),
                    'bgColor' => $note->getColor()
                ]
            )
        ]);
    }

    #[Route('/edit/{id}', name: 'app_notes_edit')]
    public function edit(Request $request, int $id): Response
    {
        $note = $this->em->getRepository(Note::class)->find($id);
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($note->getUser() !== $this->getUser())
                throw new AccessDeniedHttpException();

            $note->setUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();
            $this->flashMessage->success('Note has been edited!');
        }

        return $this->redirectToRoute('app_notes');
    }

    #[Route('/delete/{id}', name: 'app_notes_delete')]
    public function delete(Request $request, int $id): Response
    {
        $note = $this->em->getRepository(Note::class)->find($id);

        if ($note->getUser() !== $this->getUser())
            throw new AccessDeniedHttpException();

        $this->em->remove($note);
        $this->em->flush();
        $this->flashMessage->success('Note has been deleted!');

        return $this->redirectToRoute('app_notes');
    }

    // More notes for infinite scroll.
    #[Route('/getmore', name: 'app_notes_get_more', condition: 'request.isXmlHttpRequest()')]
    public function getMore(Request $request): Response
    {
        $offset = $request->query->get('offset');
        $limit = self::MAX_NOTES_PER_PAGE;
        $render = '';

        $notes = $this->em->getRepository(Note::class)->createQueryBuilder('n')
            ->where('n.user=:user')
            ->setParameter('user', $this->getUser())
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('n.' . self::NOTES_RENDER_ORDER_FIELD, self::NOTES_RENDER_ORDER_SORT)
            ->getQuery()
            ->getResult()
        ;

        foreach ($notes as $note) {
            $render .= $this->renderView('notes/note.html.twig', [
                'note' => $note
            ]);
        }

        return $this->json([
            'success' => true,
            'render' => $render,
            'isLast' => ($this->getUserNoteCount() <= $offset + self::MAX_NOTES_PER_PAGE) ? true : false
        ]);
    }

    private function getUserNoteCount(): int
    {
        $count = $this->em->getRepository(Note::class)->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user=:user')
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $count;
    }
}