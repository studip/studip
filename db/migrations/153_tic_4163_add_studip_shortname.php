<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 148_tic_4520_sem_tree_display
 *
 * @author intelec
 */
class Tic4163AddStudipShortname extends Migration {
    
    function description() {
        return 'Adds studip short name (for notifications) in confing';
    }

    function up() {
        Config::get()->create('STUDIP_SHORT_NAME', array(
            'value' => 'Stud.IP',
            'is_default' => 'Stud.IP',
            'type' => 'string',
            'range' => 'global',
            'section' => 'global',
            'description' => _('Studip Kurzname')
        ));
    }
    
    function down() {
        Config::get()->delete('STUDIP_SHORT_NAME');
    }

}
