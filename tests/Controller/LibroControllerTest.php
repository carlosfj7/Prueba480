<?php

namespace App\Tests\Controller;

use App\Entity\Libro;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;




class LibroControllerTest extends WebTestCase
{
    
    public function testCreateLibro(): void
    {
        $client = static::createClient();
        $client->request('POST', '/libro/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'titulo' => '1984',
            'autor' => 'George Orwell',
            'genero' => 'Ficción',
            'año_publicacion' => 1949
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Libro creado correctamente', $response);

    }

    public function testGetLibros(): void
    {
        $client = static::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $numLibros = count($em->getRepository(Libro::class)->findAll());
        $libro = new Libro();
        $libro->setTitulo('El Quijote');
        $libro->setAutor('Miguel de Cervantes');
        $libro->setGenero('Novela');
        $libro->setAñoPublicacion(1605);

        $em->persist( $libro );
        $em->flush();

        $client->request('GET', '/libro/get');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);
        
        
        $this->assertIsArray($data); 
        $this->assertCount($numLibros + 1, $data);

        $this->assertContains([
            'titulo' => 'El Quijote',
            'autor' => 'Miguel de Cervantes',
            'año_publicacion' => 1605,
            'genero' => 'Novela',
        
        ], $data);

    }

    public function testUpdateLibro(): void
    {

        $client = static::createClient();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
    
        $token = $this->getAuthToken($client);

        $libro = new Libro();
        $libro->setTitulo('El Quijote');
        $libro->setAutor('Miguel de Cervantes');
        $libro->setGenero('Novela');
        $libro->setAñoPublicacion(1605);

        $em->persist($libro);
        $em->flush();
    
        $libroId = $libro->getId();
    
        $updateData = [
            'titulo' => 'Don Quijote de la mancha',
            'autor' => 'Cervantes',
            'genero' => 'Novela antigua',
            'año_publicacion' => 2023,
        ];
    
        $client->request(
            'PUT', 
            "/libro/update/{$libroId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
                    ],
            json_encode($updateData)
        );
    
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);
  
        $em->clear(); 
        $updatedLibro = $em->getRepository(Libro::class)->find($libroId);
    
        $this->assertEquals('Don Quijote de la mancha', $updatedLibro->getTitulo());
        $this->assertEquals('Cervantes', $updatedLibro->getAutor());
        $this->assertEquals('Novela antigua', $updatedLibro->getGenero());
        $this->assertEquals(2023, $updatedLibro->getAñoPublicacion());
    }
    public function testDeleteLibro(): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $libroId = 66;
    
        $libro = $em->getRepository(Libro::class)->find($libroId);
        $this->assertNotNull($libro); 
        $token = $this->getAuthToken($client);
        $client->request(
            'DELETE', 
            "/libro/delete",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            
            ],json_encode(["id"=>$libroId])
           
        );
    

        $this->assertResponseIsSuccessful(); 
        $deletedLibro = $em->getRepository(Libro::class)->find($libroId);
        $this->assertNull($deletedLibro); 
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
