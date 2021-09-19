<?php

namespace Dakwamine\Component;

/**
 * Defines a dependency of a ComponentBasedObject.
 */
abstract class DependencyDefinition {

    /**
     * A DependencyDefinition to be used in case of instantiation failure.
     *
     * @var DependencyDefinition
     */
    protected $backupDependencyDefinition;

    /**
     * The class name of the dependency.
     *
     * @var string
     */
    protected $className;

    /**
     * Tells where the dependency is expected to be placed.
     *
     * @var string
     */
    protected $componentBucketType;

    /**
     * Dependency Definition constructor.
     *
     * @param string $className
     *   The class name of the dependency.
     * @param string $componentBucketType
     *   Tells where the dependency is expected to be placed.
     * @param DependencyDefinition $backupDependencyDefinition
     *   A DependencyDefinition to be used in case of instantiation failure.
     */
    public function __construct($className, DependencyDefinition $backupDependencyDefinition = null) {
        $this->className = $className;
        $this->backupDependencyDefinition = $backupDependencyDefinition;
    }

    /**
     * The optional backup dependency definition.
     *
     * @return DependencyDefinition|null
     *   The definition or null if not set.
     */
    public function getBackupDefinition(): ?DependencyDefinition
    {
        return $this->backupDependencyDefinition;
    }

    /**
     * Gets the dependency class name.
     *
     * @return string
     *   Class name.
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Gets the container type for the dependency.
     *
     * @return string
     *   Dependency type. Usually one of ComponentBucketType::* consts.
     */
    public function getComponentBucketType(): string
    {
        return $this->componentBucketType;
    }

    /**
     * Sets the backup dependency if this one fails to instantiate.
     *
     * @param DependencyDefinition $backup
     *   The backup definition.
     *
     * @return DependencyDefinition
     *   The backup definition which has been set. Useful for chaining backup
     *   definitions.
     */
    public function setBackupDependency(DependencyDefinition $backup): DependencyDefinition
    {
        $this->backupDependencyDefinition = $backup;
        return $backup;
    }
}
