<?php
/**
 * LtiData.php - LTI consumer API for Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class LtiData extends SimpleORMap
{
    /**
     * Configure the database mapping.
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'lti_data';

        $config['serialized_fields']['options'] = 'JSONArrayObject';

        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'course_id'
        ];
        $config['belongs_to']['tool'] = [
            'class_name'  => LtiTool::class,
            'foreign_key' => 'tool_id'
        ];

        $config['has_many']['grades'] = [
            'class_name'        => LtiGrade::class,
            'assoc_foreign_key' => 'link_id',
            'on_delete'         => 'delete'
        ];

        parent::configure($config);
    }

    /**
     * Find a single entry by course_id and position.
     */
    public static function findByCourseAndPosition($course_id, $position)
    {
        return self::findOneBySQL('course_id = ? AND position = ?', [$course_id, $position]);
    }

    /**
     * Delete this entity.
     */
    public function delete()
    {
        $db = DBManager::get();
        $course_id = $this->course_id;
        $position = $this->position;

        if ($result = parent::delete()) {
            $db->execute('UPDATE lti_data SET position = position - 1 WHERE course_id = ? AND position > ?', [$course_id, $position]);
        }

        return $result;
    }

    /**
     * Get the launch_url of this entry.
     */
    public function getLaunchURL()
    {
        if ($this->tool_id) {
            if (!$this->tool->allow_custom_url && !$this->tool->deep_linking || !$this->launch_url) {
                return $this->tool->launch_url;
            }
        }

        return $this->launch_url;
    }

    /**
     * Get the consumer_key of this entry.
     */
    public function getConsumerKey()
    {
        if ($this->tool_id) {
            return $this->tool->consumer_key;
        }

        return $this->options['consumer_key'];
    }

    /**
     * Get the consumer_secret of this entry.
     */
    public function getConsumerSecret()
    {
        if ($this->tool_id) {
            return $this->tool->consumer_secret;
        }

        return $this->options['consumer_secret'];
    }

    /**
     * Get the custom_parameters of this entry.
     */
    public function getCustomParameters()
    {
        if ($this->tool_id) {
            return $this->tool->custom_parameters . "\n" . $this->options['custom_parameters'];
        }

        return $this->options['custom_parameters'];
    }

    /**
     * Get the send_lis_person attribute of this entry.
     */
    public function getSendLisPerson()
    {
        if ($this->tool_id) {
            return $this->tool->send_lis_person;
        }

        return $this->options['send_lis_person'];
    }
}
