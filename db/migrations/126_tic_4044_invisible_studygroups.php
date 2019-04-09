<?php
class Tic4044InvisibleStudygroups extends Migration {

    /**
     * short description of this migration
     */
    function description() {
        return 'Allow invisible studygroups via global setting';
    }

    /**
     * perform this migration
     */
    function up() {
        Config::get()->create('STUDYGROUPS_INVISIBLE_ALLOWED', [
            'value' => 0, 
            'is_default' => 0, 
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'studygroups',
            'description' => _('Ermöglicht unsichtbare Studiengruppen')
            ]);
    }

    /**
     * revert this migration
     */
    function down() {
        Config::get()->delete('STUDYGROUPS_INVISIBLE_ALLOWED');
    }

}