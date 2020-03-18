<?php

declare(strict_types=1);

namespace App\Controller;

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
        var_dump($parameters);

        if (!isset($parameters['chatId']) && !isset($parameters['sender']) && !isset($parameters['receiver']) && !isset($parameters['message'])) {
            throw new BadRequestHttpException('Błędne dane');
        }

        $entityManager = $this->getDoctrine()->getManager();

        //ToDo: możnaby dodać sprawdzanie czy user o danych id istnieją i są razem w chacie
        $message = new Message($parameters['chatId'], $parameters['sender'], $parameters['receiver'], $parameters['message']);
        $entityManager->persist($message);
        $entityManager->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
