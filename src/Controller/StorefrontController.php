<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Marketing;


class StorefrontController extends AbstractController
{

    #[Route('/', name: 'storefront_index')]
    public function index(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $productRepository = $entityManager->getRepository(Product::class);
        $products = $productRepository->findAll();
        return $this->render('storefront/index.html.twig', [
            'products' => $products,
        ]);
    }

    //Use sessions if email is already submitted
    #[Route('/download-email-form/{id}', name: 'storefront_download_email_form', methods: ['GET', 'POST'])]
    public function downloadEmailForm(int $id, Request $request, EntityManagerInterface $em){
        $product = $em->getRepository(Product::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $form = $this->createFormBuilder()->add('email', EmailType::class)->add('emailDownloadSubmit', SubmitType::class)->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $marketing = new Marketing();
            $data = $form->getData();
            $email = $data['email'];
            $marketing->setEmail($email);
            $marketing->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
            $em->persist($marketing);
            $em->flush();

            return $this->render('storefront/download_button.html.twig', [
                'id' => $id,
                'product' => $product,
            ]);
        }
        
        return $this->render('storefront/download_email_form.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'product' => $product,
        ]);
    }

    #[Route('/download/{id}', name: 'storefront_download', methods: ['GET'])]
    public function download(int $id, Request $request, EntityManagerInterface $em){
        $product = $em->getRepository(Product::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
        $finder = 
        //Public path to the file
        $file = $this->getParameter('kernel.project_dir') . '\public\uploads\products\\' . $product->getFolderPath() . '\download\etsy-instructions-0001.pdf';
        return new BinaryFileResponse($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="thank_you_and_instructions_template.pdf"',
        ]);
    }
}