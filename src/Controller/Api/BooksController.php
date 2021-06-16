<?php

namespace App\Controller\Api;


use App\Entity\Book;
use App\Form\Type\BookFormType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;


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
        Request $request
    )
    {
        $book = new Book();
        $form = $this->createForm(BookFormType::class, $book);
        $form->handleRequest($request);

        //Con isSubmitted, la función getName del BookFormType ya nos indica si pasa o no pasa el submit.
        //Con isValid, si cumple la validación de config/validator/Book.yaml nos devuelve un $book, sino un $form que fos rest bundle es capaz de serializar con el contenido del error.
        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($book);
            $em->flush();
            return $book;
        }
        return $form;
    }
}