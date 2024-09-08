<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredienType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IngredientController extends AbstractController
{

    
    #[Route('/ingredient', name: 'ingredient')]
    #[IsGranted('ROLE_USER')]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {

        $ingredients = $paginator->paginate(
            $repository->findBy (['user' => $this->getUser ()]),
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

      
        return $this->render('pages/ingredient/index.html.twig',[
            'ingredients' => $ingredients
        ]);
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/ingredient/nouveau', name: 'ingredient.new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {

        $ingredient = new Ingredient();
        $form = $this->createForm(IngredienType::class, $ingredient);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());


            $manager->persist($ingredient);
            $manager->flush();


            $this->addFlash('success','Votre ingrédient a été créé avec succès !' );
            
            return $this->redirectToRoute('ingredient');
        }

        return $this->render('pages/ingredient/new.html.twig',[
           'form'=> $form->createView()
        ]);

    }

    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    #[Route('/ingredient/edition/{id}', name: 'ingredient.edit')]
    public function edit(Ingredient $ingredient,Request $request,EntityManagerInterface $manager ): Response
     {
        $form = $this->createForm(IngredienType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash('success','Votre ingrédient a été modifié avec succès !' );

            return $this->redirectToRoute('ingredient');
        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    #[Route('/ingredient/suppression/{id}', 'ingredient.delete')]
    public function delete(EntityManagerInterface $manager, Ingredient $ingredient): Response
     {
        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash('success','Votre ingrédient a été supprimé avec succès !' );

        return $this->redirectToRoute('ingredient');
    }








}