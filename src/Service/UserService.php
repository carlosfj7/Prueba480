<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $passwordHasher;
    private $userRepository;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function registerUser(array $data): array
    {
    

        $requiredFields = $requiredFields = ['email', 'password', 'nombre', 'edad'];
        foreach ($requiredFields as $field) {
            if(empty($data[$field])){
                return ['success' => false,'message'=>"El campo '$field' no puede estar vacío"];

            }
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false,'message'=>"El email introducido no es valido"];
        }
        $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return ['success' => false,'message'=> "El email introducido ya esta registrado"];
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setNombre($data['nombre']);
        $user->setEdad($data['edad']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return ['success'=> true, 'message' => 'Usuario creado correctamente'];
    }

    public function getAllUsers(): array
    {
        $users = $this->userRepository->findAll();
        if (count($users) === 0) {
            return ['success'=> false,'message'=> 'No hay ningun usaurio'];
        }
        $userJson = array();
        foreach ($users as $user) {
            $userJson[] = 
            [
                'email'=> $user->getEmail(),
                'nombre'=> $user->getNombre(),
                'edad'=> $user->getEdad()
            ];
        }
        return ['success'=> true,'message'=> $userJson];
    }

    public function deleteUser(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return['success'=> false, 'message'=> "No se ha podio encontrar el usuario indicado"];
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return['success'=> true, 'message'=> "Usuario eliminado correctamente"];
    }

    
}