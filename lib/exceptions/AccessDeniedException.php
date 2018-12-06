<?php
/**
 * AccessDeniedException.php
 *
 * Use this exception whenever a user tries to access a restricted part of the
 * system without having the required permissions.
 *
 * @author   Marcus Lunzenauer <mlunzena@uos.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 */
class AccessDeniedException extends Exception
{
    private $details = [];

    /**
     * Constucts the exception
     * @param string    $message  Exception message
     * @param integer   $code     Exception code
     * @param Exception $previous Previous exception (optional)
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (func_num_args() === 0) {
            $message = _('Sie haben nicht die Berechtigung, diese Aktion '
                       . 'auszuführen bzw. diesen Teil des Systems zu betreten.');
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Set additional details for the exception.
     * @param array $details Additional details
     */
    public function setDetails(array $details)
    {
        $this->details = $details;
    }

    /**
     * Get the additional details for the exception.
     * @return array Additional details
     */
    public function getDetails()
    {
        return $this->details;
    }
}
