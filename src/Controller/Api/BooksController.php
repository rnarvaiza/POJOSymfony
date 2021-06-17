<?php

namespace App\Controller\Api;


use App\Entity\Book;
use App\Form\Model\BookDto;
use App\Form\Type\BookFormType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use League\Flysystem\FilesystemOperator;


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
        FilesystemOperator $defaultStorage
    )
    {
        $bookDto = new BookDto();
        $form = $this->createForm(BookFormType::class, $bookDto);
        $form->handleRequest($request);

        //Con isSubmitted, la función getName del BookFormType ya nos indica si pasa o no pasa el submit.
        //Con isValid, si cumple la validación de config/validator/BookDto.yaml nos devuelve un $bookDto, sino un $form que fos rest bundle es capaz de serializar con el contenido del error.
        if($form->isSubmitted() && $form->isValid()) {
            $extension = explode('/', mime_content_type($bookDto->base64Image))[1];
            $data = explode(',', $bookDto->base64Image);
            $filename = sprintf('%s.%s', uniqid('book_', true), $extension);
            $defaultStorage->write($filename, base64_decode($data[1]));
            $book = new Book();
            $book->setTitle($bookDto->title);
            $book->setImage($filename);
            $em->persist($book);
            $em->flush();
            return $book;
        }
        return $form;
    }
}