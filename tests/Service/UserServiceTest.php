<?php
namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    private $userRepository;
    private $passwordHasher;
    private $entityManager;
    private $userService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userService = new UserService(
            $this->passwordHasher,
            $this->userRepository,
            $this->entityManager
        );
    }

    public function testRegisterUserSuccess(): void
    {
        $data = [
            'email' => 'test@ejemplo.com',
            'password' => '12345',
            'nombre' => 'Test',
            'edad' => 25,
        ];

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $data['email']])
            ->willReturn(null);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashedPassword123');

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->userService->registerUser($data);

        $this->assertTrue($result['success']);
        $this->assertEquals('Usuario creado correctamente', $result['message']);
    }

    public function testRegisterUserMissingFields(): void
    {
        $data = [
            'email' => 'test@ejemplo.com',
            'password' => '',
            'nombre' => 'Test',
            'edad' => 25,
        ];

        $result = $this->userService->registerUser($data);

        $this->assertFalse($result['success']);
        $this->assertEquals("El campo 'password' no puede estar vacío", $result['message']);
    }

    public function testRegisterUserInvalidEmail(): void
    {
        $data = [
            'email' => 'test.es',
            'password' => '12345',
            'nombre' => 'Test',
            'edad' => 25,
        ];

        $result = $this->userService->registerUser($data);

        $this->assertFalse($result['success']);
        $this->assertEquals("El email introducido no es valido", $result['message']);
    }

    public function testRegisterUserEmailAlreadyExists(): void
    {
        $data = [
            'email' => 'test@ejemplo.com',
            'password' => '12345',
            'nombre' => 'Test',
            'edad' => 25,
        ];

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $data['email']])
            ->willReturn(new User());

        $result = $this->userService->registerUser($data);

        $this->assertFalse($result['success']);
        $this->assertEquals("El email introducido ya esta registrado", $result['message']);
    }

    public function testGetAllUsersSuccess(): void
    {
        $user = new User();
        $user->setEmail('test1@ejemplo.com');
        $user->setNombre('Test');
        $user->setEdad(25);

        $this->userRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$user]);

        $result = $this->userService->getAllUsers();

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['message']);
        $this->assertEquals('test1@ejemplo.com', $result['message'][0]['email']);
    }

    public function testGetAllUsersEmpty(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->userService->getAllUsers();

        $this->assertFalse($result['success']);
        $this->assertEquals('No hay ningun usaurio', $result['message']);
    }

    public function testDeleteUserSuccess(): void
    {
        $user = new User();

        $this->userRepository->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->willReturn($user);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($user));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->userService->deleteUser(1);

        $this->assertTrue($result['success']);
        $this->assertEquals('Usuario eliminado correctamente', $result['message']);
    }

    public function testDeleteUserNotFound(): void
    {
        $this->userRepository->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->willReturn(null);

        $result = $this->userService->deleteUser(1);

        $this->assertFalse($result['success']);
        $this->assertEquals("No se ha podio encontrar el usuario indicado", $result['message']);
    }
}
