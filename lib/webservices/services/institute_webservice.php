<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * institute_webservice.php - Provides webservices for infos about
 *  institutes
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once('lib/webservices/api/studip_institute.php');

class InstituteService extends AccessControlledService
{
    function __construct()
    {
    $this->add_api_method('get_admins_for_institute',
                          ['string', 'string'],
                          ['string'],
                          'gets admins for institute');

    $this->add_api_method('get_lecturers_for_institute',
                          ['string', 'string'],
                          ['string'],
                          'gets lecturers for institute');
    }

    function get_admins_for_institute_action($api_key, $institute_id)
    {
        $institute_service = new StudipInstituteHelper();
        $admin_list = $institute_service->get_admins($institute_id);
        return $admin_list;
    }

    function get_lecturers_for_institute_action($api_key, $institute_id)
    {
        $institute_service = new StudipInstituteHelper();
        $lecturer_list = $institute_service->get_lecturers($institute_id);
        return $lecturer_list;
    }

}
