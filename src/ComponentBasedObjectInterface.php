<?php

namespace Dakwamine\Component;

/**
 * Interface for component based objects.
 */
interface ComponentBasedObjectInterface
{
    /**
     * Adds an already created component.
     *
     * @param object $component
     *   The component to add.
     */
    public function addComponent(object $component): void;

    /**
     * Creates a component instance by class name.
     *
     * @param string $className
     *   Fully-qualified class name.
     *
     * @return object|null
     *   The instantiated component.
     */
    public function addComponentByClassName(string $className): ?object;

    /**
     * Adds an already created component to shared components.
     *
     * @param object $component
     *   The component to add.
     */
    public static function addSharedComponent(object $component): void;

    /**
     * Creates a shared component instance by class name.
     *
     * @param string $className
     *   Fully-qualified class name.
     *
     * @return object|null
     *   The instantiated component.
     */
    public static function addSharedComponentByClassName(string $className): ?object;

    /**
     * Gets a single component by class name.
     *
     * @param string $className
     *   Class name.
     * @param bool $addIfNotFound
     *   Set to true to attempt instantiation if not found.
     *
     * @return object|null
     *   The component if found.
     */
    public function getComponentByClassName(string $className, bool $addIfNotFound = false): ?object;

    /**
     * Gets the current components.
     *
     * @return object[]
     *   Array of components.
     */
    public function getComponents(): array;

    /**
     * Gets components by class name.
     *
     * @param string $className
     *   Class name.
     *
     * @return object[]
     *   Array of components. May be empty.
     */
    public function getComponentsByClassName(string $className): array;

    /**
     * Gets the shared component by class name.
     *
     * @param string $className
     *   Class name.
     * @param bool $addIfNotFound
     *   Set to true to attempt instantiation if not found.
     *
     * @return object|null
     *   The object. Null value if not found.
     */
    public static function getSharedComponentByClassName(string $className, bool $addIfNotFound = false): ?object;

    /**
     * Gets the current shared components.
     *
     * @return object[]
     *   Array of components.
     */
    public static function getSharedComponents(): array;

    /**
     * Gets components by class name.
     *
     * @param string $className
     *   Class name.
     *
     * @return object[]
     *   Array of components. May be empty.
     */
    public static function getSharedComponentsByClassName(string $className): array;

    /**
     * Tells if there is a component by class name.
     *
     * @param string $className
     *   The class name.
     *
     * @return bool
     *   True if there is at least one component instance, false otherwise.
     */
    public function hasComponentByClassName(string $className): bool;

    /**
     * Tells if there is a shared component by class name.
     *
     * @param string $className
     *   The class name.
     *
     * @return bool
     *   True if there is at least one component instance, false otherwise.
     */
    public static function hasSharedComponentByClassName(string $className): bool;

    /**
     * Removes the specified component instance.
     *
     * @param object $component
     *   The component instance to remove.
     */
    public function removeComponent(object $component): void;

    /**
     * Removes the components by class name.
     *
     * @param string $className
     *   Class name.
     */
    public function removeComponentsByClassName(string $className): void;

    /**
     * Removes the specified shared component instance.
     *
     * @param object $component
     *   The component instance to remove.
     */
    public static function removeSharedComponent(object $component): void;

    /**
     * Removes the shared components by class name.
     *
     * @param string $className
     *   Class name.
     */
    public static function removeSharedComponentsByClassName(string $className): void;
}
