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
    public function __construct($className, DependencyDefinition $backupDependencyDefinition = null) {
        parent::__construct($className, $backupDependencyDefinition);
        $this->componentBucketType = ComponentBucketType::ROOT;
    }
}
