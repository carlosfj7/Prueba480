<?php

namespace App\Controller;

use App\Entity\Libro;
use App\Repository\LibroRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/libro', name: 'libro')]
class LibroController extends AbstractController
{
    #[Route('/create', name: 'libro_create', methods: ['POST'])]
    public function libroCreate(Request $request, EntityManagerInterface $em): Response
    {        
        
        $body = $request->getContent();
        $data = json_decode($body, true);

        if ($data !== null){

            $requiredFields = ["titulo","autor", "genero","año_publicacion"];
            foreach ($requiredFields as $field) {
                if(empty($data[$field])){
                    return $this->json("El campo '$field' no puede estar vacio", Response::HTTP_BAD_REQUEST);
                }
            }
            $libro = new Libro();
            $libro->setTitulo($data["titulo"]);
            $libro->setAutor($data["autor"]);
            $libro->setAñoPublicacion($data["año_publicacion"]);
            $libro->setGenero($data["genero"]);
        

            $em->persist($libro);
            $em->flush();
            return $this->json("Libro creado correctamente", Response::HTTP_CREATED);
        }
            
        return $this->json("No se ha podido crear el libro indicado", Response::HTTP_BAD_REQUEST);

    }

    #[Route('/get', name: 'libro_get', methods: ['GET'])]
    public function getLibros(LibroRepository $libroRepository): Response
    {
        $libros = $libroRepository->findAll();
        if(count($libros) === 0){
            return $this->json('No hay ningun libro guardado', Response::HTTP_BAD_REQUEST);
        }
        $libroJson = array();
        foreach ($libros as $libro) {
            $libroJson[] = 
            [
                'titulo'=> $libro->getTitulo(),
                'autor'=> $libro->getAutor(),
                'año_publicacion'=> $libro->getAñoPublicacion(),
                'genero'=> $libro->getGenero()
            ];
        }
        return $this->json($libroJson);
    }
    
    
    #[Route('/update/{id}', name: 'libro_Update', methods:'PUT')]
    public function editLibro($id, Request $request,LibroRepository $libroRepository, EntityManagerInterface $em): Response
    {

        $body = $request->getContent();
        $data = json_decode($body, true);
        $libro = $libroRepository->find($id);
        if (!$libro) {
            return $this->json('No se ha encontrado el libro que se quiere editar', Response::HTTP_BAD_REQUEST); 
        }

        if(isset($data['titulo'])) 
        {
            $libro->setTitulo($data['titulo']);
        }
        if(isset($data['autor'])) 
        {
            $libro->setAutor($data['autor']);
        }
        if(isset($data['año_publicacion'])) 
        {
            $libro->setAñoPublicacion($data['año_publicacion']);
        }
        if(isset($data['genero'])) 
        {
            $libro->setGenero($data['genero']);
        }
        
        $em->persist($libro);
        $em->flush();
        return $this->json('Los datos del libro se han actualizado correctamente', Response::HTTP_OK);
    }


    #[Route('/delete', name: 'libro_delete', methods:'DELETE')]
    public function deleteUsers(EntityManagerInterface $em,Request $request,LibroRepository $libroRepository): Response
    {
        $body = $request->getContent();
        $data = json_decode($request->getContent(), true);
        if(empty($data["id"])){
            return $this->json("El campo id no puede estar vacío", Response::HTTP_BAD_REQUEST);
        }
        $libro = $libroRepository->find($data['id']);
        if (!$libro) {
            return $this->json('Libro no encontrado', Response::HTTP_NOT_FOUND);
        }
        $em->remove($libro);
        $em->flush();

        return $this->json('Libro borrado correctamente', Response::HTTP_OK);
    }

}
