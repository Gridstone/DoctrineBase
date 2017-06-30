<?php

namespace adityasetiono\DoctrineBase\Entity;

trait Serializable
{
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
                        /** @var Serializable $entity */
                        foreach ($attr as $entity) {
                            $temp[] = $entity->deserialize($nestedOptions, $depth+1);
                        }
                    } else {
                        /** @var Serializable $attr */
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