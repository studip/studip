<?php

namespace JsonApi\Models;

class Studip
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getId()
    {
        return 'studip';
    }

    public function getProperties()
    {
        $properties = [
            new StudipProperty('studip-version', 'Stud.IP-Version', $GLOBALS['SOFTWARE_VERSION']),
        ];

        $copyrightDialog = self::getConfigOption('COPYRIGHT_DIALOG_ON_UPLOAD');
        if ($copyrightDialog) {
            $properties[] = $copyrightDialog;
        }

        return $properties;
    }

    private static function getConfigOption($field)
    {
        $config = \Config::get();

        if (!isset($config[$field])) {
            return null;
        }

        $description = $config->getMetadata($field)['description'];
        $value = $config->getValue($field);

        return new StudipProperty($field, $description, $value);
    }
}
