<?php

namespace App\Service;

use App\Entity\Libro;
use App\Repository\LibroRepository;
use Doctrine\ORM\EntityManagerInterface;

class LibroService
{
    private $em;
    private $libroRepository;

    public function __construct(EntityManagerInterface $em, LibroRepository $libroRepository)
    {
        $this->em = $em;
        $this->libroRepository = $libroRepository;
    }

    public function createLibro(array $data): array
    {
        $requiredFields = ["titulo", "autor", "genero", "año_publicacion"];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return ["success" => false,"message"=> "El campo '$field' no puede estar vacío"];
            }
        }

        $libro = new Libro();
        $libro->setTitulo($data["titulo"]);
        $libro->setAutor($data["autor"]);
        $libro->setAñoPublicacion($data["año_publicacion"]);
        $libro->setGenero($data["genero"]);

        $this->em->persist($libro);
        $this->em->flush();

        return ["success"=> true,"message"=> "El libro ha sido creado correctamente"];
    }

    public function getAllLibros(): array
    {
        $libros = $this->libroRepository->findAll();

        if (count($libros) === 0) {
            return ["success"=> false,"message"=> "No hay ningun libro"];
        }

        $librosJson = [];
        foreach ($libros as $libro) {
            $librosJson[] = [
                'titulo' => $libro->getTitulo(),
                'autor' => $libro->getAutor(),
                'año_publicacion' => $libro->getAñoPublicacion(),
                'genero' => $libro->getGenero()
            ];
        }

        return ['success'=> true,'message'=> $librosJson];
    }

    public function updateLibro(int $id, array $data): array
    {
        $libro = $this->libroRepository->find($id);
        if (!$libro) {
            return ['success'=> false,'message'=> 'No se ha encontrado el libro que se queria editar'];
        }
        if (isset($data['titulo'])) {
            $libro->setTitulo($data['titulo']);
        }
        if (isset($data['autor'])) {
            $libro->setAutor($data['autor']);
        }
        if (isset($data['año_publicacion'])) {
            $libro->setAñoPublicacion($data['año_publicacion']);
        }
        if (isset($data['genero'])) {
            $libro->setGenero($data['genero']);
        }
        $this->em->persist($libro);
        $this->em->flush();

        return ['success'=> true,'message'=> 'El libro se ha editado correctamente'];
    }

    public function deleteLibro(int $id): array
    {
        $libro = $this->libroRepository->find($id);
        if (!$libro) {
            return ['success'=> false,'message'=> 'No se ha encontrado el libro que se queria borrar'];
        }

        $this->em->remove($libro);
        $this->em->flush();
        return ['success'=> true,'message'=> 'El libro se ha eliminado correctamente'];
    }
}
