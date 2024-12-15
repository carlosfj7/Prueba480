<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;

#[Route('/user', name: 'user')]
class UserController extends AbstractController
{
    #[Route('/register', name: 'user_register')]
    public function userRegister(Request $request, UserPasswordHasherInterface $passwordHasher,UserRepository $userRepository, EntityManagerInterface $em): Response
    {        
        //llamada
        $body = $request->getContent();
        $data = json_decode($body, true);

        if ($data !== null){
            
            $user = new User();
            $requiredFields = ["email","password", "nombre","edad"];
            foreach ($requiredFields as $field) {
                if(empty($data[$field])){
                    return $this->json("El campo '$field' no puede estar vacio", Response::HTTP_BAD_REQUEST);
                }
            }
            if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                $user->setEmail($data["email"]);
            }else{
                return $this->json("Email no valido", Response::HTTP_BAD_REQUEST);
            }
            
            
            $user->setEdad($data["edad"]);
            $user->setNombre($data["nombre"]);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $data["password"]
            );
            $user->setPassword($hashedPassword);
            $em->persist($user);
            $em->flush();
            return $this->json("Usuario creado correctamente", Response::HTTP_CREATED);
        }
            
        return $this->json("No se ha podido crear el usuario ", Response::HTTP_BAD_REQUEST);

    }

    #[Route('/get', name: 'user_get')]
    public function getUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        if(count($users) === 0){
            return $this->json('No hay ningun usuario registrado', Response::HTTP_BAD_REQUEST);
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
        return $this->json($userJson);
    }

    #[Route('/delete', name: 'user_delete')]
    public function deleteUsers(EntityManagerInterface $em,Request $request,UserRepository $userRepository): Response
    {
        $body = $request->getContent();
        $data = json_decode($request->getContent(), true);
        if(empty($data["id"])){
            return $this->json("El campo id no puede estar vacío", Response::HTTP_BAD_REQUEST);
        }
        $user = $userRepository->find($data['id']);
        if (!$user) {
            return $this->json('Usuario no encontrado', Response::HTTP_NOT_FOUND);
        }
        $em->remove($user);
        $em->flush();

        return $this->json('Usuario borrado correctamente', Response::HTTP_OK);
    }

}
