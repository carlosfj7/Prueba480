<?php

namespace App\Tests\Service;

use App\Entity\Libro;
use App\Repository\LibroRepository;
use App\Service\LibroService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class LibroServiceTest extends TestCase
{
    private $em;
    private $libroRepository;
    private $libroService;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->libroRepository = $this->createMock(LibroRepository::class);
        $this->libroService = new LibroService($this->em, $this->libroRepository);
    }

    public function testCreateLibroSuccess(): void
    {
        $data = [
            'titulo' => 'Titulo del libro',
            'autor' => 'Autor del libro',
            'genero' => 'genero',
            'año_publicacion' => 2023
        ];

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->libroService->createLibro($data);

        $this->assertTrue($result['success']);
        $this->assertEquals('El libro ha sido creado correctamente', $result['message']);
    }

    public function testCreateLibroMissingField(): void
    {
        $data = [
            'titulo' => 'Titulo del libro',
            'autor' => 'Autor del libro',
            'genero' => 'genero',
            
        ];

        $result = $this->libroService->createLibro($data);

        $this->assertFalse($result['success']);
        $this->assertEquals("El campo 'año_publicacion' no puede estar vacío", $result['message']);
    }

    public function testGetAllLibrosSuccess(): void
    {
        $libro1 = (new Libro())
            ->setTitulo('Titulo 1')
            ->setAutor('Autor 1')
            ->setGenero('Genero 1')
            ->setAñoPublicacion(2020);

        $libro2 = (new Libro())
            ->setTitulo('Titulo 2')
            ->setAutor('Autor 2')
            ->setGenero('Genero 2')
            ->setAñoPublicacion(2021);

        $this->libroRepository
            ->method('findAll')
            ->willReturn([$libro1, $libro2]);

        $result = $this->libroService->getAllLibros();

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['message']);
    }

    public function testUpdateLibroNotFound(): void
    {
        $this->libroRepository->method('find')->willReturn(null);

        $result = $this->libroService->updateLibro(1, ['titulo' => 'Updated Title']);

        $this->assertFalse($result['success']);
        $this->assertEquals('No se ha encontrado el libro que se queria editar', $result['message']);
    }

    public function testDeleteLibroSuccess(): void
    {
        $libro = $this->createMock(Libro::class);

        $this->libroRepository->method('find')->willReturn($libro);
        $this->em->expects($this->once())->method('remove');
        $this->em->expects($this->once())->method('flush');

        $result = $this->libroService->deleteLibro(1);

        $this->assertTrue($result['success']);
        $this->assertEquals('El libro se ha eliminado correctamente', $result['message']);
    }

    public function testDeleteLibroNotFound(): void
    {
        $this->libroRepository->method('find')->willReturn(null);

        $result = $this->libroService->deleteLibro(1);

        $this->assertFalse($result['success']);
        $this->assertEquals('No se ha encontrado el libro que se queria borrar', $result['message']);
    }
}
