<?php

namespace Dakwamine\Component;

/**
 * Dependency definition for root components.
 */
class RootDependencyDefinition extends DependencyDefinition
{
    /**
     * {@inheritdoc}
     */
    public function __construct($className, &$dependerPropertyToSet = null, DependencyDefinition $backupDependencyDefinition = null) {
        parent::__construct($className, $dependerPropertyToSet, $backupDependencyDefinition);
        $this->componentBucketType = ComponentBucketType::ROOT;
    }
}
