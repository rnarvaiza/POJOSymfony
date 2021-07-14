<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Entity\Category;
use App\Form\Model\BookDto;
use App\Form\Model\CategoryDto;
use App\Form\Type\BookFormType;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use App\Service\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\Response;


class BooksController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(path="/books")
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */

    public function getAction(
        BookRepository $bookRepository
    )
    {
        return $bookRepository->findAll();
    }


    /**
     * @Rest\Post(path="/books")
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */

    public function postAction(
        EntityManagerInterface $em,
        Request $request,
        FileUploader $fileUploader
    )
    {
        $bookDto = new BookDto();
        $form = $this->createForm(BookFormType::class, $bookDto);
        $form->handleRequest($request);

        //Con isSubmitted, la función getName del BookFormType ya nos indica si pasa o no pasa el submit.
        //Con isValid, si cumple la validación de config/validator/BookDto.yaml nos devuelve un $bookDto, sino un $form que fos rest bundle es capaz de serializar con el contenido del error.
        if(!$form->isSubmitted()) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }
        if($form->isValid()){
            $book = new Book();
            $book->setTitle($bookDto->title);

            if($bookDto->base64Image){
                $filename = $fileUploader->uploadBase64File($bookDto->base64Image);
                $book->setImage($filename);
            }
            $em->persist($book);
            $em->flush();
            return $book;
        }
        return $form;
    }

    /**
     * @Rest\Post(path="/books/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */

    public function editAction(
        int $id,
        EntityManagerInterface $em,
        BookRepository $bookRepository,
        CategoryRepository $categoryRepository,
        Request $request,
        FileUploader $fileUploader
    ){
        $book = $bookRepository->find($id);
        if(!$book){
            throw $this->createNotFoundException('Book not found');
        }
        $bookDto = BookDto::createFromBook($book);

        $originalCategories = new ArrayCollection();
        foreach ($book->getCategories() as $category){
            $categoyDto = CategoryDto::createFromCategory($category);
            $bookDto->categories[] = $categoyDto;
            $originalCategories->add($categoyDto);
        }
        $form = $this->createForm(BookFormType::class, $bookDto);
        $form->handleRequest($request);
        if(!$form->isSubmitted()){
            return new Response('', Response::HTTP_BAD_REQUEST);
        }
        if($form->isValid()){

            //remove categories decided by user.
            foreach($originalCategories as $originalCategoryDto){
                if(!in_array($originalCategories, $bookDto->categories)){
                    $category = $categoryRepository->find($originalCategoryDto->id);
                    $book->removeCategory($category);
                }
            }

            //add categories

            foreach($bookDto->categories as $newCategoryDto){
                if(!$originalCategories->contains($newCategoryDto)){
                    $category = $categoryRepository->find($newCategoryDto->id ?? 0);
                    if(!$category){
                        $category = new Category();
                        $category->setName($newCategoryDto->name);
                        $em->persist($category);
                    }
                    $book->addCategory($category);
                }
            }
            $book->setTitle($bookDto->title);
            if($bookDto->base64Image){
                $filename = $fileUploader->uploadBase64File($bookDto->base64Image);
                $book->setImage($filename);
            }
            $em->persist($book);
            $em->flush();
            $em->refresh($book);
            return $book;
        }
        return $form;
    }
}