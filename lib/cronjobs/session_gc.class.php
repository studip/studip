<?php
/**
* session_gc.class.php
*
* @author André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access public
* @since  2.4
*/

class SessionGcJob extends CronJob
{

    public static function getName()
    {
        return _('Sessions bereinigen');
    }

    public static function getDescription()
    {
        return _('Entfernt abgelaufene session Daten');
    }

    public function execute($last_result, $parameters = [])
    {
        $sess = new Seminar_Session();
        $sess->set_container();
        return $sess->gc();
    }
}
