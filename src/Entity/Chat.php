<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name = "chats")
 */
class Chat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name = "id")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, name = "chatUsers")
     */
    private $chatusers;



    /**
     * @var \DateTime Data utworzenia produktu
     *
     * @ORM\Column(type = "datetime", name = "lastCheck")
     */
    private $lastCheck;

    public function __construct(string $chatusers)
    {
        $this->chatusers = $chatusers;
        $this->lastCheck = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
    }

    public function getId()
    {
        return $this->id;
    }
}
