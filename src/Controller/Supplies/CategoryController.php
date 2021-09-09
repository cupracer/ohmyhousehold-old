<?php

namespace App\Controller\Supplies;

use App\Entity\Supplies\Category;
use App\Entity\Supplies\DTO\CategoryDTO;
use App\Form\Supplies\CategoryType;
use App\Repository\HouseholdRepository;
use App\Service\Supplies\CategoryService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_SUPPLIES")]
#[Route('/{_locale<%app.supported_locales%>}/supplies/components/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'supplies_category_index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->render('supplies/category/index.html.twig', [
            'pageTitle' => t('Categories'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'supplies_category_datatables', methods: ['GET'])]
    public function getAsDatatables(Request $request, CategoryService $categoryService, HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->json(
            $categoryService->getCategoriesAsDatatablesArray($request, $currentHousehold)
        );
    }

//    #[Route('/select2', name: 'housekeepingbook_accountholder_select2', methods: ['GET'])]
//    public function getAsSelect2(Request $request, AccountHolderService $accountHolderService, HouseholdRepository $householdRepository, SessionInterface $session): Response
//    {
//        $currentHousehold = $householdRepository->find($session->get('current_household'));
//
//        return $this->json(
//            $accountHolderService->getAccountHoldersAsSelect2Array($request, $currentHousehold)
//        );
//    }

    #[Route('/new', name: 'supplies_category_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        SessionInterface $session,
        HouseholdRepository $householdRepository
    ): Response
    {
        $household = null;

        if($session->has('current_household')) {
            $household = $householdRepository->find($session->get('current_household'));
        }

        $this->denyAccessUnlessGranted('createSuppliesCategory', $household);

        $category = new Category();
        $createCategory = new CategoryDTO();

        $form = $this->createForm(CategoryType::class, $createCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $category->setName($createCategory->getName());
                $category->setHousehold($household);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($category);
                $entityManager->flush();

                $this->addFlash('success', t('Category was added.'));

                return $this->redirectToRoute('supplies_category_new');
            }catch (Exception) {
                $this->addFlash('error', t('Category could not be created.'));
            }
        }

        return $this->render('supplies/category/form.html.twig', [
            'pageTitle' => t('Add category'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'supplies_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category): Response
    {
        $this->denyAccessUnlessGranted('edit', $category);

        $editCategory = new CategoryDTO();
        $editCategory->setName($category->getName());

        $form = $this->createForm(CategoryType::class, $editCategory, ['category' => $category]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $category->setName($editCategory->getName());

                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', t('Category was updated.'));

                return $this->redirectToRoute('supplies_category_index');
            }catch(Exception) {
                $this->addFlash('error', t('Category could not be updated.'));
            }
        }

        return $this->render('supplies/category/form.html.twig', [
            'pageTitle' => t('Edit category'),
            'form' => $form->createView(),
            'category' => $category,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'supplies_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_category_' . $category->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete' , $category);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($category);
                $entityManager->flush();
                $this->addFlash('success', t('Category was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Category could not be deleted.'));
        }

        return $this->redirectToRoute('supplies_category_index');
    }
}
