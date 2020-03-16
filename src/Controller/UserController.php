<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Chat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

        // Stworzenie chatu 
        $chatUsers=serialize([$user->getId(),$enableUser->getId()]);
        $chat = new Chat($chatUsers);
        $entityManager->persist($chat);

        // aktualizacja dodawania użytkownika
        $enableUserEntity = $entityManager->getRepository(User::class)->find($enableUser->getId());
        $entityManager->flush();

        // zwrócenie danych o użytkownikach i chacie
        return $this->json([
            'id' => $user->getId(),
            'interlocutor' => $enableUserEntity->getId(),
            'chatId' => $chat->getId()
        ]);
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
}
