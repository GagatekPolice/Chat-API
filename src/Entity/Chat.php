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
     * @ORM\Column(type="integer", length=32, name = "userIdCreated")
     */
    private $userIdCreated;

    /**
     * @ORM\Column(type="integer", length=32, name = "userIdMember")
     */
    private $userIdMember;

    /**
     * @var \DateTime Data utworzenia produktu
     *
     * @ORM\Column(type = "datetime", name = "lastCheck")
     */
    private $lastCheck;

    public function __construct(int $userIdCreated, int $userIdMember)
    {
        $this->userIdCreated = $userIdCreated;
        $this->userIdMember = $userIdMember;
        $this->lastCheck = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserCreatedId()
    {
        return $this->userIdCreated;
    }

    public function getUserMemberId()
    {
        return $this->userIdMember;
    }

    public function updateDate()
    {
        $this->lastCheck = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
    }
}
