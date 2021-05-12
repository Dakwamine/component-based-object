# Component based object

A simple starter class for component based objects.

This is used when flexibility is needed, for highly interactive components.

The components can be added to any other components, and fetched by any object having the reference to it.
There are no visibility checks.

## Basic usage

```php
<?php

use Dakwamine\Component\ComponentBasedObject;

class SomeObject extends ComponentBasedObject
{
  public function doSomething($someValue)
  {
    // This will instantiate a component on this SomeObject instance.
    /** @var AnotherComponentBasedObject $newComponent */
    $newComponent = $this->addComponentByClassName(AnotherComponentBasedObject::class);

    // Edit the component as needed.
    $newComponent->computeSomething($someValue);

    // Returns an array containing all components attached to this instance
    // which extend or implement the given class name.
    $this->getComponentsByClassName(AnotherComponentBasedObjectInterface::class);

    // Tells if component exists.
    $this->hasComponentByClassName(AnotherComponentBasedObject::class);

    // Gets the first component of the given class, and adds it if not found.
    $this->getComponentByClassName(AnotherComponentBasedObject::class, true);

    // Remove a specific component.
    $this->removeComponent($newComponent);

    // Adds a shared component. Useful for reusable objects such as tools or services.
    // Shared with all ComponentBasedObject children.
    static::addSharedComponentByClassName(AnotherComponentBasedObject::class);
  }
}

class AnotherComponentBasedObject extends ComponentBasedObject implements AnotherComponentBasedObjectInterface
{
  private $value;

  public function computeSomething($value)
  {
    $this->value = $value;
  }
}

interface AnotherComponentBasedObjectInterface
{
  public function computeSomething($value);
}
```