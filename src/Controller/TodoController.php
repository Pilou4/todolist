<?php

namespace App\Controller;

use App\Model\TodoModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TodoController extends AbstractController
{
    #[Route('/todos', name: 'todo_list', methods:'GET')]
    public function index(): Response
    {
        $todos =TodoModel::findAll();

        // on verifie que la tache demandée existe bien
        if($todos === false) {
            // on demande a AbsstractController de nous créer une jolie exception
            // puis on la throw (envoi) pour que symfony affiche une page 404
            throw $this->createNotFoundException("La tâche n'existe pas !");
        }

        return $this->render('todo/list.html.twig', [
            'todos' => $todos
        ]);
    }

    
    //Affichage d'une tâche
    #[Route('/todo/{id}', name:'todo_show', methods:['GET'])]
    public function todoShow($id)
    {
        $todo = TodoModel::find($id);

        // on verifie que la tache demandée existe bien
        if($todo === false) {
            // on demande a AbsstractController de nous créer une jolie exception
            // puis on la throw (envoi) pour que symfony affiche une page 404
            throw $this->createNotFoundException("La tâche n°$id n'existe pas !");
        }

        return $this->render('todo/single.html.twig', [
            'todo' => $todo
        ]);
    }

    /**
     * Changement de statut
     *
     * @Route("/todo/{id}/{status}", name="todo_set_status", requirements={"id" = "\d+", "status" = "^(done|undone)$"}, methods={"GET"})
     */
    public function todoSetStatus($id, $status)
    {
        // Modifier le status
        // j'instancie le model pour modifier les données
        $todoModel = new TodoModel();
        // la methode setStatus() me renvoi false si la tache n'éxiste pas
        $result = $todoModel->setStatus($id, $status);

        if($result === false) {
            // on demande a AbsstractController de nous créer une jolie exception
            // puis on la throw (envoi) pour que symfony affiche une page 404
            throw $this->createNotFoundException("La tâche n°$id n'existe pas !");
        }

        // flash message
        // https://symfony.com/doc/current/controller.html#flash-messages
        $this->addFlash(
            'success',
            'La tâche a bien été changé en ' . $status
        );


        // redirection vers la liste des taches
        //https://symfony.com/doc/current/controller.html#redirecting
        return $this->redirectToRoute('todo_list');

    }

    /**
     * Ajout d'une tâche
     *
     * @Route("/todo/add", name="todo_add", methods={"POST"})
     *
     * La route est définie en POST parce qu'on veut ajouter une ressource sur le serveur
     */
    public function todoAdd(Request $request)
    {
        // Je recupérer le titre de la tache à créer
        // j'utilise la request et je demande parmis les données qui ont été postées (propriété request de l'objet Request)
        // je recupère le titre de la tache qui a été fournis dans le champs name="task"
        $title = $request->request->get('task'); // $_POST['task']
        //$request->query->get('var') // $_GET['var']

        // je verifie que les données du formulaire sont correct
        if(empty($title)) {
            // s'il manque le titre on met un message d'erreur 
            $this->addFlash(
                'warning',
                'Impossible de créer une tâche sans titre'
            );
        } else {
            // si on a bien un titre on crée la tache

            // j'ajoute cette tache grace au model
            // je recupère le model (je crée une instance)
            $todoModel = new TodoModel();
            // j'utilise la model pour ajouter une tache en fournissant son titre
            $todoModel->add($title);

            // ajouter un flash messsage pour dire que c'est bon
            $this->addFlash(
                'success',
                'La tâche a bien été créée'
            );
        }


        // redirection vers la page liste des taches  
        //https://symfony.com/doc/current/controller.html#redirecting
        return $this->redirectToRoute('todo_list');
    }


    // creer une methode de controlleur pour la route /todo/{id}/delete 
    // cette route sera accessible avec la methode POST uniquement

    /**
     * @Route("/todo/{id}/delete", name="todo_delete", requirements={"id" = "\d+"}, methods={"POST"})
     */
    public function todoDelete(int $id)
    {
        // supprimer la tache grace au model
        $todoModel = new TodoModel();
        $result = $todoModel->delete($id);

        // un flash message
        if(!$result) {
            throw $this->createNotFoundException("La tâche n°$id n'éxiste pas");
        }

        $this->addFlash(
            'success',
            'La tâche a bien été supprimée'
        );
        // redirection
        return $this->redirectToRoute('todo_list');
    }

    /**
     * @Route("/todo/reset", name="todo_reset", methods={"GET"})
     */
    public function todoReset()
    {
        // reset les todo grace au model
        $todoModel = new TodoModel();
        $todoModel->reset();

        // flash message
        $this->addFlash("info", "Les tâches ont été réinitialisées");

        // redirection
        return $this->redirectToRoute("homepage");
    }

}
