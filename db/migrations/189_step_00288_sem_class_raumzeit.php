<?php
/**
 * Migration for StEP00288
 *
 * @author  Timo Hartge <hartge@data-quest.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 *
 * @see https://develop.studip.de/trac/ticket/5797
 */
class Step00288SemClassRaumzeit extends Migration
{

    /**
     * short description of this migration
     */
    public function description()
    {
        return 'Registers new sem_class settings for display of raumzeit.';
    }

    /**
     * perform this migration
     */
    public function up()
    {        
        DBManager::get()->exec('ALTER TABLE `sem_classes` ADD `show_raumzeit` TINYINT( 4 ) NOT NULL DEFAULT "1" AFTER `title_autor_plural`');
                
        try { 
            $sem_class = new SemClass(99);
            $sem_class->set('show_raumzeit', '0');
            $sem_class->set('schedule', 'CoreSchedule');
            $modules = $sem_class->getModules();
            $modules['CoreSchedule'] = ['activated' => '1', 'sticky' => '0'];
            $sem_class->setModules($modules);
            $sem_class->store();
        } catch (Exception $e) { }
                
    }

    /**
     * revert this migration
     */
    public function down()
    {
        DBManager::get()->execute('ALTER TABLE `sem_classes` DROP `show_raumzeit`');
    }

}
