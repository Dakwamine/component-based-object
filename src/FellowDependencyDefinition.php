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
    public function __construct($className, DependencyDefinition $backupDependencyDefinition = null) {
        parent::__construct($className, $backupDependencyDefinition);
        $this->componentBucketType = ComponentBucketType::FELLOW;
    }
}
