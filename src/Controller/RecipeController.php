<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{
   


    #[Route('/recette', name: 'recipe')]
    public function index(RecipeRepository $recipeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $recipes = $paginator->paginate(
            $recipeRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }


    #[Route('/recette/creation', 'recipe.new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
           

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash( 'success','Votre recette a été créé avec succès !');

            return $this->redirectToRoute('recipe');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/recette/edition/{id}', 'recipe.edit')]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $manager): Response
     {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash('success', 'Votre recette a été modifié avec succès !');

            return $this->redirectToRoute('recipe');
        }

        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }



    #[Route('/recette/suppression/{id}', 'recipe.delete')]
        public function delete(EntityManagerInterface $manager,Recipe $recipe): Response
     {
        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash('success','Votre recette a été supprimé avec succès !');

        return $this->redirectToRoute('recipe');
    }



}








































