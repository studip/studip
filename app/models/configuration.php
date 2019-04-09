<?php
/**
 * configuration.php - model class for the configuration
 *
 * @author      Nico Müller <nico.mueller@uni-oldenburg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     GPL2 or any later version
 * @category    Stud.IP
 * @package     admin
 * @since       2.0
 */
class ConfigurationModel
{
    /*
     * Get all config-files
     */
    public static function getConfig($section = null, $name = null)
    {
        $config = Config::get();
        $allconfigs = [];
        foreach ($config->getFields('global', $section, $name) as $field) {
            $metadata = $config->getMetadata($field);
            $metadata['value'] = $config->$field;
            $allconfigs[$metadata['section']][] = $metadata;
        }
        return $allconfigs;
    }

    /**
     * Search the user configuration from the config or give all parameter
     * with range=user
     *
     * @param   string $user_id
     *
     * @return array()
     */
    public static function searchUserConfiguration($user_id = null)
    {
        $config = Config::get();
        $allconfigs = [];
        if (isset($user_id)) {
            $user = User::find($user_id);

            $uconfig = UserConfig::get($user_id);
            foreach ($uconfig as $field => $value) {
                $data = $config->getMetadata($field);
                if (!count($data)) {
                    $data['field'] = $field;
                    $data['type'] = 'string';
                    $data['description'] = 'missing in table `config`';
                }
                $data['value'] = $value;
                $data['fullname'] = $user->getFullname();
                $allconfigs[] = $data;
            }
        } else {
            foreach ($config->getFields('user') as $field) {
                $metadata = $config->getMetadata($field);
                $metadata['value'] = $config->$field;
                $allconfigs[] = $metadata;
            }
        }
        return $allconfigs;
    }

    /**
     * Show the user configuration for one parameter
     *
     * @param   string $user_id
     * @param   string $field
     *
     * @return  array()
     */
    public static function showUserConfiguration($user_id, $field)
    {
        $uconfig = UserConfig::get($user_id);
        $config = Config::get();
        $data = $config->getMetadata($field);
        if (!count($data)) {
            $data['field'] = $field;
            $data['type'] = 'string';
            $data['description'] = 'missing in table `config`';
        }
        $data['value'] = $uconfig->$field;
        $data['fullname'] = User::find($user_id)->getFullname();
        return $data;
    }

    /**
     * Search the course configuration from the config or give all parameter
     * with range=course
     *
     * @param   string $range_id
     *
     * @return array()
     */
    public static function searchCourseConfiguration($range_id = null)
    {
        $config = Config::get();
        $allconfigs = [];
        if (isset($range_id)) {
            $course = Course::find($range_id);

            $uconfig = CourseConfig::get($range_id);
            foreach ($uconfig as $field => $value) {
                $data = $config->getMetadata($field);
                if(!count($data)) {
                    $data['field'] = $field;
                    $data['type'] = 'string';
                    $data['description'] = 'missing in table `config`';
                }
                $data['value'] = $value;
                $data['fullname'] = $course->getFullname();
                $allconfigs[] = $data;
            }
        } else {
            foreach ($config->getFields('course') as $field) {
                $metadata = $config->getMetadata($field);
                $metadata['value'] = $config->$field;
                $allconfigs[] = $metadata;
            }
        }
        return $allconfigs;
    }

    /**
     * Show the course configuration for one parameter
     *
     * @param   string $range_id
     * @param   string $field
     *
     * @return  array()
     */
    public static function showCourseConfiguration($range_id, $field)
    {
        $uconfig = CourseConfig::get($range_id);
        $config = Config::get();
        $data = $config->getMetadata($field);
        if (!count($data)) {
            $data['field'] = $field;
            $data['type'] = 'string';
            $data['description'] = 'missing in table `config`';
        }
        $data['value'] = $uconfig->$field;
        $data['fullname'] = Course::find($range_id)->getFullname();
        return $data;
    }

    /**
     * Show all information for one configuration parameter
     *
     * @param string $field
     */
    public static function getConfigInfo($field)
    {
        $config = Config::get();
        $metadata = $config->getMetadata($field);
        $metadata['value'] = $config->$field;
        return $metadata;
    }
}
