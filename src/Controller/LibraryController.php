<?php

namespace App\Controller;
use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LibraryController extends AbstractController
{

    //Si estás fuera de controllers, tienes que llamar al servicio desde el constructor, ejemplo aquí.
/*
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

*/
    /**
     * @Route ("/library/list", name="library_list")
     */

    //Si estás dentro de controllers, puedes llamar al servicio a traves de los argumentos de la función.
    /*
    public function list(Request $request, LoggerInterface $logger){

        $title = $request->get('title', 'ValorPorDefectoDeTítulo');
        $logger->info('List action called3');
        $response = new JsonResponse();
        $response->setData([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'nombre' => 'Crimenes',
                    'autor' => 'Ferdinand Von Schirach'
                ],
                [
                    'id' => 2,
                    'nombre' => 'Culpa',
                    'autor' => 'Ferdinand Von Schirach'
                ],
                [
                    'id' => 3,
                    'nombre' => $title,
                    'autor' => 'Ferdinand Von Schirach'
                ]

            ]
        ]);
        return $response;
    }
    */

    /**
     * @Route("/books", name="books_get")
     */
    public function list(Request $request, BookRepository $bookRepository){
        $books = $bookRepository->findAll();
        $booksAsArray = [];
        foreach ($books as $book) {
            $booksAsArray[] = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'image' => $book->getImage()
            ];
        };
        $response = new JsonResponse();
        $response->setData([
            'success' => true,
            'data' => $booksAsArray
        ]);
        return $response;
    }


    /**
     * @Route ("/book/create", name="create_book")
     */
    public function createBook(Request $request, EntityManagerInterface $em){
        $book = new Book();
        $response = new JsonResponse();
        $title = $request->get('title', null);
        if(empty($title)){
            $response->setData([
                'success' => false,
                'error' => 'Tittle cannot be empty',
                'data' => null

            ]);
            return $response;
        }
        $book-> setTitle($title);
        $em->persist($book);
        $em->flush();
        $response->setData([
            'success' => true,
            'data' => [
                [
                    'id' => $book->getId(),
                    'nombre' => $book->getTitle()
                ]
            ]
        ]);
        return $response;
    }
}