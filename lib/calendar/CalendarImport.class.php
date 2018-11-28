<?
# Lifter002: TODO
# Lifter007: TODO

/**
 * CalendarImport.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     calendar
 */

class CalendarImport
{

    const IGNORE_ERRORS = 1;

    protected $_parser;
    private $data;
    private $public_to_private = false;

    public function __construct(&$parser, $data = null)
    {
        $this->_parser = $parser;
        $this->data = $data;
    }

    public function getContent()
    {
        return $this->data;
    }

    public function importIntoDatabase($range_id, $ignore = CalendarImport::IGNORE_ERRORS)
    {
        $this->_parser->changePublicToPrivate($this->public_to_private);
        if ($this->_parser->parseIntoDatabase($range_id, $this->getContent(), $ignore)) {
            return true;
        }

        return false;
    }

    public function importIntoObjects($ignore = CalendarImport::IGNORE_ERRORS)
    {
        $this->_parser->changePublicToPrivate($this->public_to_private);
        if ($this->_parser->parseIntoObjects($this->getContent(), $ignore)) {
            return true;
        }

        return false;
    }

    public function getObjects()
    {
        return $objects =& $this->_parser->getObjects();
    }

    public function getCount()
    {
        return $this->_parser->getCount($this->getContent());
    }

    public function changePublicToPrivate($value = TRUE)
    {
        $this->public_to_private = $value;
    }

    public function getClientIdentifier()
    {
        if (!$client_identifier = $this->_parser->getClientIdentifier()) {
            return $this->_parser->getClientIdentifier($this->getContent());
        }
        return $client_identifier;
    }

    public function setImportSem($do_import)
    {
        if ($do_import) {
            $this->_parser->import_sem = true;
        } else {
            $this->_parser->import_sem = false;
        }
    }

}
