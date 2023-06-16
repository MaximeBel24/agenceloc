<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Commande;
use App\Entity\Vehicule;
use App\Form\MembreType;
use App\Form\VehiculeType;
use App\Form\EditCommandeType;
use App\Repository\MembreRepository;
use App\Repository\CommandeRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/gestionVehicule', name: 'gestion_vehicule')]
    public function adminVehicule(VehiculeRepository $repo, EntityManagerInterface $manager)
    {
        $colonnes = $manager->getClassMetadata(Vehicule::class)->getFieldNames();

        $vehicules = $repo->findAll();
        return $this->render('admin/gestionVehicule.html.twig', [
            "colonnes" => $colonnes,
            "vehicules" => $vehicules
        ]);
    }

    #[Route('/admin/gestionMembre', name: 'gestion_membre')]
    public function adminMembre(MembreRepository $repo, EntityManagerInterface $manager)
    {
        $colonnes = $manager->getClassMetadata(Membre::class)->getFieldNames();

        $membres = $repo->findAll();
        return $this->render('admin/gestionMembre.html.twig', [
            "colonnes" => $colonnes,
            "membres" => $membres
        ]);
    }

    #[Route('/admin/gestioncommande', name: 'gestion_commande')]
    public function admincommande(CommandeRepository $repo, EntityManagerInterface $manager)
    {
        $colonnes = $manager->getClassMetadata(Commande::class)->getFieldNames();

        $commandes = $repo->findAll();
        return $this->render('admin/gestionCommande.html.twig', [
            "colonnes" => $colonnes,
            "commandes" => $commandes
        ]);
    }

    #[Route("/admin/vehicule/edit/{id}", name:"edit_vehicule")]
    #[Route('/admin/vehicule/new', name:'new_vehicule')]
    public function formVehicule(Request $globals, EntityManagerInterface $manager, Vehicule $vehicule = null)
    {
        if($vehicule == null)
        {
            $vehicule = new Vehicule;
        }        
        $form= $this->createForm(VehiculeType::class, $vehicule );

        $form->handleRequest($globals);

        if($form->isSubmitted() && $form->isValid())
        {
            $vehicule->setDateEnregistrement(new \DateTime);
            $manager->persist($vehicule);
            $manager->flush();
            $this->addFlash('success', "Le vehicule a bien été ajouté");          
            return $this->redirectToRoute('gestion_vehicule');
        }

        return $this->render("admin/formVehicule.html.twig", [
            "formVehicule" => $form,
            "editMode" => $vehicule->getId() !== null
        ]);
    }

    #[Route("/admin/membre/edit/{id}", name:"edit_membre")]
    public function formMembre(Request $request, EntityManagerInterface $entityManager, Membre $user = null): Response
    {
        if($user == null)
        {
            $user = new Membre();
        }       
        $form = $this->createForm(MembreType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $statut = $form->get('statut')->getData();

            if ($statut == 1) {
                $role = 'ROLE_ADMIN';
            } elseif ($statut == 2) {
                $role = 'ROLE_USER';
            } else {
                
                $role = 'ROLE_USER';
            }

            $user->setRoles([$role]);

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('gestion_membre');
        }

        return $this->render('admin/formMembre.html.twig', [
            'membreForm' => $form->createView(),
            "editMode" => $user->getId() !== null
        ]);
    }

    #[Route("/admin/vehicule/delete/{id}", name:'delete_vehicule')]
    public function deleteVehicule(Vehicule $vehicule, EntityManagerInterface $manager)
    {
        $manager->remove($vehicule);
        $manager->flush();
        $this->addFlash('success', "Le vehicule a bien été supprimer");
        return $this->redirectToRoute('gestion_vehicule');
    }

    #[Route("/admin/membre/delete/{id}", name:'delete_membre')]
    public function deleteMembre(Membre $membre, EntityManagerInterface $manager)
    {
        $manager->remove($membre);
        $manager->flush();
        $this->addFlash('success', "Le membre a bien été supprimer");
        return $this->redirectToRoute('gestion_membre');
    }   


    #[Route("/blog/show/{id}", name: "show_membre")]
    public function showMembre( Membre $membre =null) :Response
    {
        if($membre == null)
        {
            return $this->redirectToRoute('home');
        }

        return $this->render('admin/showMembre.html.twig', [
            'membre' => $membre,
        ]);
    }

    #[Route("/admin/commande/edit/{id}", name:"admin_edit_commande")]
    public function formCommande(EntityManagerInterface $manager, Request $request,Vehicule $vehicule = null, Commande $commande): Response 
    {
        if ($commande == null) 
        {
            $commande = new Commande;
        }

        $commande;
        $membre = $this->getUser();

        $form = $this->createForm(EditCommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            

            $commande
                ->setDateEnregistrement(new \DateTime())
                // ->setVehicule($vehicule)
                ->setMembre($membre);

            $manager->persist($commande);
            $manager->flush();

            return $this->redirectToRoute('gestion_commande');
        }

        return $this->render('admin/formEditCommande.html.twig', [
            'commande' => $commande,
            'commandeForm' => $form->createView(),
        ]);
    }

    #[Route('/admin/commande/delete/{id}', name: 'admin_delete_commande')]
    public function deleteCommande(Commande $commande, EntityManagerInterface $manager): Response
    {
        $manager->remove($commande);
        $manager->flush();

        $this->addFlash('success', 'La commande a été supprimée avec succès.');

        return $this->redirectToRoute('gestion_commande');
    }
}
