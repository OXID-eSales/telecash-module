<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Traits;

use OxidSolutionCatalysts\TeleCash\IPG\API\Exception\PropertyNotExistsException;

/**
 * Trait PropertyTrait
 *
 * can be used to add dynamic getter/setter for class properties
 */
trait PropertyTrait
{
    /** @var array<string, mixed> $properties */
    private array $properties = [];

    /**
     * Dynamic getter/setter for class properties
     * */
    public function __call(string $name, mixed $arguments): mixed
    {
        if (str_starts_with($name, 'get')) {
            $property = lcfirst(substr($name, 3));
            if (array_key_exists($property, $this->properties)) {
                return $this->properties[$property];
            }
        }
        if (str_starts_with($name, 'set')) {
            $property = lcfirst(substr($name, 3));
            if (array_key_exists($property, $this->properties)) {
                $this->properties[$property] = $arguments[0];
                return $this;
            }
        }
        throw new PropertyNotExistsException('Property "' . $name . '" does not exist in class ' . get_class($this));
    }

    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }
        return null;
    }

    public function __set(string $name, mixed $value): void
    {
        if (array_key_exists($name, $this->properties)) {
            $this->properties[$name] = $value;
        }
    }

    public function __isset(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function initProperties(array $properties): void
    {
        $this->properties = $properties;
    }
}
