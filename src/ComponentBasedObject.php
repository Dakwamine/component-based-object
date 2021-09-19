<?php

namespace Dakwamine\Component;

/**
 * Base class for component based objects.
 */
class ComponentBasedObject implements ComponentBasedObjectInterface
{
    /**
     * Current components.
     *
     * @var object[]
     */
    protected $components = [];

    /**
     * Global container for shared components.
     *
     * @var ComponentBasedObjectInterface
     */
    private static $sharedComponentsContainer;

    /**
     * {@inheritdoc}
     */
    public function addComponent(object $component): void
    {
        $this->components[] = $component;
    }

    /**
     * {@inheritdoc}
     */
    public function addComponentByClassName(string $className): ?object
    {
        if (!class_exists($className)) {
            // Not an existing class.
            return null;
        }

        $instance = new $className;
        $this->components[] = $instance;
        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public static function addSharedComponent(object $component): void
    {
        self::getSharedComponentsContainer()->addComponent($component);
    }

    /**
     * {@inheritdoc}
     */
    public static function addSharedComponentByClassName(string $className): ?object
    {
        return self::getSharedComponentsContainer()->addComponentByClassName($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentByClassName(string $className, bool $addIfNotFound = false): ?object
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
     * {@inheritdoc}
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * {@inheritdoc}
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
     * Gets the global container.
     *
     * @return ComponentBasedObjectInterface
     *   The global container.
     */
    private static function getSharedComponentsContainer(): ComponentBasedObjectInterface
    {
        if (empty(self::$sharedComponentsContainer)) {
            self::$sharedComponentsContainer = new self();
        }

        return self::$sharedComponentsContainer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSharedComponentByClassName(
        string $className,
        bool $addIfNotFound = false
    ): ?object {
        return self::getSharedComponentsContainer()->getComponentByClassName($className, $addIfNotFound);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSharedComponents(): array
    {
        return self::getSharedComponentsContainer()->getComponents();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSharedComponentsByClassName(string $className): array
    {
        return self::getSharedComponentsContainer()->getComponentsByClassName($className);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function removeComponent(object $component): void
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
     * {@inheritdoc}
     */
    public function removeComponentsByClassName(string $className): void
    {
        foreach ($this->components as $key => $c) {
            if ($c instanceof $className) {
                unset($this->components[$key]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function removeSharedComponent(object $component): void
    {
        self::getSharedComponentsContainer()->removeComponent($component);
    }

    /**
     * {@inheritdoc}
     */
    public static function removeSharedComponentsByClassName(string $className): void
    {
        self::getSharedComponentsContainer()->removeSharedComponentsByClassName($className);
    }
}
