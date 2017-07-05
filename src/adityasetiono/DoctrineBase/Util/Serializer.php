<?php

namespace adityasetiono\DoctrineBase\Util;

class Serializer
{
    public static function deserialize($object, ?array $options = null): array
    {
        $fields = $options;
        $getters = [];
        $nestedOptions = [];
        if (is_array($fields)) { // if the options are passed in
            foreach ($fields as $label => $field) {
                // initialize the getters from the field name
                if (!is_string($field)) { // if the attribute needs to be deserialized again
                    if (isset($field['__field'])) {
                        $ucField = ucwords($field['__field']);
                        $getters[] = "get" . $ucField;
                        $temp = $field;
                        unset($temp['__field']);
                        $nestedOptions[$label] = $temp;
                    } else { // else the object is a custom object which values derived from this parent object
                        $nestedOptions[$label] = $field;
                        $getters[] = false;
                    }
                } else { // else the attribute is just a primitive value from the getter method
                    $ucField = ucwords($field);
                    $getters[] = "get" . $ucField;
                }
            }
            $fields = array_combine(array_keys($fields), $getters); // creating 'field': 'getter' key value pair
        } else { // else the options are not passed in
            $methods = get_class_methods(get_class($object));
            $getters = array_filter($methods, function ($method) {
                return preg_match('/^get.*/', $method);
            });
            foreach ($getters as $getter) {
                $lowercaseGetter = strtolower($getter);
                $field = preg_replace('/^get/', '', $lowercaseGetter);
                $fields[$field] = $getter; // creating 'field': 'getter' key value pair
            }
        }
        $arr = [];
        foreach ($fields as $field => $getter) { // loop through the field-getter key-value pair
            $attr = null;
            $n = empty($nestedOptions[$field]) ? null : $nestedOptions[$field]; // reset nestedOptions to null if empty
            if (method_exists($object, $getter)) {
                $attr = $object->{$getter}(); // call the getter method
                $temp = [];
                if (is_object($attr)) { // if the result is an object, value needs to be deserialized again
                    if ($attr instanceof \Doctrine\ORM\PersistentCollection) {
                        foreach ($attr as $entity) {
                            $temp[] = self::deserialize($entity, $n);
                        }
                    } else {
                        $temp = self::deserialize($attr, $n);
                    }
                    $attr = $temp;
                } elseif (is_array($attr) && isset($attr[0]) && is_object($attr[0])) {
                    // if the result is an array of object, loop through and deserialize
                    foreach ($attr as $entity) {
                        $temp[] = self::deserialize($entity, $n);
                    }
                    $attr = $temp;
                }
            } else { // creating a custom object from the parents attributes
                $attr = self::deserialize($object, $n);
            }
            $arr[$field] = $attr;
        }
        return $arr;
    }

    public static function serialize($params, $className)
    {
        $object = new $className;
        foreach ($params as $field => $value) {
            $setter = "set" . ucwords($field);
            if (method_exists($object, $setter)) {
                $object->{$setter}($value);
            }
        }
        return $object;
    }
}