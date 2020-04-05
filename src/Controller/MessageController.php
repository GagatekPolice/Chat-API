<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Message;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/message")
 */
class MessageController extends AbstractController
{
    /**
     *  Dodanie wiadomości.
     *
     * @Route("", name="postMessage", methods={"POST"})
     *
     * @param Request $request Obiekt reprezentujący żądanie HTTP
     */
    public function postMessageAction(Request $request)
    {
        $parameters = [];
        $content = $request->getContent();

        //TODO: w dalekiej przyszłości zmienić weryfikację reqestów, na weryfikację przy pomocy formów
        if (!empty($content)) {
            $parameters = json_decode($content, true);
        }

        if (!isset($parameters['chatId']) && !isset($parameters['sender']) && !isset($parameters['receiver']) && !isset($parameters['message'])) {
            throw new BadRequestHttpException('Błędne dane');
        }

        $entityManager = $this->getDoctrine()->getManager();

        //ToDo: możnaby dodać sprawdzanie czy user o danych id istnieją i są razem w chacie
        $message = new Message($parameters['chatId'], $parameters['sender'], $parameters['receiver'], $parameters['message']);
        $entityManager->persist($message);
        $entityManager->flush();

        $user = $entityManager->getRepository(User::class)->find($parameters['sender']);
        $user->updateDate();
        $entityManager->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     *  Pobranie wiadomości.
     *
     * @Route("/{userId}", name="getMessage", methods={"GET"})
     *
     * @param int $userId id usuwanego użytkownika
     *
     * @return Message zwraca chat z jego wszystkimi informacjami
     */
    public function getMessageAction(int $userId)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $dql = 'SELECT message FROM App\Entity\Message message WHERE message.receiver=' . $userId;

        $queryUser = $entityManager->createQuery($dql)
            ->setMaxResults(1)
            ->getResult();

        $message = array_pop($queryUser) ?? null;

        // update daty usera
        $user = $entityManager->getRepository(User::class)->find($userId);
        $user->updateDate();
        $entityManager->flush();

        if (!$message) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        // update daty chatu pobranego z numeru wiadomości
        $chat = $entityManager->getRepository(Chat::class)->find($message->getChatId());
        $chat->updateDate();
        $entityManager->flush();

        $entityManager->remove($message);
        $entityManager->flush();

        return $this->json([
            'message' => $message->getMessage(),
        ]);
    }
}
