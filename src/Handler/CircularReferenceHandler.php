<?php

namespace App\Handler;

class CircularReferenceHandler
{
    public function __invoke($object)
    {
        return $object->getId(); // ou une autre propriété unique
    }
}