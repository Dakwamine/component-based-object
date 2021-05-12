<?php

namespace Dakwamine\Component;

/**
 * Base class for component based objects.
 */
abstract class ComponentBasedObject
{
    /**
     * Current components.
     *
     * @var ComponentBasedObject[]
     */
    protected $components = [];

    /**
     * Shared components.
     *
     * @var ComponentBasedObject[]
     */
    private static $sharedComponents = [];

    /**
     * Adds an already created component.
     *
     * @param ComponentBasedObject $component
     *   The component to add.
     */
    public function addComponent(ComponentBasedObject $component)
    {
        $this->components[] = $component;
    }

    /**
     * Creates a component instance by class name.
     *
     * @param string $className
     *   Fully-qualified class name.
     *
     * @return ComponentBasedObject|null
     *   The instantiated component.
     */
    public function addComponentByClassName(string $className): ?ComponentBasedObject
    {
        if (!class_exists($className)) {
            // Not an existing class.
            return null;
        }

        if (!is_a($className, ComponentBasedObject::class, true)) {
            // Not a component based object.
            return null;
        }

        $instance = new $className;
        $this->components[] = $instance;
        return $instance;
    }

    /**
     * Adds an already created component to shared components.
     *
     * @param ComponentBasedObject $component
     *   The component to add.
     */
    public static function addSharedComponent(ComponentBasedObject $component)
    {
        self::$sharedComponents[] = $component;
    }

    /**
     * Creates a shared component instance by class name.
     *
     * @param string $className
     *   Fully-qualified class name.
     *
     * @return ComponentBasedObject|null
     *   The instantiated component.
     */
    public static function addSharedComponentByClassName(string $className): ?ComponentBasedObject
    {
        if (!class_exists($className)) {
            // Not an existing class.
            return null;
        }

        if (!is_a($className, ComponentBasedObject::class, true)) {
            // Not a component based object.
            return null;
        }

        $instance = new $className;
        self::$sharedComponents[] = $instance;
        return $instance;
    }

    /**
     * Gets a single component by class name.
     *
     * @param string $className
     *   Class name.
     * @param bool $addIfNotFound
     *   Set to true to attempt instantiation if not found.
     *
     * @return ComponentBasedObject|null
     *   The component if found.
     */
    public function getComponentByClassName(string $className, bool $addIfNotFound = false): ?ComponentBasedObject
    {
        foreach ($this->components as $component) {
            if ($component instanceof $className) {
                return $component;
            }
        }

        if ($addIfNotFound === true) {
            $component = $this->addComponentByClassName($className);

            // May still be null.
            return $component;
        }

        // Not found.
        return null;
    }

    /**
     * Gets the current components.
     *
     * @return ComponentBasedObject[]
     *   Array of components.
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Gets components by class name.
     *
     * @param string $className
     *   Class name.
     *
     * @return ComponentBasedObject[]
     *   Array of components. May be empty.
     */
    public function getComponentsByClassName(string $className): array
    {
        $components = [];

        foreach ($this->components as $component) {
            if ($component instanceof $className) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * Gets the shared component by class name.
     *
     * @param string $className
     *   Class name.
     * @param bool $addIfNotFound
     *   Set to true to attempt instantiation if not found.
     *
     * @return ComponentBasedObject|null
     *   The object. Null value if not found.
     */
    public static function getSharedComponentByClassName(
        string $className,
        bool $addIfNotFound = false
    ): ?ComponentBasedObject {
        foreach (self::$sharedComponents as $component) {
            if ($component instanceof $className) {
                return $component;
            }
        }

        if ($addIfNotFound === true) {
            $component = self::addSharedComponentByClassName($className);

            // May still be null.
            return $component;
        }

        // Not found.
        return null;
    }

    /**
     * Gets the current shared components.
     *
     * @return ComponentBasedObject[]
     *   Array of components.
     */
    public static function getSharedComponents(): array
    {
        return self::$sharedComponents;
    }

    /**
     * Gets components by class name.
     *
     * @param string $className
     *   Class name.
     *
     * @return ComponentBasedObject[]
     *   Array of components. May be empty.
     */
    public static function getSharedComponentsByClassName(string $className): array
    {
        $components = [];

        foreach (self::$sharedComponents as $component) {
            if ($component instanceof $className) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * Tells if there is a component by class name.
     *
     * @param string $className
     *   The class name.
     *
     * @return bool
     *   True if there is at least one component instance, false otherwise.
     */
    public function hasComponentByClassName(string $className): bool
    {
        foreach ($this->components as $component) {
            if ($component instanceof $className) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tells if there is a shared component by class name.
     *
     * @param string $className
     *   The class name.
     *
     * @return bool
     *   True if there is at least one component instance, false otherwise.
     */
    public static function hasSharedComponentByClassName(string $className): bool
    {
        foreach (self::$sharedComponents as $component) {
            if ($component instanceof $className) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes the specified component instance.
     *
     * @param ComponentBasedObject $component
     *   The component instance to remove.
     */
    public function removeComponent(ComponentBasedObject $component)
    {
        foreach ($this->components as $key => $c) {
            // This will compare by reference.
            if ($c === $component) {
                unset($this->components[$key]);
                return;
            }
        }
    }

    /**
     * Removes the components by class name.
     *
     * @param string $className
     *   Class name.
     */
    public function removeComponentsByClassName(string $className)
    {
        foreach ($this->components as $key => $c) {
            if ($c instanceof $className) {
                unset($this->components[$key]);
            }
        }
    }

    /**
     * Removes the specified shared component instance.
     *
     * @param ComponentBasedObject $component
     *   The component instance to remove.
     */
    public static function removeSharedComponent(ComponentBasedObject $component)
    {
        foreach (self::$sharedComponents as $key => $c) {
            // This will compare by reference.
            if ($c === $component) {
                unset(self::$sharedComponents[$key]);
                return;
            }
        }
    }

    /**
     * Removes the shared components by class name.
     *
     * @param string $className
     *   Class name.
     */
    public static function removeSharedComponentsByClassName(string $className)
    {
        foreach (self::$sharedComponents as $key => $c) {
            if ($c instanceof $className) {
                unset(self::$sharedComponents[$key]);
            }
        }
    }
}
