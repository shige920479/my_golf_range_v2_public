<?php

trait ReflectionHelper
{
  protected function getProperty(object $object, string $property): mixed
  {
    $ref = new \ReflectionClass($object);
    $prop = $ref->getProperty($property);
    $prop->setAccessible(true);

    return $prop->getValue($object);
  }

  protected function setProperty(object $object, string $property, mixed $value): void
  {
    $ref = new \ReflectionClass($object);
    $prop = $ref->getProperty($property);
    $prop->setAccessible(true);
    $prop->setValue($object, $value);
  }

  protected function callMethod(object $object, string $method, array $args = []): mixed
  {
    $ref = new \ReflectionClass($object);
    $method = $ref->getMethod($method);
    $method->setAccessible(true);
    return $method->invokeArgs($object, $args);
  }
}