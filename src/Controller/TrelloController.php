<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/trello')]
class TrelloController extends AbstractController
{
    #[Route('/', name: 'app_trello_index')]
    public function index(TaskRepository $taskRepository): Response
    {
        $tasksByStatus = [
            'todo' => $taskRepository->findByStatusOrdered('todo'),
            'inprogress' => $taskRepository->findByStatusOrdered('inprogress'),
            'done' => $taskRepository->findByStatusOrdered('done')
        ];
        
        return $this->render('carnet_educatif/indexTrello.html.twig', [
            'tasksByStatus' => $tasksByStatus,
        ]);
    }
    
    #[Route('/task/new', name: 'app_trello_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $task->setTitle($request->request->get('title'));
        $task->setDescription($request->request->get('description'));
        $task->setStatus($request->request->get('status', 'todo'));
        $task->setIsChecked(false);
        $task->setCreatedAt(new \DateTime());
        
        $dueDate = $request->request->get('dueDate');
        if ($dueDate && !empty($dueDate)) {
            $task->setDueDate(new \DateTime($dueDate));
        }
        
        // 🔥 GESTION SIMPLIFIÉE AVEC VICH UPLOADER
        $uploadedFile = $request->files->get('attachment');
        if ($uploadedFile && $uploadedFile->getSize() > 0) {
            $task->setAttachmentFile($uploadedFile);
        }
        
        $task->setPosition($taskRepository->getNextPosition($task->getStatus()));
        
        $em->persist($task);
        $em->flush();
        
        $this->addFlash('success', '✅ Tâche ajoutée avec succès !');
        return $this->redirectToRoute('app_trello_index');
    }
    
    #[Route('/task/edit/{id}', name: 'app_trello_edit', methods: ['GET', 'POST'])]
    public function edit(Task $task, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $task->setTitle($request->request->get('title'));
            $task->setDescription($request->request->get('description'));
            
            $dueDate = $request->request->get('dueDate');
            if ($dueDate && !empty($dueDate)) {
                $task->setDueDate(new \DateTime($dueDate));
            } else {
                $task->setDueDate(null);
            }
            
            // 🔥 GESTION SIMPLIFIÉE AVEC VICH UPLOADER
            $uploadedFile = $request->files->get('attachment');
            if ($uploadedFile && $uploadedFile->getSize() > 0) {
                $task->setAttachmentFile($uploadedFile);
            }
            
            $em->flush();
            $this->addFlash('success', '✏️ Tâche modifiée !');
            return $this->redirectToRoute('app_trello_index');
        }
        
        return $this->render('carnet_educatif/editTrello.html.twig', [
            'task' => $task
        ]);
    }
    
    #[Route('/task/move/{id}', name: 'app_trello_move', methods: ['POST'])]
    public function move(Task $task, Request $request, EntityManagerInterface $em, TaskRepository $taskRepository): JsonResponse
    {
        $newStatus = $request->request->get('status');
        
        if ($newStatus && $newStatus !== $task->getStatus()) {
            $task->setStatus($newStatus);
            $task->setPosition($taskRepository->getNextPosition($newStatus));
            $em->flush();
        }
        
        return $this->json(['success' => true]);
    }
    
    #[Route('/task/toggle/{id}', name: 'app_trello_toggle', methods: ['POST'])]
    public function toggle(Task $task, EntityManagerInterface $em): JsonResponse
    {
        $task->setIsChecked(!$task->isIsChecked());
        $em->flush();
        
        return $this->json(['success' => true, 'checked' => $task->isIsChecked()]);
    }
    
    #[Route('/task/delete/{id}', name: 'app_trello_delete', methods: ['POST'])]
    public function delete(Task $task, EntityManagerInterface $em): Response
    {
        // 🔥 Vich supprime automatiquement le fichier grâce à delete_on_remove: true
        $em->remove($task);
        $em->flush();
        
        $this->addFlash('success', '🗑️ Tâche supprimée !');
        return $this->redirectToRoute('app_trello_index');
    }
}