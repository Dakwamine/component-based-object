<?php

namespace Dakwamine\Component;

/**
 * Contains components on the same "level".
 *
 * A container is usually created when a subcomponent is created. This is needed
 * for fellow dependency instantiation.
 */
class ComponentContainer {

    /**
     * Components.
     *
     * Known as sibling/fellow components.
     *
     * @var object[]
     */
    public $components = [];

}
