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
    public function show($id, ClientsRepository $repo){
        $show = $repo->findOneById($id);
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

}
