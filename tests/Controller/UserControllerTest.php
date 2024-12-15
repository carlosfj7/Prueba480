<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


class UserControllerTest extends WebTestCase
{
    public function testRegisterValidUser(): void
    {
        $client = self::createClient();

        $userData = [
            'email' => 'testuser@ejemplo.com',
            'password' => '12345',
            'nombre' => 'Test',
            'edad' => 25
        ];

        $client->request('POST', '/user/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($userData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($client->getResponse()->getContent());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Usuario creado correctamente', $response);
    }

    public function testRegisterUserInvalidEmail(): void
    {
        $client = self::createClient();

        $userData = [
            'email' => 'test.es',
            'password' => '12345',
            'nombre' => 'Test',
            'edad' => 25
        ];

        $client->request('POST', '/user/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($userData));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals("El email introducido no es valido", $response);
    }

    public function testRegisterUserWithEmptyField(): void
    {
        $client = self::createClient();

        $userData = [
            'email' => 'testuser1@ejemplo.com',
            'password' => '12345',
            'nombre' => '', 
            'edad' => 25
        ];

        $client->request('POST', '/user/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($userData));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals("El campo 'nombre' no puede estar vacío", $response);
    }

    public function testGetUsers(): void
    {
        $client = self::createClient();

        $client->request('GET', '/user/get');


        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);
        

        $this->assertIsArray($data); 
        $this->assertContains([
            'email' => 'test@test.es',
            'nombre' => 'test',
            'edad' => 21
        ], $data);
    }

    public function testDeleteUserValid(): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);


        $user = new User();
        $user->setEmail('userToDelete@example.com');
        $user->setPassword('password123');
        $user->setNombre('User To Delete');
        $user->setEdad(25);
        $em->persist($user);
        $em->flush();
        $token = self::getAuthToken($client);
        
        $userId = $user->getId();
        
        $client->request('DELETE', '/user/delete', [], [], ['CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => "Bearer {$token}",], json_encode(['id' => $userId]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
    public function testDeleteUserInvalidId(): void
    {
        $client = self::createClient();
        $token = self::getAuthToken($client);

        $client->request('DELETE', '/user/delete', [], [], ['CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => "Bearer {$token}"], json_encode(['id' => 9999]));

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
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
