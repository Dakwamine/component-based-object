<?php

namespace Dakwamine\Component;

/**
 * Dependency definition for fellow components.
 */
class FellowDependencyDefinition extends DependencyDefinition
{
    /**
     * {@inheritdoc}
     */
    public function __construct($className, $dependerPropertyToSet = null, DependencyDefinition $backupDependencyDefinition = null) {
        parent::__construct($className, $dependerPropertyToSet, $backupDependencyDefinition);
        $this->componentBucketType = ComponentBucketType::FELLOW;
    }
}
