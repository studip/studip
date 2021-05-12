<?php

namespace Courseware;

use User;

/**
 * Courseware's feedback on blocks.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property int               $id        database column
 * @property int               $block_id  database column
 * @property string            $user_id database column
 * @property string            $feedback  database column
 * @property int               $mkdate    database column
 * @property int               $chdate    database column
 * @property \User             $user      belongs_to User
 * @property \Courseware\Block $block     belongs_to Courseware\Block
 */
class BlockFeedback extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_block_feedbacks';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['block'] = [
            'class_name' => Block::class,
            'foreign_key' => 'block_id',
        ];

        parent::configure($config);
    }
}
