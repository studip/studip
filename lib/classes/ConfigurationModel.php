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
     * Search the configuration from the config or give all parameters
     *
     * @param Range  $range
     * @param string $limit_to Limit entries to certain range type
     * @return array()
     */
    public static function searchConfiguration(Range $range = null)
    {
        $config = Config::get();
        $allconfigs = [];
        if ($range && !$range->isNew()) {
            foreach ($range->getConfiguration() as $field => $value) {
                $data = $config->getMetadata($field) ?: [
                    'field'       => $field,
                    'type'        => 'string',
                    'description' => 'missing in table `config`',
                ];
                $data['value'] = $value;
                $data['fullname'] = $range->getFullname();

                $allconfigs[] = $data;
            }
        } else {
            foreach ($config->getFields($range ? $range->getRangeType() : 'range') as $field) {
                $metadata = $config->getMetadata($field);
                $metadata['value'] = $config->$field;

                $allconfigs[] = $metadata;
            }
        }
        return $allconfigs;
    }

    /**
     * Show the range configuration for one parameter
     *
     * @param  Range $range
     * @return array
     */
    public static function showConfiguration(Range $range, $field)
    {
        $data = Config::get()->getMetadata($field) ?: [
            'field'       => $field,
            'type'        => 'string',
            'description' => 'missing in table `config`',
        ];

        $data['value']    = $range->getConfiguration()->$field;
        $data['fullname'] = $range->getFullname();

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
