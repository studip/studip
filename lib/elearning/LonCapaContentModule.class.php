<?php
/**
 *
 * This class contains methods to handle LonCapa learning modules
 *
 * @modulegroup  elearning_interface_modules
 * @module       LonCapaContentModule
 * @package  ELearning-Interface
 */

class LonCapaContentModule extends ContentModule
{
    /**
     * @var LonCapaRequest
     */
    public $lcRequest;
    /**
     * @var string
     */
    public $cmsUrl;

    /**
     * LonCapaContentModule constructor.
     * @param string $module_id
     * @param string $module_type
     * @param string $cms_type
     */
    public function __construct($module_id = "", $module_type, $cms_type)
    {
        $this->lcRequest = new LonCapaRequest();
        $this->cmsUrl = $GLOBALS['ELEARNING_INTERFACE_MODULES'][$cms_type]['ABSOLUTE_PATH_ELEARNINGMODULES'];

        parent::__construct($module_id, $module_type, $cms_type);
    }

    /**
     *fetch data from LonCapa
     *
     */
    public function readData()
    {
        $url = $this->cmsUrl . '/course/' . urlencode($this->id);
        $response = $this->lcRequest->request($url);

        if ($response) {
            $courses = new SimpleXMLElement($response);
            $course = $courses->course[0];

            list($author, $dummy) = explode(':', (string)$course->owner);

            $this->id = (string)$course->id;
            $this->title = (string)$course->description;
            $this->authors = $author;
        }

    }

    /**
     * get permission-status
     *
     *
     * @param string $operation operation
     * @return boolean allowed
     */
    public function isAllowed($operation)
    {
        return true;
    }

    /**
     * store connection between Stud.IP course and LonCapa course
     *
     * @param string $seminar_id
     * @return bool
     */
    public function setConnection($seminar_id)
    {
        $this->is_connected = true;
        return ObjectConnections::setConnection(
            $seminar_id,
            $this->id,
            $this->module_type,
            $this->cms_type
        );
    }
}
