<?php
class Step00332MvvOverlappingCourses extends Migration
{
    public function description()
    {
        return 'New tables to check for overlapping courses (conflicts). ';
    }

    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `mvv_ovl_selections` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `selection_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `semester_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `base_version_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `comp_version_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `fachsems` varchar(100) NOT NULL DEFAULT '',
                `semtypes` varchar(100) NOT NULL DEFAULT '',
                `user_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `show_excluded` int(1) UNSIGNED NOT NULL DEFAULT '0',
                `mkdate` bigint(20) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `selection_id` (`selection_id`),
            KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
            
        DBManager::get()->exec($query);
        
        $query = "CREATE TABLE IF NOT EXISTS `mvv_ovl_conflicts` (
                `conflict_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `selection_id` int(11) NOT NULL,
                `base_abschnitt_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `base_modulteil_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `base_course_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `base_metadate_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `comp_abschnitt_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `comp_modulteil_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `comp_course_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `comp_metadate_id` varchar(32) COLLATE latin1_bin NOT NULL,
            PRIMARY KEY (`conflict_id`),
            KEY `selection_id` (`selection_id`)
          ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        
        DBManager::get()->exec($query);
        
        $query = "CREATE TABLE IF NOT EXISTS `mvv_ovl_excludes` (
                `selection_id` varchar(32) COLLATE latin1_bin NOT NULL,
                `course_id` varchar(32) COLLATE latin1_bin NOT NULL,
            PRIMARY KEY (`selection_id`,`course_id`),
            KEY `course_id` (`course_id`) USING BTREE
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        
        DBManager::get()->exec($query);
        
        // Add config (2nd select shows only versions inside a multiple course of study)
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                      'MVV_OVERLAPPING_SHOW_VERSIONS_INSIDE_MULTIPLE_STUDY_COURSES',
                      '0', 'boolean', 'global',
                      'global', 'Zeigt als zweite Auswahl bei Mehrfachstudiengängen nur Versionen der dazugehörigen Teilstudiengänge an.',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        
        DBManager::get()->exec($query);
        
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $query = "DROP TABLE `mvv_ovl_selections`";
        DBManager::get()->exec($query);
        $query = "DROP TABLE `mvv_ovl_conflicts`";
        DBManager::get()->exec($query);
        $query = "DROP TABLE `mvv_ovl_excludes`";
        DBManager::get()->exec($query);
        $query = "DELETE FROM `config`
                WHERE `field` = 'MVV_OVERLAPPING_SHOW_VERSIONS_INSIDE_MULTIPLE_STUDY_COURSES'";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }
}
