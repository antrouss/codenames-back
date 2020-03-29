<?php

/**
 * Service with generic methods
 *
 * PHP version 7.4
 *
 * @category   Service
 * @author     Antony Roussos <antrouss4@gmail.com>
 * @version    0.0.1
 */

namespace App\Service;

/**
 * Class Utilities is responsible to provide some useful processing pattern
 * to other classes.
 */
class Utilities extends BaseService
{
    /**
     * Returns the class name of the object, or array of objects.
     * 
     * @param object|array $object object or array of objects
     *
     * @return string the name of the object class
     */
    public function getClassName($object)
    {
        if (is_object($object)) {
            $class = get_class($object);
            $class_split = explode('\\', $class);
            $class_name = end($class_split);

            return $class_name;
        } elseif ($object === []) {
            return "empty";
        }
        if (isset($object[0]) && is_array($object)) {
            return $this->getClassName($object[0]);
        }
        return "custom";
    }
}
