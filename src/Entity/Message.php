<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name = "messages")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name = "id")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=11, name = "chatId")
     */
    private $chatId;

    /**
     * @ORM\Column(type="integer", length=11, name = "sender")
     */
    private $sender;

    /**
     * @ORM\Column(type="integer", length=11, name = "receiver")
     */
    private $receiver;

    /**
     * @ORM\Column(type="string", length=256, name = "message")
     */
    private $message;

    /**
     * @var \DateTime Data utworzenia produktu
     *
     * @ORM\Column(type = "datetime", name = "lastCheck")
     */
    private $lastCheck;

    public function __construct(int $chatId, int $sender, int $receiver, string $message)
    {
        $this->chatId = $chatId;
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->message = $message;
        $this->lastCheck = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
    }

    public function getId()
    {
        return $this->id;
    }

        public function getMessage()
    {
        return $this->message;
    }
}
