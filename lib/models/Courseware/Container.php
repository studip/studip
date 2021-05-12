<?php

namespace Courseware;

use User;

/**
 * Courseware's containers.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Till Glöggler <gloeggler@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property int                                      $id                    database column
 * @property int                                      $structural_element_id database column
 * @property string                                   $owner_id              database column
 * @property string                                   $editor_id             database column
 * @property string                                   $edit_blocker_id       database column
 * @property int                                      $position              database column
 * @property int                                      $site                  database column
 * @property string                                   $container_type        database column
 * @property int                                      $visible               database column
 * @property string                                   $payload               database column
 * @property int                                      $mkdate                database column
 * @property int                                      $chdate                database column
 * @property \Courseware\ContainerTypes\ContainerType $type                  computed column read/write
 * @property \SimpleORMapCollection                   $blocks                has_many Courseware\Block
 * @property \User                                    $owner                 belongs_to User
 * @property \User                                    $editor                belongs_to User
 * @property \User                                    $edit_blocker          belongs_to User
 * @property \Courseware\StructuralElement            $structural_element    belongs_to Courseware\StructuralElement
 */
class Container extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_containers';

        $config['serialized_fields']['payload'] = 'JSONArrayObject';

        $config['has_many']['blocks'] = [
            'class_name' => Block::class,
            'assoc_foreign_key' => 'container_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY position',
        ];

        $config['belongs_to']['owner'] = [
            'class_name' => User::class,
            'foreign_key' => 'owner_id',
        ];

        $config['belongs_to']['editor'] = [
            'class_name' => User::class,
            'foreign_key' => 'editor_id',
        ];
        $config['belongs_to']['edit_blocker'] = [
            'class_name' => User::class,
            'foreign_key' => 'edit_blocker_id',
        ];

        $config['belongs_to']['structural_element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'structural_element_id',
        ];

        $config['additional_fields']['type'] = [
            'get' => function ($container) {
                return ContainerTypes\ContainerType::factory($container);
            },
            'set' => false,
        ];

        parent::configure($config);
    }

    /**
     * Returns the structural element this container belongs to.
     *
     * @return StructuralElement the structural element
     */
    public function getStructuralElement(): StructuralElement
    {
        return $this->structural_element;
    }

    /**
     * Returns the number of blocks contained in this.
     *
     * @return int the number of blocks contained in this
     */
    public function countBlocks(): int
    {
        return Block::countBySql('container_id = ?', [$this->id]);
    }

    /**
     * Copies this block into another structural element such that the given user is the owner of the copy.
     *
     * @param User              $user    the owner and editor of the new copy of this block
     * @param StructuralElement $element the structural element this block will be copied into
     *
     * @return Container the copy of this Container
     */
    public function copy(User $user, StructuralElement $element): Container
    {
        $container = self::build([
            'structural_element_id' => $element->id,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => null,
            'position' => $element->countContainers(),
            'container_type' => $this->type->getType(),
            'payload' => $this['payload'],
        ]);

        $container->store();

        $blockMap = self::copyBlocks($user, $container, $this);

        $container['payload'] = $container->type->copyPayload($blockMap);

        $container->store();

        return $container;
    }

    private function copyBlocks(User $user, Container $newContainer, Container $remoteContainer): array
    {
        $blockMap = [];

        $blocks = Block::findBySQL('container_id = ?', [$remoteContainer->id]);

        foreach ($blocks as $block) {
            $newBlock = $block->copy($user, $newContainer);
            $blockMap[$block->id] = $newBlock->id;
        }

        return $blockMap;
    }
}
