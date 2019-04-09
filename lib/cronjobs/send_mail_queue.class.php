<?php
/**
* send_mail_queue.class.php
*
* @author Rasmus Fuhse <fuhse@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access public
* @since  3.0
*/

/**
 * Cronjob class to send the mailqueue each interval.
 */
class SendMailQueueJob extends CronJob
{

    /**
     * Returns the name of the cronjob.
     * @return string : name of the cronjob
     */
    public static function getName()
    {
        return _('Mailqueue senden');
    }

    /**
     * Returns the description of the cronjob.
     * @return string : description of the cronjob.
     */
    public static function getDescription()
    {
        return _('Sendet alle Einträge in der Mailqueue bis zu 24 Stunden, nachdem sie hinzugefügt wurden.');
    }

    /**
     * Sends all mails in the queue.
     * @param integer $last_result : not evaluated for execution, so any integer
     * will do. Usually it would be a unix-timestamp of last execution. But in
     * this case we don't care at all.
     * @param array $parameters : not needed here
     */
    public function execute($last_result, $parameters = [])
    {
        $status_messages = MailQueueEntry::sendAll(
            Config::get()->MAILQUEUE_SEND_LIMIT,
            (bool)$parameters['verbose']
        );

        //We output one status message per line:
        echo implode("\n", $status_messages);
    }

    /**
     * Returns a list of available parameters for this cronjob.
     * See the description in the CronJob class for a specification
     * for the returned array.
     *
     * @return array A list of available parameters for this cronjob.
     */
    public static function getParameters()
    {
        return [
            'verbose' => [
                'type'        => 'boolean',
                'default'     => false,
                'status'      => 'optional',
                'description' => _('Sollen Ausgaben erzeugt werden? Diese sind später im Log des Cronjobs sichtbar.'),
            ],
        ];
    }
}
