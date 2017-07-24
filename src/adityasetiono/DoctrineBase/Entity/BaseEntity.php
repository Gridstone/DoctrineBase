<?php
declare(strict_types=1);

namespace adityasetiono\DoctrineBase\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class BaseEntity
{
    use Serializable;

    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $deleted;

    /**
     * @ORM\PrePersist
     */
    public function prePersistBase()
    {
        $this->setCreatedAt(time() * 1000);
        $this->setUpdatedAt(time() * 1000);
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdateBase()
    {
        $this->setUpdatedAt(time() * 1000);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getCreatedAt(): int
    {
        return intval($this->createdAt);
    }

    public function setCreatedAt(int $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): int
    {
        return intval($this->updatedAt);
    }

    public function setUpdatedAt(int $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(?int $deleted)
    {
        $this->deleted = $deleted;
    }
}
