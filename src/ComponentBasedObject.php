<?php

namespace Dakwamine\Component;

use Dakwamine\Component\Exception\UnmetDependencyException;

/**
 * Base class for component based objects.
 */
class ComponentBasedObject
{
    /**
     * A reference to the fellow components container.
     *
     * This is a very simple object which lists all fellow/sibling components.
     *
     * @var ComponentContainer
     */
    protected $fellowComponentsContainer;

    /**
     * Root component.
     *
     * Automatically set on self::getRootContainer().
     *
     * @var ComponentBasedObject
     */
    private static $rootComponent;

    /**
     * Sub components.
     *
     * Sub components are components which are taken care by this component.
     * For example, a Team component can have People subcomponents.
     *
     * @var object[]
     */
    protected $subComponents = [];

    /**
     * Adds a component to a bucket.
     *
     * @param object $component
     *   The component to add.
     * @param string $componentBucketType
     *   The bucket where to put to component.
     *   One of ComponentBucketType::* consts.
     *
     * @return bool
     *   False if the component could not be added (unknown bucket).
     */
    private function addComponent(object $component, string $componentBucketType = ComponentBucketType::SUB): bool
    {
        switch ($componentBucketType) {
            case ComponentBucketType::FELLOW:
                // Fellow components do not reference each other directly.
                $this->getFellowComponentsContainer()->components[] = $component;

                if ($component instanceof ComponentBasedObject) {
                    // They share the same fellow container.
                    $component->setFellowComponentsContainer($this->getFellowComponentsContainer());
                }

                return true;

            case ComponentBucketType::ROOT:
                // Recursive call to reuse the FELLOW code.
                return self::getRootComponent()->addComponent($component, ComponentBucketType::FELLOW);

            case ComponentBucketType::SUB:
                $this->subComponents[] = $component;

                // No need to instantiate the fellow components container here.
                // It can be lazily created by the component instance when
                // needed.
                return true;
        }

        return false;
    }

    /**
     * Creates a component instance by class name.
     *
     * @param string $className
     *   Fully-qualified class name.
     * @param string $componentBucketType
     *   The bucket where to put to component.
     *   One of ComponentBucketType::* consts.
     *
     * @return object|null
     *   The instantiated component. Null on soft failure: class does not exist,
     *   no bucket set.
     *
     * @throws UnmetDependencyException
     *   Could not retrieve a mandatory dependency.
     */
    public function addComponentByClassName(string $className, string $componentBucketType = ComponentBucketType::SUB): ?object
    {
        if (!class_exists($className)) {
            // Not an existing class.
            return null;
        }

        if (is_a($className, ComponentBasedObject::class, true)) {
            // Specific features for ComponentBasedObject instances.
            if (!class_exists($className)) {
                // Not an existing class.
                return null;
            }

            // This is the reason why ComponentBasedObject constructors must be
            // devoid of any argument. The constructor call is generic.
            /** @var ComponentBasedObject $instance */
            $instance = new $className;

            // Register the component now. It is very important to register
            // the component before referencing dependencies.
            // That's how we are able to work with inter-dependent fellow
            // components.
            if (!$this->addComponent($instance, $componentBucketType)) {
                // Attempted to add a component which could not be referenced.
                return null;
            }

            // It is of the responsibility of the new instance to prepare its
            // dependencies from its perspective.
            $instance->referenceDependencies();

            // Let the instance initialize itself.
            $instance->onReady();
        }
        else {
            // Non-component based objects.
            $instance = new $className;

            if (!$this->addComponent($instance, $componentBucketType)) {
                // Attempted to add a component which could not be referenced.
                return null;
            }
        }

        return $instance;
    }

    /**
     * Creates a root component instance by class name.
     *
     * @param string $className
     *   Fully-qualified class name.
     *
     * @return object|null
     *   The instantiated component.
     *
     * @throws UnmetDependencyException
     *   Could not retrieve a mandatory dependency.
     */
    public static function addRootComponentByClassName(string $className): ?object
    {
        return self::getRootComponent()->addComponentByClassName($className, ComponentBucketType::FELLOW);
    }

    /**
     * Gets a single component by class name.
     *
     * @param string $className
     *   Class name.
     * @param bool $addIfNotFound
     *   Set to true to attempt instantiation if not found.
     * @param string $componentBucketType
     *   The bucket where to get to component.
     *   One of ComponentBucketType::* consts.
     *
     * @return object|null
     *   The component if found.
     *
     * @throws UnmetDependencyException
     *   Could not retrieve a mandatory dependency.
     */
    public function getComponentByClassName(string $className, bool $addIfNotFound = false, string $componentBucketType = ComponentBucketType::SUB): ?object
    {
        /** @var object[] $componentBucket */
        $componentBucket = $this->getComponents($componentBucketType);

        foreach ($componentBucket as $component) {
            if ($component instanceof $className) {
                return $component;
            }
        }

        if ($addIfNotFound === true) {
            $component = $this->addComponentByClassName($className, $componentBucketType);

            // May still be null.
            return $component;
        }

        // Not found.
        return null;
    }

    /**
     * Gets the component bucket for the given type.
     *
     * @param string $componentBucketType
     *   The bucket where to get to component.
     *   One of ComponentBucketType::* consts.
     *
     * @return object[]
     *   Array containing components (ComponentBasedObject or any object).
     */
    public function getComponents(string $componentBucketType = ComponentBucketType::SUB): array
    {
        switch ($componentBucketType) {
            case ComponentBucketType::FELLOW:
                // Look for "sibling" components.
                return $this->getFellowComponentsContainer()->components;

            case ComponentBucketType::ROOT:
                return self::getRootComponent()->getFellowComponentsContainer()->components;

            case ComponentBucketType::SUB:
                return $this->subComponents;
        }

        // Make this fail safe by returning a "fake" empty bucket.
        return [];
    }

    /**
     * Gets components by class name.
     *
     * @param string $className
     *   Class name.
     * @param string $componentBucketType
     *   The bucket where to get to component.
     *   One of ComponentBucketType::* consts.
     *
     * @return object[]
     *   Array of components. May be empty.
     */
    public function getComponentsByClassName(string $className, string $componentBucketType = ComponentBucketType::SUB): array
    {
        /** @var object[] $componentBucket */
        $componentBucket = $this->getComponents($componentBucketType);

        $components = [];

        foreach ($componentBucket as $component) {
            if ($component instanceof $className) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * Returns a collection of definitions of dependencies which are needed to
     * build this component.
     *
     * Extend this method when needed. Don't forget to call
     * parent::getDependencies() when extending.
     *
     * Remember: you can only define fellow or root dependencies.
     * Sub-dependencies are not allowed.
     *
     * @return DependencyDefinition[]
     *   An array of dependency definitions.
     */
    public function getDependencyDefinitions(): array
    {
        return [];
    }

    /**
     * Gets the fellow components container.
     *
     * Creates it if it does not exist.
     *
     * @return ComponentContainer
     *   The container.
     */
    private function getFellowComponentsContainer() {
        if (empty($this->fellowComponentsContainer)) {
            $this->fellowComponentsContainer = new ComponentContainer();
        }

        return $this->fellowComponentsContainer;
    }

    /**
     * Gets the root components container.
     *
     * @return ComponentBasedObject
     *   The global container.
     */
    private static function getRootComponent(): ComponentBasedObject
    {
        if (empty(self::$rootComponent)) {
            self::$rootComponent = new self();
        }

        return self::$rootComponent;
    }

    /**
     * Gets a root component by class name.
     *
     * @param string $className
     *   Class name.
     * @param bool $addIfNotFound
     *   Set to true to attempt instantiation if not found.
     *
     * @return object|null
     *   The object. Null value if not found.
     *
     * @throws UnmetDependencyException
     *   Could not retrieve a mandatory dependency.
     */
    public static function getRootComponentByClassName(
        string $className,
        bool $addIfNotFound = false
    ): ?object {
        return self::getRootComponent()->getComponentByClassName($className, $addIfNotFound, ComponentBucketType::FELLOW);
    }

    /**
     * Gets the root components.
     *
     * @return object[]
     *   Array of components.
     */
    public static function getRootComponents(): array
    {
        return self::getRootComponent()->getComponents(ComponentBucketType::FELLOW);
    }

    /**
     * Gets root components by class name.
     *
     * @param string $className
     *   Class name.
     *
     * @return object[]
     *   Array of components. May be empty.
     */
    public static function getRootComponentsByClassName(string $className): array
    {
        return self::getRootComponent()->getComponentsByClassName($className, ComponentBucketType::FELLOW);
    }

    /**
     * Tells if there is a component by class name.
     *
     * @param string $className
     *   The class name.
     * @param string $componentBucketType
     *   The bucket where to check.
     *   One of ComponentBucketType::* consts.
     *
     * @return bool
     *   True if there is at least one component instance, false otherwise.
     */
    public function hasComponentByClassName(string $className, string $componentBucketType = ComponentBucketType::SUB): bool
    {
        /** @var object[] $componentBucket */
        $componentBucket = $this->getComponents($componentBucketType);

        foreach ($componentBucket as $component) {
            if ($component instanceof $className) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tells if there is a root component by class name.
     *
     * @param string $className
     *   The class name.
     *
     * @return bool
     *   True if there is at least one component instance, false otherwise.
     */
    public static function hasRootComponentByClassName(string $className): bool
    {
        return self::getRootComponent()->hasComponentByClassName($className, ComponentBucketType::FELLOW);
    }

    /**
     * Called after this instance has been added to its holder.
     *
     * Useful for initializations which runs every time this object is
     * instantiated, like creating a default set of sub components.
     */
    protected function onReady(): void
    {
        // Implement if needed.
    }

    /**
     * Called after removal from the component which held this instance.
     *
     * This gives the opportunity to clean up things like unsetting references,
     * changing a value back to an ancient value and such.
     */
    protected function onRemoved(): void
    {
        // Implement if needed.
    }

    /**
     * Processes the dependency definition for this component.
     *
     * @param DependencyDefinition $dependencyDefinition
     *   Dependency definition to process.
     *
     * @return DependencyDefinition|null
     *   A backup DependencyDefinition if failed to process this dependency,
     *   or null if all went well.
     *
     * @throws UnmetDependencyException
     *   Could not retrieve a mandatory dependency.
     */
    private function processDependencyDefinition(DependencyDefinition $dependencyDefinition): ?DependencyDefinition
    {
        if (empty($className = $dependencyDefinition->getClassName())) {
            // Empty definition.
            if (!empty($backup = $dependencyDefinition->getBackupDefinition())) {
                // A backup exists. Return the handle to try to use it instead.
                return $backup;
            }

            throw new UnmetDependencyException(sprintf('Tried to instantiate a dependency, but the class name was not set.'));
        }

        if (!class_exists($className)) {
            // Not an existing class.
            if (!empty($backup = $dependencyDefinition->getBackupDefinition())) {
                // A backup exists. Return the handle to try to use it instead.
                return $backup;
            }

            throw new UnmetDependencyException(sprintf('Tried to instantiate a dependency, but could not find it and no more backup dependencies were provided. Expected dependency class name was: %s', $className));
        }

        // The class exists.
        // Determine the container of the instantiated component/object.
        $componentBucketType = $dependencyDefinition->getComponentBucketType();

        // We expect only a single component instance of a given class name.
        // This is why we use get(Root)ComponentByClassName instead of
        // add(Root)ComponentByClassName.
        // If multiple components of the same class name must be instantiated,
        // it must be handled after the dependency referencing step, when the
        // component is ready.
        switch ($componentBucketType) {
            case ComponentBucketType::FELLOW:
                // Add to/get from this component based object.
                $instance = $this->getComponentByClassName($className, true, ComponentBucketType::FELLOW);

                if (empty($instance)) {
                    // Failed to retrieve a suitable instance.
                    throw new UnmetDependencyException(sprintf('Failed to retrieve a suitable instance. Dependency class name was: %s', $className));
                }

                break;

            case ComponentBucketType::ROOT:
                // Add to/get from root components.
                $instance = static::getRootComponentByClassName($className, true);

                if (empty($instance)) {
                    // Failed to retrieve a suitable instance.
                    throw new UnmetDependencyException(sprintf('Failed to retrieve a suitable instance. Dependency class name was: %s', $className));
                }

                break;

            case ComponentBucketType::SUB:
                // We cannot depend on sub-components.
                // This is too dangerous, it could lead to infinite inclusions.
                throw new UnmetDependencyException(sprintf('Instantiating a sub-component dependency is forbidden on auto-dependency processing stage. Dependency class name was: %s, dependee class name was: %s', $className, __CLASS__));

            default:
                // Unknown dependency type.
                throw new UnmetDependencyException(sprintf('Tried to instantiate a dependency, but could not determine were it was expected to be appended. Expected dependency class name was: %s, given dependency type was: %s', $className, $componentBucketType));
        }

        // Reference the instance on the depender.
        // (if the property reference has been provided on DependencyDefinition).
        $dependencyDefinition->setDependerPropertyValue($instance);

        // Dependency retrieved.
        return null;
    }

    /**
     * References/auto-instantiate the dependencies of this component.
     *
     * @throws UnmetDependencyException
     *   Could not retrieve a mandatory dependency.
     */
    private function referenceDependencies(): void
    {
        // Get the dependencies declarations.
        $dependencyDefinitions = $this->getDependencyDefinitions();

        foreach ($dependencyDefinitions as $dependencyDefinition) {
            if (empty($dependencyDefinition)) {
                // Should not happen, but let's make this code fail safe.
                continue;
            }

            $definitionToProcess = $dependencyDefinition;

            do {
                // We continue to process this dependency and its backups until
                // processDependency() returns an empty result.
                $definitionToProcess = $this->processDependencyDefinition($definitionToProcess);
            }
            while (!empty($definitionToProcess));
        }
    }

    /**
     * Removes the specified component instance.
     *
     * Note: this does not delete the component! If any other object has a
     * reference to it, it will still exist in memory. This may lead to memory
     * leaks if not used properly.
     *
     * @param object $component
     *   The component instance to remove.
     * @param string $componentBucketType
     *   The bucket where to check.
     *   One of ComponentBucketType::* consts.
     */
    public function removeComponent(object $component, string $componentBucketType = ComponentBucketType::SUB): void
    {
        /** @var object[] $componentBucket */
        $componentBucket = $this->getComponents($componentBucketType);

        foreach ($componentBucket as $key => $c) {
            // This will compare by reference.
            if ($c === $component) {
                unset($this->subComponents[$key]);

                if ($c instanceof ComponentBasedObject) {
                    $c->onRemoved();
                }

                return;
            }
        }
    }

    /**
     * Removes all components by class name.
     *
     * Note: this does not delete the component! If any other object has a
     * reference to it, it will still exist in memory. This may lead to memory
     * leaks if not used properly.
     *
     * @param string $className
     *   Class name.
     * @param string $componentBucketType
     *   The bucket where to check.
     *   One of ComponentBucketType::* consts.
     */
    public function removeComponentsByClassName(string $className, string $componentBucketType = ComponentBucketType::SUB): void
    {
        /** @var object[] $componentBucket */
        $componentBucket = $this->getComponents($componentBucketType);

        foreach ($componentBucket as $key => $c) {
            if ($c instanceof $className) {
                unset($this->subComponents[$key]);

                if ($c instanceof ComponentBasedObject) {
                    $c->onRemoved();
                }
            }
        }
    }

    /**
     * Removes the specified root component instance.
     *
     * Note: this does not delete the component! If any other object has a
     * reference to it, it will still exist in memory. This may lead to memory
     * leaks if not used properly.
     *
     * @param object $component
     *   The component instance to remove.
     */
    public static function removeRootComponent(object $component): void
    {
        self::getRootComponent()->removeComponent($component, ComponentBucketType::FELLOW);
    }

    /**
     * Removes all root components by class name.
     *
     * Note: this does not delete the component! If any other object has a
     * reference to it, it will still exist in memory. This may lead to memory
     * leaks if not used properly.
     *
     * @param string $className
     *   Class name.
     */
    public static function removeRootComponentsByClassName(string $className): void
    {
        self::getRootComponent()->removeComponentsByClassName($className, ComponentBucketType::FELLOW);
    }

    /**
     * Sets the fellow components container.
     *
     * @param ComponentContainer $container
     *   Container.
     */
    private function setFellowComponentsContainer(ComponentContainer $container) {
        $this->fellowComponentsContainer = $container;
    }
}
