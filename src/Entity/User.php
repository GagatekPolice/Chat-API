<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name = "users")
 * @ORM\MappedSuperclass
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name = "id")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, name = "nickname")
     */
    private $nickname;

    /**
     * @ORM\Column(type="integer", name = "enable")
     */
    private $enable;

    /**
     * @var \DateTime Data utworzenia produktu
     *
     * @ORM\Column(type = "datetime", name = "lastCheck")
     */
    private $lastCheck;

    public function __construct(string $nickname)
    {
        $this->nickname = $nickname;
        $this->enable = 1;
        $this->lastCheck = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
    }

    public function getId()
    {
        return $this->id;
    }

    public function isEnable()
    {
        return (bool) $this->enable;
    }

    public function setEnable(bool $status)
    {
        $this->enable = $status;

        return $this;
    }
}
