<?php
declare(strict_types=1);

namespace adityasetiono\DoctrineBase\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class BaseEntity
{
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

    public function setDeleted(int $deleted)
    {
        $this->deleted = $deleted;
    }

    public function deserialize(?array $options = null, ?int $depth = 0): array
    {
        $fields = $options ?? array_keys(get_class_vars(get_called_class()));
        if (!(array_keys($fields) !== range(0, count($fields) - 1))) {
            $fields = array_combine($fields, $fields);
        }
        $arr = [];
        foreach ($fields as $label => $field) {
            $nestedOptions = null;
            if (!is_string($field)) {
                $nestedOptions = $field["options"];
                $field = $field['field'];
            }
            $ucField = ucwords($field);
            $getter = "get" . $ucField;
            if (method_exists($this, $getter)) {
                $attr = $this->{$getter}();
                if (is_object($attr) && ($depth < 3 || $nestedOptions)) {
                    $temp = [];
                    if ($attr instanceof \Doctrine\ORM\PersistentCollection) {
                        /** @var BaseEntity $entity */
                        foreach ($attr as $entity) {
                            $temp[] = $entity->deserialize($nestedOptions, $depth+1);
                        }
                    } else {
                        /** @var BaseEntity $attr */
                        $temp = $attr->deserialize($nestedOptions, $depth+1);
                    }
                    $attr = $temp;
                }
                $arr[$label] = $attr;
            }
        }
        return $arr;
    }

    public function serialize(array $params)
    {
        foreach ($params as $field => $value) {
            $setter = "set" . ucwords($field);
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            }
        }
    }
}
