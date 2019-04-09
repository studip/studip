<?php
require_once('lib/models/CourseWizardStepRegistry.php');

class Tic6653AddAdvancedCoursewizardstep extends Migration
{

    function description() {
        return 'advanced alternative to the BasicDataWizardStep';
    }

    function up()
    {
        if (!CourseWizardStepRegistry::findByClassName('AdvancedBasicDataWizardStep')) {
            CourseWizardStepRegistry::registerStep('Erweiterte Grunddaten', 'AdvancedBasicDataWizardStep', 1, false);
        }
    }

    function down()
    {
        CourseWizardStepRegistry::deleteBySQL('classname = :name', 
                [':name' => 'AdvancedBasicDataWizardStep']);
    }
}