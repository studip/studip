<?php
namespace RESTAPI;
use \Exception;

/**
 * Router exception.
 *
 * @author     Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author     <mlunzena@uos.de>
 * @license    GPL 2 or later
 * @since      Stud.IP 3.0
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class RouterException extends Exception
{
    protected static $error_messages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
        501 => 'Not implemented',
    ];

    public function __construct($code = 500, $message = '', $previous = null)
    {
        $message = $message ?: self::$error_messages[$code] ?: '';
        parent::__construct($message, $code, $previous);
    }
}
