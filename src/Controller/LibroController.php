<?php

namespace App\Controller;

use App\Service\LibroService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/libro', name: 'libro')]
class LibroController extends AbstractController
{
    private $libroService;

    public function __construct(LibroService $libroService)
    {
        $this->libroService = $libroService;
    }

    #[Route('/create', name: 'libro_create', methods: ['POST'])]
    public function libroCreate(Request $request): Response
    {        
        $body = $request->getContent();
        $data = json_decode($body, true);

        if ($data === null) {
            return $this->json("El formato de la solicitud no es válido", Response::HTTP_BAD_REQUEST);
        }

        $result = $this->libroService->createLibro($data);

        if(!$result["success"]) {
            return $this->json($result['message'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result['message'], Response::HTTP_CREATED);
    }

    #[Route('/get', name: 'libro_get', methods: ['GET'])]
    public function getLibros(): Response
    {
        
        $result = $this->libroService->getAllLibros();
        if(!$result['success']){
            return $this->json($result['message'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($result['message'], Response::HTTP_OK);
    }

    #[Route('/update/{id}', name: 'libro_update', methods: ['PUT'])]
    public function editLibro(int $id, Request $request): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $result = $this->libroService->updateLibro($id, $data);

        if(!$result["success"]){
            return $this->json($result["message"], Response::HTTP_NOT_FOUND);
        }

        return $this->json($result["message"], Response::HTTP_OK);
    }

    #[Route('/delete', name: 'libro_delete', methods: ['DELETE'])]
    public function deleteLibro(Request $request): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (empty($data["id"])) {
            return $this->json("El campo id no puede estar vacío", Response::HTTP_BAD_REQUEST);
        }


        $result = $this->libroService->deleteLibro($data["id"]);

        if(!$result["success"]){
            return $this->json($result["message"], Response::HTTP_NOT_FOUND);
        }

        return $this->json($result["message"], Response::HTTP_OK);
    
    }
}
