<?php

namespace App\Tests\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;


class UserControllerTest extends WebTestCase
{
    public function testRegisterValidUser(): void
    {
        $client = self::createClient();

        $userData = [
            'email' => 'testuser@example.com',
            'password' => '12345',
            'nombre' => 'Test User',
            'edad' => 25
        ];

        $client->request('POST',
         '/user/register', 
         [], [],
          ['CONTENT_TYPE' => 'application/json'],
           json_encode($userData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testRegisterUserInvalidFields(): void
    {
        $client = self::createClient();

        $userData = [
            'email' => 'testuser1@example.com',
            'password' => '12345',
            'nombre' => '',
            'edad' => 25
        ];

        $client->request('POST', '/user/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($userData));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals("El campo 'nombre' no puede estar vacio", $response);

    }

    public function testRegisterUserInvalidEmail(): void
    {
        $client = self::createClient();

        $userData = [
            'email' => 'invalid-email',
            'password' => 'password123',
            'nombre' => 'Test User',
            'edad' => 25
        ];

        $client->request('POST', '/user/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($userData));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals("Email no valido", $response);
    }

    public function testRegisterUserWithMissingField(): void
    {
        $client = self::createClient();

        $userData = [
            'password' => 'password123',
            'nombre' => 'Test User',
            'edad' => 25
        ];

        $client->request('POST', '/user/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($userData));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

    }
    public function testGetUsers(): void
    {
    
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $numUsers = count($em->getRepository(User::class)->findAll());
        
        $client->request('GET', '/user/get');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        $this->assertIsArray($data); 
        $this->assertCount($numUsers, $data);

        $this->assertContains([
            'email' => 'test@test.es',
            'nombre' => 'test',
            'edad' => 21
        
        ], $data);
    }

    public function testDeleteUser(): void
    {
        $client = self::createClient();

        $user = new User();
        $user->setEmail('userToDelete@example.com');
        $user->setNombre('User To Delete');
        $user->setEdad(25);
        $user->setPassword('12345');
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $token = $this->getAuthToken($client);

        $client->request(
            'DELETE', 
            "/user/delete",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            
            ],json_encode(["id"=>$user->getId()])
           
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $deletedUser = $em->getRepository(User::class)->find($user->getId());
        $this->assertNull($deletedUser);
    }

    public function testDeleteUserInvalidId(): void
    {
        
        $client = self::createClient();

        $token = $this->getAuthToken($client);
        $client->request(
            'DELETE', 
            "/user/delete",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            
            ],json_encode(["id"=>999999])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($client->getResponse()->getContent());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals("Usuario no encontrado", $response);

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
