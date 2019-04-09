<?php

namespace FilesSearch;

/**
 * Cron job that re-indexes all files.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class Cronjob extends \CronJob
{
    /**
     * {@inheritdoc}
     */
    public static function getName()
    {
        return _('Index der Dokumentensuche erneuern');
    }

    /**
     * {@inheritdoc}
     */
    public static function getDescription()
    {
        return _('Aktualisiert den Index der Dokumentensuche.');
    }

    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return [
            'verbose' => [
                'type' => 'boolean',
                'default' => false,
                'status' => 'optional',
                'description' => _('Sollen Ausgaben erzeugt werden'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($lastResult, $parameters = [])
    {
        require_once 'lib/classes/FilesSearch/FilesIndexManager.php';
        FilesIndexManager::sqlIndex(null, ['verbose' => $parameters['verbose']]);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
    }
}
