<?php

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name'=> $user->getName(),
            'surname' => $user->getSurname(),
            'personalCode' => $user->getPersonalCode(),
            'phoneNumber' => $user->getPhoneNumber(),
            'dateOfBirth' => $user->getDateOfBirth()->format('Y-m-d'),
        ];
    }

    #[Route('/users', name: 'user_list', methods:['get'] )]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $users = $doctrine->getRepository(User::class)->findAll();
        $data = array_map([$this, 'formatUser'], $users);

        return $this->json(['status' => 'success', 'data' => $data]);
    }

    #[Route('/users', name: 'user_create', methods:['post'] )]
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $validator = Validation::createValidator();
        $input = $request->request->all();

        $constraints = new Assert\Collection([
            'name' => new Assert\Length(['min' => 1]),
            'surname' => new Assert\Length(['min' => 1]),
            'personalCode' => new Assert\Length(['min' => 1]),
            'phoneNumber' => new Assert\Length(['min' => 1]),
            'dateOfBirth' => new Assert\Date()
        ]);

        $violations = $validator->validate($input, $constraints);

        if(count($violations) > 0) {
            return $this->json(['status' => 'error', 'message' => (string) $violations], 400);
        }

        $entityManager = $doctrine->getManager();
        $user = new User();
        $user->setName($input['name']);
        $user->setSurname($input['surname']);
        $user->setPersonalCode($input['personalCode']);
        $user->setPhoneNumber($input['phoneNumber']);
        $dateOfBirthObject = DateTime::createFromFormat('Y-m-d', $input['dateOfBirth']);
        $user->setDateOfBirth($dateOfBirthObject);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['status' => 'success', 'data' => $this->formatUser($user)]);
    }

    #[Route('/users/{id}', name: 'user_show', methods:['get'] )]
    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $user = $doctrine->getRepository(User::class)->find($id);

        if(!$user) {
            return $this->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        return $this->json(['status' => 'success', 'data' => $this->formatUser($user)]);
    }

    #[Route('/users/{id}', name: 'user_update', methods:['put', 'patch'] )]
    public function update(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user){
            return $this->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $input = $request->request->all();

        $user->setName($input['name']);
        $user->setSurname($input['surname']);
        $user->setPersonalCode($input['personalCode']);
        $user->setPhoneNumber($input['phoneNumber']);
        $dateOfBirthObject = DateTime::createFromFormat('Y-m-d', $input['dateOfBirth']);
        $user->setDateOfBirth($dateOfBirthObject);
        $entityManager->flush();

        return $this->json(['status' => 'success', 'data' => $this->formatUser($user)]);
    }

    #[Route('/users/{id}', name: 'user_delete', methods:['delete'] )]
    public function delete(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user){
            return $this->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        // Mark the user as deleted, but don't remove them from the database
        $user->setActive(false);
        $entityManager->flush();

        return $this->json(['status' => 'success', 'message' => 'User marked as deleted with id ' . $id]);
    }

    #[Route('/api/login_check', name: 'app_login', methods:['POST'] )]
    public function login(): JsonResponse
    {
        // The client is automatically authenticated at this point
        $user = $this->getUser();

        return $this->json([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }


    #[Route('/users/transfer', name: 'user_transfer', methods:['post'] )]
    public function transfer(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $input = json_decode($request->getContent(), true);

        $fromUser = $doctrine->getRepository(User::class)->find($input['fromUserId']);
        $toUser = $doctrine->getRepository(User::class)->find($input['toUserId']);
        $amount = $input['amount'];

        if(!$fromUser || !$toUser) {
            return $this->json(['status' => 'error', 'message' => 'User not found'], 404);
        }
        if($fromUser->getBalance() < $amount) {
            return $this->json(['status' => 'error', 'message' => 'Not enough money'], 400);
        }

        $fromUser->setBalance($fromUser->getBalance() - $amount);
        $toUser->setBalance($toUser->getBalance() + $amount);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($fromUser);
        $entityManager->persist($toUser);
        $entityManager->flush();

        return $this->json(['status' => 'success', 'message' => 'Transfer successful']);
    }

}