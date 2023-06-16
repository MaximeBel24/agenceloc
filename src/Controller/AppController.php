<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Commande;
use App\Entity\Vehicule;
use App\Form\CommandeType;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }

    #[Route('app/blogVehicule', name:"blog_vehicule")]
    public function blog(VehiculeRepository $repo) : Response
    {
        $vehicules = $repo->findBy([],['date_enregistrement' => "DESC"]);
        return $this->render('app/blogVehicule.html.twig', [
            'vehicules' => $vehicules
        ]);
    }

    #[Route("/show/{id}", name: "show_vehicule")]
    public function show( Vehicule $vehicule) :Response
    {
        if($vehicule == null)
        {
            return $this->redirectToRoute('home');
        }

        return $this->render('app/showVehicule.html.twig', [
            'vehicule' => $vehicule,
        ]);
    }

    #[Route("/show/commande/{id}", name: "show_commande")]
    public function showCommande( Commande $commande) :Response
    {
        if($commande == null)
        {
            return $this->redirectToRoute('home');
        }

        return $this->render('app/showCommande.html.twig', [
            'commande' => $commande,
        ]);
    }

    // #[Route("/commande/edit/{id}", name:"edit_commande")]
    #[Route('/show/formCommande/{id}', name: 'form_commande')]
    public function formCommande(EntityManagerInterface $manager, Request $request,Vehicule $vehicule = null): Response 
    {
        if ($vehicule == null) 
        {
            return $this->redirectToRoute('app_admin');
        }

        $commande = new Commande();
        $membre = $this->getUser();

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateDebut = $commande->getDateHeureDepart();
            $dateFin = $commande->getDateHeureFin();
            $nombreJours = $dateFin->diff($dateDebut)->days;

            $prixJournalier = $vehicule->getPrixJournalier();
            $prixTotal = $prixJournalier * $nombreJours;

            $commande
                ->setDateEnregistrement(new \DateTime())
                ->setPrixTotal($prixTotal)
                ->setVehicule($vehicule)
                ->setMembre($membre);

            $manager->persist($commande);
            $manager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('app/formCommande.html.twig', [
            'vehicule' => $vehicule,
            'commandeForm' => $form->createView(),
            // "editMode" => $commande->getId() !== null

        ]);
    }
}
