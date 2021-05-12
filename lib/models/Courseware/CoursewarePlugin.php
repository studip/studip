<?php

namespace Courseware;

/**
 * Interface for a courseware plugin.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
interface CoursewarePlugin
{
    /**
     * Implement this method to register more block types.
     *
     * You get the current list of block types and must return an updated list
     * containing your own block types.
     *
     * @param array $otherBlockTypes the current list of block types
     *
     * @return array the updated list of block types
     */
    public function registerBlockTypes(array $otherBlockTypes): array;

    /**
     * Implement this method to register more container types.
     *
     * You get the current list of container types and must return an updated list
     * containing your own container types.
     *
     * @param array $otherContainerTypes the current list of container types
     *
     * @return array the updated list of container types
     */
    public function registerContainerTypes(array $otherContainerTypes): array;
}
