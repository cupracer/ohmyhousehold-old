<?php

namespace App\Controller\Supplies;

use App\Entity\Supplies\Product;
use App\Entity\Supplies\DTO\ProductDTO;
use App\Form\Supplies\ProductType;
use App\Repository\HouseholdRepository;
use App\Service\Supplies\ProductService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_SUPPLIES")]
#[Route('/{_locale<%app.supported_locales%>}/supplies/product')]
class ProductController extends AbstractController
{
    private ManagerRegistry $managerRegistry;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, ManagerRegistry $managerRegistry)
    {
        $this->requestStack = $requestStack;
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/', name: 'supplies_product_index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('supplies/product/index.html.twig', [
            'pageTitle' => t('Products'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'supplies_product_datatables', methods: ['GET'])]
    public function getAsDatatables(Request $request, ProductService $productService, HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $productService->getProductsAsDatatablesArray($request, $currentHousehold, false)
        );
    }

    #[Route('/select2', name: 'supplies_product_select2', methods: ['GET'])]
    public function getAsSelect2(Request $request, ProductService $productService, HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $productService->getProductsAsSelect2Array($request, $currentHousehold, false)
        );
    }

    #[Route('/in-use/select2', name: 'supplies_product_inuse_select2', methods: ['GET'])]
    public function getInUseAsSelect2(Request $request, ProductService $productService, HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $productService->getProductsAsSelect2Array($request, $currentHousehold, true)
        );
    }

    #[Route('/new', name: 'supplies_product_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        HouseholdRepository $householdRepository
    ): Response
    {
        $household = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $this->denyAccessUnlessGranted('createSuppliesProduct', $household);

        $product = new Product();
        $createProduct = new ProductDTO();

        $form = $this->createForm(ProductType::class, $createProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $product->setSupply($createProduct->getSupply());
                $product->setName($createProduct->getName());
                $product->setBrand($createProduct->getBrand());
                $product->setEan($createProduct->getEan());
                $product->setMeasure($createProduct->getMeasure());
                $product->setQuantity($createProduct->getQuantity());
                $product->setOrganicCertification($createProduct->getOrganicCertification());
                $product->setPackaging($createProduct->getPackaging());
                $product->setMinimumNumber($createProduct->getMinimumNumber());
                $product->setHousehold($household);

                $entityManager = $this->managerRegistry->getManager();
                $entityManager->persist($product);
                $entityManager->flush();

                $this->addFlash('success', t('Product was added.'));

                return $this->redirectToRoute('supplies_product_new');
            }catch (Exception) {
                $this->addFlash('error', t('Product could not be created.'));
            }
        }

        return $this->render('supplies/product/form.html.twig', [
            'pageTitle' => t('Add product'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'supplies_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product): Response
    {
        $this->denyAccessUnlessGranted('edit', $product);

        $editProduct = new ProductDTO();
        $editProduct->setSupply($product->getSupply());
        $editProduct->setName($product->getName());
        $editProduct->setBrand($product->getBrand());
        $editProduct->setEan($product->getEan());
        $editProduct->setMeasure($product->getMeasure());
        $editProduct->setQuantity($product->getQuantity());
        $editProduct->setOrganicCertification($product->getOrganicCertification());
        $editProduct->setPackaging($product->getPackaging());
        $editProduct->setMinimumNumber($product->getMinimumNumber());

        $form = $this->createForm(ProductType::class, $editProduct, ['product' => $product]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $product->setSupply($editProduct->getSupply());
                $product->setName($editProduct->getName());
                $product->setBrand($editProduct->getBrand());
                $product->setEan($editProduct->getEan());
                $product->setMeasure($editProduct->getMeasure());
                $product->setQuantity($editProduct->getQuantity());
                $product->setOrganicCertification($editProduct->getOrganicCertification());
                $product->setPackaging($editProduct->getPackaging());
                $product->setMinimumNumber($editProduct->getMinimumNumber());

                $this->managerRegistry->getManager()->flush();

                $this->addFlash('success', t('Product was updated.'));

                return $this->redirectToRoute('supplies_product_index');
            }catch(Exception) {
                $this->addFlash('error', t('Product could not be updated.'));
            }
        }

        return $this->render('supplies/product/form.html.twig', [
            'pageTitle' => t('Edit product'),
            'form' => $form->createView(),
            'product' => $product,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'supplies_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_product_' . $product->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete' , $product);
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->remove($product);
                $entityManager->flush();
                $this->addFlash('success', t('Product was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Product could not be deleted.'));
        }

        return $this->redirectToRoute('supplies_product_index');
    }
}
