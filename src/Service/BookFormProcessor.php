<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Category;
use App\Form\Model\BookDto;
use App\Form\Model\CategoryDto;
use App\Form\Type\BookFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookFormProcessor
{

    private $bookManager;
    private $categoryManager;
    private $fileUploader;
    private $formFactory;

    public function __construct(
        BookManager $bookManager,
        CategoryManager $categoryManager,
        FileUploader $fileUploader,
        FormFactoryInterface $formFactory
    )
    {
        $this->bookManager=$bookManager;
        $this->categoryManager=$categoryManager;
        $this->fileUploader=$fileUploader;
        $this->formFactory=$formFactory;
    }


    public function __invoke(Book $book, Request $request): array
    {

        $bookDto = BookDto::createFromBook($book);

        $originalCategories = new ArrayCollection();
        foreach ($book->getCategories() as $category){
            $categoyDto = CategoryDto::createFromCategory($category);
            $bookDto->categories[] = $categoyDto;
            $originalCategories->add($categoyDto);
        }
        $form = $this->formFactory->create(BookFormType::class, $bookDto);
        $form->handleRequest($request);

        if(!$form->isSubmitted()){
            return [null, 'Form is not submitted'];
            //return new Response('', Response::HTTP_BAD_REQUEST);
        }
        if($form->isValid()){

            //remove categories decided by user.
            foreach($originalCategories as $originalCategoryDto){
                if(!in_array($originalCategories, $bookDto->categories)){
                    $category = $this->categoryManager->find($originalCategoryDto->id);
                    $book->removeCategory($category);
                }
            }

            //add categories

            foreach($bookDto->categories as $newCategoryDto){
                if(!$originalCategories->contains($newCategoryDto)){
                    $category = $this->categoryManager->find($newCategoryDto->id ?? 0);
                    if(!$category){
                        $category = $this->categoryManager->create();
                        $category->setName($newCategoryDto->name);
                        $this->categoryManager->persist($category);
                    }
                    $book->addCategory($category);
                }
            }
            $book->setTitle($bookDto->title);
            if($bookDto->base64Image){
                $filename = $this->fileUploader->uploadBase64File($bookDto->base64Image);
                $book->setImage($filename);
            }
            $this->bookManager->save($book);
            $this->bookManager->reload($book);
            //return $book;
            return [$book, null];
        }
        //return $form;
        return [null, $form];
    }
}