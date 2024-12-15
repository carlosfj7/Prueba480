<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user')]
class UserController extends AbstractController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $result = $this->userService->registerUser(data: $data);

        if (!$result['success']) {
            return $this->json($result['message'], Response::HTTP_BAD_REQUEST);
        } 
        return $this->json($result['message'], Response::HTTP_CREATED);
    }

    #[Route('/get', name: 'user_get', methods: ['GET'])]
    public function getUsers(): Response
    {                
        $result = $this->userService->getAllUsers();
        if (!$result['success']) {
            return $this->json($result['message'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($result['message'], Response::HTTP_OK);
    }

    #[Route('/delete', name: 'user_delete', methods: ['DELETE'])]
    public function delete(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);        
        $result = $this->userService->deleteUser($data['id']);
        if (!$result['success']) {
            return $this->json($result['message'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($result['message'], Response::HTTP_OK);
    }
}