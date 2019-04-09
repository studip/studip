<?php

class Step00255ImportantSemnumber extends Migration {

    /**
     * short description of this migration
     */
    function description() {
        return 'sets a global setting to improve the overall presents of semnumbers';
    }

    /**
     * perform this migration
     */
    function up() {
        Config::get()->create('IMPORTANT_SEMNUMBER', [
            'value' => 0, 
            'is_default' => 0, 
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'global',
            'description' => _('Zeigt die Veranstaltungsnummer prominenter in der Suche und auf der Meine Veranstaltungen Seite an')
            ]);
    }

    /**
     * revert this migration
     */
    function down() {
        Config::get()->delete('IMPORTANT_SEMNUMBER');
    }

}
?>