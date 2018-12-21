<?php
/**
 * Exception class used to report overlapping errors
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 */
class OverlapException extends Exception
{
    protected $details;

    public function __construct($message = '', array $details = [])
    {
        parent::__construct($message);

        $this->setDetails($details);
    }

    public function setDetails(array $details)
    {
        $this->details = $details;
    }

    public function getDetails()
    {
        return $this->details;
    }
}
