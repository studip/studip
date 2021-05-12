<?php

namespace Courseware;

/**
 * Courseware's user data fields.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Till Glöggler <gloeggler@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property array             $id       computed column read/write
 * @property string            $user_id  database column
 * @property id                $block_id database column
 * @property \JSONArrayObject  $payload  database column
 * @property int               $mkdate   database column
 * @property int               $chdate   database column
 * @property \Courseware\Block $block    belongs_to Courseware\Block
 * @property \User             $user     belongs_to User
 */
class UserDataField extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_user_data_fields';

        $config['serialized_fields']['payload'] = 'JSONArrayObject';

        $config['belongs_to']['block'] = [
            'class_name' => Block::class,
            'foreign_key' => 'block_id',
        ];

        $config['belongs_to']['user'] = [
            'class_name' => \User::class,
            'foreign_key' => 'user_id',
        ];

        parent::configure($config);
    }

    /**
     * Returns either an existing UserDataField of this user and block or creates a new one.
     *
     * @param \User $user  the user of the existing or new UserDataField
     * @param Block $block the block of the existing or new UserDataField
     *
     * @return UserDataField the either already existing or new UserDataField
     */
    public static function getUserDataField(\User $user, Block $block): UserDataField
    {
        /** @var ?UserDataField $userDataField */
        $userDataField = UserDataField::find([$user->id, $block->id]);
        if (!$userDataField) {
            // TODO: Jeder BlockType sollte hier sagen dürfen, welchen Defaultwert das UserDataField haben sollte
            $payload = [];
            /** @var UserDataField $userDataField */
            $userDataField = UserDataField::build([
                'user_id' => $user->id,
                'block_id' => $block->id,
                'payload' => $payload,
            ]);
        }

        return $userDataField;
    }
}
