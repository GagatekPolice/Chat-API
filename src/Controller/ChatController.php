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
 * @Route("/chat")
 */
class ChatController extends AbstractController
{
    /**
     *  Usunięcie chatu .
     *
     * @Route("/{chatId}", name="deleteChat")
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
            return new Response('id : userIdCreated '.$userIdCreated, Response::HTTP_NOT_FOUND);
        }
        $userCreated->setEnable(true);
        $entityManager->flush();

        $userMember = $entityManager->getRepository(User::class)->find($userIdMember);
        if (!$userMember) {
            return new Response('id : userIdMember '.$userIdMember, Response::HTTP_NOT_FOUND);
        }
        $userMember->setEnable(true);
        $entityManager->flush();
        // ToDo: Dodać by próbiwało im znaleźć czaz kimś innym, jeżli nie znajdzie to łączy ich jeszcze raz ze sobą
        return new Response('', Response::HTTP_NO_CONTENT);
    }

}