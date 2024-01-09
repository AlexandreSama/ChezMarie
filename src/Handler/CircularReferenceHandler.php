<?php

namespace App\Handler;

class CircularReferenceHandler
{
    /**
     * The function returns the ID property of an object.
     * 
     * @param object The parameter "object" is a variable that represents an object of any class.
     * 
     * @return the ID property of the object.
     */
    public function __invoke($object)
    {
        return $object->getId();
    }
}