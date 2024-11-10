<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->security = $security;
    }

    #[Route('/v1/api/users', name: 'api_users_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!empty($data['id'])) {
            $user = $this->entityManager->getRepository(User::class)->find($data['id']);
            
            return new JsonResponse([
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'phone' => $user->getPhone(),
                'roles' => $user->getRoles()
            ], 200);
        }

        $users = $this->entityManager->getRepository(User::class)->findAll();
        return $this->json($users);
    }

    #[Route('/v1/api/users', name: 'api_users_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['login']) || empty($data['phone']) || empty($data['pass'])) {
            return new JsonResponse(['error' => 'Login, phone, and pass are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $data['login']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Login already exists'], JsonResponse::HTTP_CONFLICT);
        }

        $user = new User();

        $user->setLogin($data['login']);
        $user->setPass($data['pass']); // skipped hashing of pass before saving
        $user->setPhone($data['phone']);
        $user->setRoles($data['roles'] ? $data['roles'] : ['ROLE_USER']);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorsArray], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone(),
            'roles' => $user->getRoles()
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/api/users', name: 'api_users_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (empty($data['login']) || empty($data['phone']) || empty($data['pass']) || empty($data['id'])) {
            return new JsonResponse(['error' => 'Login, phone, id and pass are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->security->getUser();

        return $this->json(['error' => $user], Response::HTTP_FORBIDDEN);

        if ($user->getId() !== $data['id']) {
            return $this->json(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        $user = $userRepository->find($data['id']);
        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $data['login']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Login already exists'], JsonResponse::HTTP_CONFLICT);
        }
        
        $user->setLogin($data['login']);
        $user->setPass($data['pass']); // skipped hashing of pass before saving
        $user->setPhone($data['phone']);

        $hasAccessToRoles = $this->isGranted('ROLE_ADMIN');

        if ($hasAccessToRoles && !empty($data['roles']))
            $user->setRoles($data['roles']);
    
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone(),
            'roles' => $user->getRoles()
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/api/users', name: 'api_users_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'You are not allowed to access the admin method.')]
    public function delete(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['id'])) {
            return new JsonResponse(['message' => 'Id is required'], 404);
        }

        $user = $userRepository->find($data['id']);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    
        return new JsonResponse(['message' => 'User ' . $data['id'] . ' deleted'], 200);
    }    
}
