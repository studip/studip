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
class Tic4520SemTreeDisplay extends Migration {
    
    function description() {
        return 'adds switch for SemTree display';
    }

    function up() {
        Config::get()->create('COURSE_SEM_TREE_DISPLAY', array(
            'value' => 0,
            'is_default' => 0,
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'global',
            'description' => _('Zeigt den Studienbereichsbaum als Baum an')
        ));
        Config::get()->create('COURSE_SEM_TREE_CLOSED_LEVELS', array(
            'value' => '[1]',
            'is_default' => '[1]',
            'type' => 'array',
            'range' => 'global',
            'section' => 'global',
            'description' => _('Gibt an, welche Ebenen der Studienbereichszuordnung geschlossen bleiben sollen')
        ));
    }
    
    function down() {
        Config::get()->delete('COURSE_SEM_TREE_DISPLAY');
        Config::get()->delete('COURSE_SEM_TREE_CLOSED_LEVELS');
    }

}
