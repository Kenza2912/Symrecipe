<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Entity\Ingredient;
use App\Repository\MarkRepository;

use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;

use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class RecipeController extends AbstractController
{
   

    #[IsGranted('ROLE_USER')]
    #[Route('/recette', name: 'recipe')]
    public function index(RecipeRepository $recipeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $recipes = $paginator->paginate(
            $recipeRepository->findBy (['user' => $this->getUser ()]),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recette/communaute', 'recipe.community')]
    public function indexPublic(RecipeRepository $repository, PaginatorInterface $paginator, Request $request ): Response
     {
        
        $data = $cache->get('recipes', function (ItemInterface $item) use ($repository) {
            $item->expiresAfter(15);
            return $repository->findPublicRecipe(null);
        });

        $recipes = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/recipe/community.html.twig', [
            'recipes' => $recipes
        ]);
    }



    #[IsGranted('ROLE_USER')]
    #[Route('/recette/creation', 'recipe.new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());
           

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash( 'success','Votre recette a été créé avec succès !');

            return $this->redirectToRoute('recipe');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
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



    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
    #[Route('/recette/suppression/{id}', 'recipe.delete')]
        public function delete(EntityManagerInterface $manager,Recipe $recipe): Response
     {
        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash('success','Votre recette a été supprimé avec succès !');

        return $this->redirectToRoute('recipe');
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/recette/{id}', 'recipe.show')]
    public function show(Recipe $recipe,Request $request, EntityManagerInterface $manager, MarkRepository $markRepository,): Response 
    {
      

        if (!$recipe->getIsPublic() && $this->getUser() !== $recipe->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette recette.');
        }

        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);

            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if (!$existingMark) {
                $manager->persist($mark);
            } else {
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
            }

            $manager->flush();

            $this->addFlash(
                'success',
                'Votre note a bien été prise en compte.'
            );

            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }

        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
    }




}








































