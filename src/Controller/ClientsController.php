<?php

namespace App\Controller;

use App\Entity\Clients;
use App\Form\ClientsType;
use App\Repository\ClientsRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ClientsController extends AbstractController
{

/* -------------------------------------------------------------------------- */
    /**
     * Ajouter un client 
     * @Route("/add/client", name="add_client")
     */
    public function addClient(Request $request, ObjectManager $manager)
    {
        // creation d'un client
        $clients = new Clients();

        // creation du formulaire via le formbuilder
        $formClient = $this->createForm(ClientsType::class, $clients);
        
        // recuperation dans la requete
        $data = $formClient->handleRequest($request);
        // dump($data);

        // enregistrement en base de données si ok
        if($formClient->isSubmitted() && $formClient->isValid()){
            $manager->persist($clients);
            $manager->flush();

            // si le client a etait enregistrer en base de données 
            // affiche un message de success
            $this->addFlash(
            	'success',
                "le client a etait enregistré !"
            );
            return $this->redirectToRoute('list_clients');
        }
        
        return $this->render('clients/index.html.twig', [
            'controller_name' => 'ClientsController',
            'formclient' => $formClient->createView()
        ]);
    }



/* -------------------------------------------------------------------------- */
    /**
     * liste des clients enregistrer
     * @Route("/list/clients", name="list_clients")
     */
    public function listClient()
    {
        // on demande a Doctrine de nous donner un Repository avec getRepository()
        $repo = $this->getDoctrine()->getRepository(Clients::class);
         // on demande au $repo daller trouver toute les clients dans la BDD
        $clients = $repo->findAll();

        return $this->render('clients/list.html.twig', [
            'controller_name' => 'ListClientsController',
            'clients' => $clients 
        ]);
    }


/* -------------------------------------------------------------------------- */
    /**
     * Afficher un client
     * @Route("/show/client/{id}", name="show_client")
     */
    public function show(Clients $clients, ClientsRepository $repo){
        $show = $repo->findOneById($clients);
        return $this->render("clients/show.html.twig", [
            'show' => $show
        ]);
    }

    
/* -------------------------------------------------------------------------- */
    /**
     * suppresion d'un client
     * @Route("/delete/client/{id}", name="delete_client")
     */
    public function delete($id){
        // on demande a doctrine le manager d'entité
        $em = $this->getDoctrine()->getManager();
        // on recupere le client dans une variable client
        $client = $em->getRepository(Clients::class)->find($id);
        // si le client n'existe pas affiche un message d'erreur
        if(!$client){
            throw $this->createNotFoundException('le client n\'existe pas');
        }
        // demander au manager de supprimer le client
        $em->remove($client);
        // persiste en base de données
        $em->flush();
        $this->addFlash(
            'danger',
            "le client a etait supprimé !"
        );
        // redirection
        return $this->redirectToRoute('list_clients');
    }

/* -------------------------------------------------------------------------- */
    /**
     * editer un client
     * @Route("/edit/client/{id}", name="edit_client")
     */
    public function edit(Clients $clients, Request $request, ObjectManager $manager){

        $form = $this->createForm(ClientsType::class, $clients);

        $data = $form->handleRequest($request);

        if(!$clients){
            throw $this->createNotFoundException('le client n\'existe pas');
        }

        // condition + envoi dans la bas de données
        if ($form->isSubmitted() && $form->isValid()){
                // on a besoin du manager doctrine qui est injecter dans la fonction
                $manager->persist($clients);
                $manager->flush();
                // message flash
                $this->addFlash(
                    "success",
                    "le client à bien atait modifier"
                );
                // redirection
                return $this->redirectToRoute('list_clients');
            }

        return $this->render('clients/edit.html.twig', [
            'form' => $form->createView(),
            // 'clients' => $clients
        ]);

    }
    

}
