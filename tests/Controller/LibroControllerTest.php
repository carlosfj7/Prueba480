<?php

namespace App\Tests\Controller;

use LDAP\Result;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Libro;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LibroRepository;



class LibroControllerTest extends WebTestCase
{
   
    public function testCreateLibroSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', '/libro/create', [], [], [], json_encode([
            'titulo' => 'Titulo',
            'autor' => 'Autor',
            'genero' => 'Genero',
            'año_publicacion' => 2023
        ]));

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testCreateLibroInvalidRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', '/libro/create', [], [], [], json_encode([]));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetLibrosSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/libro/get');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());
    }



    public function testUpdateLibroSuccess(): void
    {
        $client = static::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $libro = (new Libro())
            ->setTitulo('Titulo ')
            ->setAutor('Autor ')
            ->setGenero('Genero ')
            ->setAñoPublicacion(2020);
        $em->persist($libro);
        $em->flush();
        $token = $this->getAuthToken($client);
        $libroId = $libro->getId();

        $client->request('put', "/libro/update/{$libroId}", [], [], ['CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => "Bearer {$token}"], json_encode(['titulo' => 'nuevo titulo', 'año_publicacion' => 2023]));
    
    

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testDeleteLibroSuccess(): void
    {
        $client = static::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);


        $libro = (new Libro())
            ->setTitulo('Titulo ')
            ->setAutor('Autor ')
            ->setGenero('Genero ')
            ->setAñoPublicacion(2020);
           
        $em->persist($libro);
        $em->flush();
        $token = $this->getAuthToken($client);
        $libroId = $libro->getId();

        $client->request('DELETE', '/libro/delete', [], [], ['CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => "Bearer {$token}"], json_encode(['id' => $libroId]));
    

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testDeleteLibroNotFound(): void
    {
        $client = static::createClient();
        $token = $this->getAuthToken($client);
        $client->request('DELETE', '/libro/delete', [], [], ['CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => "Bearer {$token}"], json_encode(['id' => 999]));

        $this->assertResponseStatusCodeSame(404);
        $this->assertJson($client->getResponse()->getContent());
    }


    private function getAuthToken($client): string
    {
        $credentials = [
            'username' => 'test@test.es', 
            'password' => '12345', 
        ];


        $client->request(
            'POST',
            '/api/login_check', 
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($credentials)
        );


        $this->assertResponseStatusCodeSame(Response::HTTP_OK);


        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        $this->assertArrayHasKey('token', $data);

        return $data['token'];
    }
}
