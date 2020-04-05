<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/chat")
 */
class ChatController extends AbstractController
{
    /**
     *  Usunięcie chatu .
     *
     * @Route("/{chatId}", name="deleteChat", methods={"DELETE"})
     *
     * @param int $chatId id usuwanego chatu
     */
    public function deleteChatAction(int $chatId)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // usunięcie chatu
        $chat = $this->getDoctrine()
            ->getRepository(Chat::class)
            ->find($chatId);
        // jeżeli nie znajdzie chatu zwraca 404 not found
        if (!$chat) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
        $userIdCreated = $chat->getUserCreatedId();
        $userIdMember = $chat->getUserMemberId();

        $entityManager->remove($chat);
        $entityManager->flush();

        $userCreated = $entityManager->getRepository(User::class)->find($userIdCreated);
        if (!$userCreated) {
            return new Response('id : userIdCreated ' . $userIdCreated, Response::HTTP_NOT_FOUND);
        }
        $userCreated->setEnable(true);
        $entityManager->flush();

        $userMember = $entityManager->getRepository(User::class)->find($userIdMember);
        if (!$userMember) {
            return new Response('id : userIdMember ' . $userIdMember, Response::HTTP_NOT_FOUND);
        }
        $userMember->setEnable(true);
        $entityManager->flush();

        // ToDo: Dodać by próbiwało im znaleźć czaz kimś innym, jeżli nie znajdzie to łączy ich jeszcze raz ze sobą
        $enableUser1 = $this->findChatUser($entityManager);
        $enableUser2 = $this->findChatUser($entityManager, $enableUser1->getId());

        // Stworzenie chatu
        $chat = new Chat($enableUser1->getId(), $enableUser2->getId());
        $entityManager->persist($chat);

        // dodanie chatu
        $entityManager->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     *  Wyszukanie chatu użytkownika.
     *
     * @Route("/{userId}", name="getChatId", methods={"GET"})
     *
     * @param int $userId id usuwanego chatu
     */
    public function getChatAction(int $userId)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $dql = 'SELECT chat FROM App\Entity\Chat chat WHERE chat.userIdCreated=' . $userId . ' OR chat.userIdMember=' . $userId;

        $queryChat = $entityManager->createQuery($dql)
            ->setMaxResults(1)
            ->getResult();
        $chatId = array_pop($queryChat)->getId();

        // update daty usera
        $user = $entityManager->getRepository(User::class)->find($userId);
        $user->updateDate();
        $entityManager->flush();

        // update daty chatu
        $chat = $entityManager->getRepository(Chat::class)->find($chatId);
        $chat->updateDate();
        $entityManager->flush();
        if (!$chatId) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
        // odnalezenie uzytkownika z którym gadamy
        if ($userId === $chat->getUserCreatedId()) {
            $userMemberId = $chat->getUserMemberId();
        } else {
            $userMemberId = $chat->getUserCreatedId();
        }

        $userMember = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userMemberId);

        return $this->json([
            'chatId' => $chatId,
            'userIdCreated' => $chat->getUserCreatedId(),
            'userIdMember' => $chat->getUserMemberId(),
            'stangerNickname' => $userMember->getNickname(),
        ]);
    }

    /**
     *  Metoda wyszukuje dostępnego użytkownika.
     *
     * @param EntityManager $entityManager Obiekt entityManagera
     *
     * @return User|null
     */
    private function findChatUser($entityManager, $userId = null)
    {
        if (!$userId) {
            $dql = 'SELECT user FROM App\Entity\User user WHERE user.enable>0';
        } else {
            $dql = 'SELECT user FROM App\Entity\User user WHERE user.enable>0 NOT LIKE user.id=' . $userId;
        }

        $queryUser = $entityManager->createQuery($dql)
            ->setMaxResults(1)
            ->getResult();

        return array_pop($queryUser) ?? null;
    }
}
