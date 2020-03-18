<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     *  Dodanie użytkownika czatu.
     *
     * @Route("", name="postUser")
     *
     * @param Request $request Obiekt reprezentujący żądanie HTTP
     *
     * @return json zwraca id dodanego użytkownika, jeżeli znalazło chat to id drugiego użytkownika i id chatu
     */
    public function postUserAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $parameters = [];
        $content = $request->getContent();

        //TODO: w dalekiej przyszłości zmienić weryfikację reqestów, na weryfikację przy pomocy formów
        if (!empty($content)) {
            $parameters = json_decode($content, true);
        }

        if (!isset($parameters['nickname'])) {
            throw new BadRequestHttpException('Błędne dane');
        }

        $user = new User($parameters['nickname']);
        $entityManager->persist($user);

        $enableUser = $this->findChatUser($entityManager);

        // jeżeli nie znaleziono drugiego użytkownika,lub znaleziony użytkownik to my
        if (!$enableUser || $enableUser->getId() === $user->getId()) {
            $entityManager->flush();

            return $this->json([
                'id' => $user->getId(),
            ]);
        }

        $user->setEnable(false);
        $enableUser->setEnable(false);

        // aktualizacja dodawania użytkownika
        $enableUserEntity = $entityManager->getRepository(User::class)->find($enableUser->getId());
        $entityManager->flush();

        // Stworzenie chatu
        $chat = new Chat($user->getId(), $enableUser->getId());
        $entityManager->persist($chat);

        // dodanie chatu
        $entityManager->flush();

        // zwrócenie danych o użytkownikach i chacie
        return $this->json([
            'id' => $user->getId(),
            'interlocutor' => $enableUserEntity->getId(),
            'chatId' => $chat->getId(),
        ]);
    }

    /**
     *  Usunięcie użytkownika .
     *
     * @Route("/{userId}", name="deleteUser")
     *
     * @param int $userId id usuwanego użytkownika
     */
    public function deleteUserAction(int $userId)
    {
        // usunięcie user-a
        $entityManager = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        // jeżeli nie znajdzie usera zwraca 404 not found
        if (!$user) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($user);

        // usunięcie chatu
        $chat = $this->findUserChat($entityManager, $userId);
        // jeżeli nie znajdzie chatu to aktualizuje baze i zwraca ok
        if (!$chat) {
            $entityManager->flush();

            return new Response('', Response::HTTP_NO_CONTENT);
        }
        $entityManager->remove($chat);

        // ustawienie użytkownika chatu na dostępnego
        if ($userId === $chat->getUserCreatedId()) {
            $userMemberId = $chat->getUserMemberId();
        } else {
            $userMemberId = $userId;
        }
        // aktualizacja usera
        $entityManager->flush();

        $userMember = $entityManager->getRepository(User::class)->find($userMemberId);
        $userMember->setEnable(true);

        // akutalizacja  drugiego użytkownika
        $entityManager->flush();

        return new Response((string) $userMember->getId(), Response::HTTP_NO_CONTENT);
    }

    /**
     *  Metoda wyszukuje dostępnego użytkownika.
     *
     * @param EntityManager $entityManager Obiekt entityManagera
     *
     * @return User|null
     */
    private function findChatUser($entityManager)
    {
        $dql = 'SELECT user FROM App\Entity\User user WHERE user.enable>0';

        $queryUser = $entityManager->createQuery($dql)
            ->setMaxResults(1)
            ->getResult();

        return array_pop($queryUser) ?? null;
    }

    /**
     *  Metoda wyszukuje chat użytkownika.
     *
     * @param EntityManager $entityManager Obiekt entityManagera
     *
     * @return int|null
     */
    private function findUserChat($entityManager, $userId)
    {
        $dql = 'SELECT chat FROM App\Entity\Chat chat WHERE chat.userIdCreated=' . $userId . ' OR chat.userIdMember=' . $userId;

        $queryChat = $entityManager->createQuery($dql)
            ->setMaxResults(1)
            ->getResult();

        return array_pop($queryChat) ?? null;
    }
}
