<?php

namespace Courseware;

/**
 * This class represents an instance of a courseware of a course or a user.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Instance
{
    public static function deleteForRange(\Range $range): void
    {
        $root = null;
        switch ($range->getRangeType()) {
            case 'course':
                $root = StructuralElement::getCoursewareCourse($range->getRangeId());
                break;
            case 'user':
                $root = StructuralElement::getCoursewareUser($range->getRangeId());
                break;
            default:
                throw new \InvalidArgumentException('Only ranges of type "user" and "course" are currently supported.');
        }

        // there is no courseware for this course
        if (!$root) {
            return;
        }

        $instance = new self($root);

        $range->getConfiguration()->delete('COURSEWARE_SEQUENTIAL_PROGRESSION');
        $range->getConfiguration()->delete('COURSEWARE_EDITING_PERMISSION');

        $last_element_configs = \ConfigValue::findBySQL('field = ? AND value LIKE ?', ['COURSEWARE_LAST_ELEMENT', '%'.$range->getRangeId().'%']);
        foreach ($last_element_configs as $config) {
            $arr = json_decode($config->value, true);
            $arr = array_filter($arr, function ($key) use ($range) {
                return $key !== $range->id;
            }, ARRAY_FILTER_USE_KEY);
            \UserConfig::get($config->range_id)->unsetValue('COURSEWARE_LAST_ELEMENT');
            \UserConfig::get($config->range_id)->store('COURSEWARE_LAST_ELEMENT', $arr);
        }

        $root->delete();
    }

    /**
     * @var StructuralElement
     */
    private $root;

    /**
     * Create a new representation of a a courseware instance.
     *
     * This model class purely represents and does not create anything. Its purpose is to have all things related to a
     * single courseware instance in one place.
     *
     * @param StructuralElement $root the root of this courseware instance
     */
    public function __construct(StructuralElement $root)
    {
        $this->root = $root;
    }

    /**
     * Returns the root element of this courseware instance.
     *
     * @return StructuralElement the root element of this courseware instance
     */
    public function getRoot(): StructuralElement
    {
        return $this->root;
    }

    /**
     * Returns the range this courseware instance belongs to.
     *
     * @return \Range the range this courseware instance belongs to
     */
    public function getRange(): \Range
    {
        $rangeType = $this->root['range_type'];

        return $this->root->$rangeType;
    }

    /**
     * Returns the type of this courseware instance's range as coded in the root element.
     *
     * @return string the type of this courseware instance's range
     */
    public function getRangeType(): string
    {
        return $this->root['range_type'];
    }

    /**
     * Returns all associated block types registered to this courseware instance.
     *
     * @return array a list of all associated block types
     */
    public function getBlockTypes(): array
    {
        $types = BlockTypes\BlockType::getBlockTypes();

        return $types;
    }

    /**
     * Returns all associated container types registered to this courseware instance.
     *
     * @return array a list of all associated block types
     */
    public function getContainerTypes(): array
    {
        $types = ContainerTypes\ContainerType::getContainerTypes();

        return $types;
    }

    /**
     * Returns a user's favorite block types for this instance.
     *
     * @param \User $user the user for whom the favorite block types will be returned
     *
     * @return array a list of favorite block types
     */
    public function getFavoriteBlockTypes(\User $user): array
    {
        /** @var array $favoriteBlockTypes */
        $favoriteBlockTypes = \UserConfig::get($user->id)->getValue('COURSEWARE_FAVORITE_BLOCK_TYPES');

        return $favoriteBlockTypes;
    }

    /**
     * Sets a user's favorite block types for this courseware instance.
     *
     * @param \User $user      the user for whom the favorite block types will be set
     * @param array $favorites the list of favorite block types
     */
    public function setFavoriteBlockTypes(\User $user, array $favorites): void
    {
        \UserConfig::get($user->id)->store('COURSEWARE_FAVORITE_BLOCK_TYPES', $favorites);
    }

    /**
     * Returns whether this courseware instance uses a sequential progression through the structural elements.
     *
     * @return bool true if this courseware instance uses a sequential progression, false otherwise
     */
    public function getSequentialProgression(): bool
    {
        $range = $this->getRange();
        $config = $range->getConfiguration()->getValue('COURSEWARE_SEQUENTIAL_PROGRESSION');

        return (bool) $config;
    }

    /**
     * Sets whether this courseware instance uses a sequential progression through the structural elements.
     *
     * @param bool $isSequentialProgression true if this courseware instance uses a sequential progression
     */
    public function setSequentialProgression(bool $isSequentialProgression): void
    {
        $range = $this->getRange();
        $range->getConfiguration()->store('COURSEWARE_SEQUENTIAL_PROGRESSION', $isSequentialProgression);
    }

    const EDITING_PERMISSION_DOZENT = 'dozent';
    const EDITING_PERMISSION_TUTOR = 'tutor';

    /**
     * Returns the level needed to edit this courseware instance.
     *
     * @return string can be either `Instance::EDITING_PERMISSION_DOZENT` or  `Instance::EDITING_PERMISSION_TUTOR`
     */
    public function getEditingPermissionLevel(): string
    {
        $range = $this->getRange();
        /** @var string $editingPermissionLevel */
        $editingPermissionLevel = $range->getConfiguration()->getValue('COURSEWARE_EDITING_PERMISSION');
        $this->validateEditingPermissionLevel($editingPermissionLevel);

        return $editingPermissionLevel;
    }

    /**
     * Sets the level needed to edit this courseware instance.
     *
     * @param string $editingPermissionLevel can be either `Instance::EDITING_PERMISSION_DOZENT` or
     *                                       `Instance::EDITING_PERMISSION_TUTOR`
     */
    public function setEditingPermissionLevel(string $editingPermissionLevel): void
    {
        $this->validateEditingPermissionLevel($editingPermissionLevel);
        $range = $this->getRange();
        $range->getConfiguration()->store('COURSEWARE_EDITING_PERMISSION', $editingPermissionLevel);
    }

    /**
     * Validates a editing permission level.
     *
     * @param string $editingPermissionLevel the editing permission level to validate
     *
     * @return bool true if this editing permission level is valid, false otherwise
     */
    public function isValidEditingPermissionLevel(string $editingPermissionLevel): bool
    {
        return in_array($editingPermissionLevel, [self::EDITING_PERMISSION_DOZENT, self::EDITING_PERMISSION_TUTOR]);
    }

    private function validateEditingPermissionLevel(string $editingPermissionLevel): void
    {
        if (!$this->isValidEditingPermissionLevel($editingPermissionLevel)) {
            throw new \InvalidArgumentException('Invalid editing permission of courseware.');
        }
    }

    /**
     * Returns all bookmarks of a user associated to this courseware instance.
     *
     * @param \User $user the user for whom to find associated bookmarks for
     *
     * @return array a list of the given user's bookmarks associated to this instance
     */
    public function getUsersBookmarks(\User $user): array
    {
        return StructuralElement::findUsersBookmarksByRange($user, $this->getRange());
    }
}
