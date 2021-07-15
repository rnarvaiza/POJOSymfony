<?php

namespace App\Controller\Api;

use App\Service\BookManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use App\Service\BookFormProcessor;


class BooksController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(path="/books")
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */

    public function getAction(
        BookManager $bookManager
    )
    {
        return $bookManager->getRepository()->findAll();
    }

    /**
     * @Rest\Post(path="/books")
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */

    public function postAction(
        BookManager $bookManager,
        BookFormProcessor $bookFormProcessor,
        Request $request
    )
    {
        $book = $bookManager->create();
        [$book, $error] = ($bookFormProcessor)($book, $request);
        $statusCode = $book ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
        $data = $book ?? $error;
        return View::create($data, $statusCode);
       // return $book ?? $error;

//        $bookDto = new BookDto();
//        $form = $this->createForm(BookFormType::class, $bookDto);
//        $form->handleRequest($request);
//
//        //Con isSubmitted, la función getName del BookFormType ya nos indica si pasa o no pasa el submit.
//        //Con isValid, si cumple la validación de config/validator/BookDto.yaml nos devuelve un $bookDto, sino un $form que fos rest bundle es capaz de serializar con el contenido del error.
//        if(!$form->isSubmitted()) {
//            return new Response('', Response::HTTP_BAD_REQUEST);
//        }
//        if($form->isValid()){
//            $book = new Book();
//            $book->setTitle($bookDto->title);
//
//            if($bookDto->base64Image){
//                $filename = $fileUploader->uploadBase64File($bookDto->base64Image);
//                $book->setImage($filename);
//            }
//            $em->persist($book);
//            $em->flush();
//            return $book;
//        }
//        return $form;

    }

    /**
     * @Rest\Post(path="/books/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */

    public function editAction(
        int $id,
        BookFormProcessor $bookFormProcessor,
        BookManager $bookManager,
        Request $request
    ){
        $book = $bookManager->find($id);
        if(!$book){
            //throw $this->createNotFoundException('Book not found');
            return View::create('Book not found', Response::HTTP_BAD_REQUEST);
        }
        [$book, $error] = ($bookFormProcessor)($book, $request);
        $statusCode = $book ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
        $data = $book ?? $error;
        return View::create($data, $statusCode);
    }


    /**
     * @Rest\Delete(path="/books/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */

    public function deleteAction(
        int $id,
        BookManager $bookManager
    ){
        $book = $bookManager->find($id);
        if(!$book){
            //throw $this->createNotFoundException('Book not found');
            return View::create('Book not found', Response::HTTP_BAD_REQUEST);
        }
        $bookManager->delete($book);

        return View::create('Book deleted', Response::HTTP_NO_CONTENT);
    }
}