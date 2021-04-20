<?php
class DatabaseChangesTic9218 extends Migration
{
    public function description()
    {
        return 'Adjusts timestamps, ids and boolean fields in database. See ticket #9218 for more info';
    }

    public function up()
    {
        $query = "ALTER TABLE `abschluss`
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `activities`
                  CHANGE COLUMN `context_id` `context_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admissionfactor`
                  CHANGE COLUMN `list_id` `list_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `owner_id` `owner_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admissionrules`
                  CHANGE COLUMN `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admissionrule_compat`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admissionrule_inst`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `institute_id` `institute_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admission_condition`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `filter_id` `filter_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `conditiongroup_id` `conditiongroup_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admission_conditiongroup`
                  CHANGE COLUMN `conditiongroup_id` `conditiongroup_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admission_seminar_user`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `mkdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `archiv`
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `start_time` `start_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `heimat_inst_id` `heimat_inst_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `archiv_file_id` `archiv_file_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `archiv_protected_file_id` `archiv_protected_file_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `archiv_user`
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `auth_extern`
                  CHANGE COLUMN `studip_user_id` `studip_user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `auth_user_md5`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `auto_insert_user`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `aux_lock_rules`
                  CHANGE COLUMN `lock_id` `lock_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `banner_ads`
                  CHANGE COLUMN `ad_id` `ad_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `startdate` `startdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `enddate` `enddate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `priority` `priority` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `views` `views` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `clicks` `clicks` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_comments`
                  CHANGE COLUMN `external_contact` `external_contact` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_events_queue`
                  CHANGE COLUMN `item_id` `item_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_external_contact`
                  CHANGE COLUMN `external_contact_id` `external_contact_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_follower`
                  CHANGE COLUMN `studip_user_id` `studip_user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `external_contact_id` `external_contact_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `left_follows_right` `left_follows_right` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_mentions`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `external_contact` `external_contact` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_tags`
                  CHANGE COLUMN `topic_id` `topic_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_threads`
                  CHANGE COLUMN `context_id` `context_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `external_contact` `external_contact` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `visible_in_stream` `visible_in_stream` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `commentable` `commentable` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        try {
            $query = "ALTER TABLE `blubber_threads_unfollow`
                  CHANGE COLUMN `thread_id` `thread_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL";
            DBManager::get()->exec($query);
        } catch (Exception $e) {
            $query = "ALTER TABLE `blubber_threads_followstates`
                  CHANGE COLUMN `thread_id` `thread_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL";
            DBManager::get()->exec($query);
        }

        $query = "ALTER TABLE `calendar_event`
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `event_id` `event_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `group_status` `group_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `calendar_user`
                  CHANGE COLUMN `owner_id` `owner_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `comments`
                  CHANGE COLUMN `comment_id` `comment_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `object_id` `object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `conditionaladmissions`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_time` `start_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `conditions_stopped` `conditions_stopped` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `config`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `config_values`
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `contact`
                  CHANGE COLUMN `owner_id` `owner_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `content_terms_of_use_entries`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `is_default` `is_default` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `coursememberadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `start_time` `start_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `course_id` `course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `coursesets`
                  CHANGE COLUMN `set_id` `set_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `algorithm_run` `algorithm_run` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `private` `private` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `courseset_factorlist`
                  CHANGE COLUMN `set_id` `set_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `factorlist_id` `factorlist_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `courseset_institute`
                  CHANGE COLUMN `set_id` `set_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `institute_id` `institute_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `courseset_rule`
                  CHANGE COLUMN `set_id` `set_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `coursewizardsteps`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `enabled` `enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `cronjobs_schedules`
                  CHANGE COLUMN `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `cronjobs_tasks`
                  CHANGE COLUMN `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `datafields`
                  CHANGE COLUMN `datafield_id` `datafield_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `institut_id` `institut_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `is_required` `is_required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `is_userfilter` `is_userfilter` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `datafields_entries`
                  CHANGE COLUMN `datafield_id` `datafield_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `sec_range_id` `sec_range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `deputies`
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `edit_about` `edit_about` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_assignments`
                  CHANGE COLUMN `start` `start` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `end` `end` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `active` `active` TINYINT(1) UNSIGNED NOT NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_assignment_attempts`
                  CHANGE COLUMN `start` `start` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `end` `end` INT(11) UNSIGNED NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_assignment_ranges`
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_responses`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_tasks`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_tests`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_test_tasks`
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval`
                  CHANGE COLUMN `eval_id` `eval_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `startdate` `startdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `stopdate` `stopdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `timespan` `timespan` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `anonymous` `anonymous` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `shared` `shared` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `evalanswer`
                  CHANGE COLUMN `evalanswer_id` `evalanswer_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `rows` `rows` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `counter` `counter` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `residual` `residual` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `evalanswer_user`
                  CHANGE COLUMN `evalanswer_id` `evalanswer_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `evaldate` `evaldate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `evalgroup`
                  CHANGE COLUMN `evalgroup_id` `evalgroup_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mandatory` `mandatory` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `template_id` `template_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `evalquestion`
                  CHANGE COLUMN `evalquestion_id` `evalquestion_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `multiplechoice` `multiplechoice` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_group_template`
                  CHANGE COLUMN `evalgroup_id` `evalgroup_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_range`
                  CHANGE COLUMN `eval_id` `eval_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_templates`
                  CHANGE COLUMN `template_id` `template_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `institution_id` `institution_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `show_questions` `show_questions` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `show_total_stats` `show_total_stats` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `show_graphics` `show_graphics` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `show_questionblock_headline` `show_questionblock_headline` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `show_group_headline` `show_group_headline` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_templates_eval`
                  CHANGE COLUMN `eval_id` `eval_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `template_id` `template_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_templates_user`
                  CHANGE COLUMN `eval_id` `eval_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `template_id` `template_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_user`
                  CHANGE COLUMN `eval_id` `eval_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `event_data`
                  CHANGE COLUMN `event_id` `event_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `start` `start` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end` `end` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `ts` `ts` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `expire` `expire` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `importdate` `importdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `extern_config`
                  CHANGE COLUMN `config_id` `config_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `is_standard` `is_standard` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `ex_termine`
                  CHANGE COLUMN `termin_id` `termin_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `autor_id` `autor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `date` `date` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `topic_id` `topic_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `metadate_id` `metadate_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `resource_id` `resource_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `fach`
                  CHANGE COLUMN `fach_id` `fach_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `feedback`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `course_id` `course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `results_visible` `results_visible` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `commentable` `commentable` TINYINT(1) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `feedback_entries`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `files`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `files_search_attributes`
                  CHANGE COLUMN `file_ref_mkdate` `file_ref_mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `file_ref_chdate` `file_ref_chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `semester_start` `semester_start` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `semester_end` `semester_end` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `files_search_index`
                  CHANGE COLUMN `file_ref_id` `file_ref_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `file_refs`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `file_id` `file_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `folder_id` `folder_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `content_terms_of_use_id` `content_terms_of_use_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `folders`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `parent_id` `parent_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_abo_users`
                  CHANGE COLUMN `topic_id` `topic_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_categories`
                  CHANGE COLUMN `category_id` `category_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_categories_entries`
                  CHANGE COLUMN `category_id` `category_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `topic_id` `topic_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_entries`
                  CHANGE COLUMN `topic_id` `topic_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `latest_chdate` `latest_chdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `closed` `closed` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `sticky` `sticky` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_entries_issues`
                  CHANGE COLUMN `topic_id` `topic_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `issue_id` `issue_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_favorites`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `topic_id` `topic_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_likes`
                  CHANGE COLUMN `topic_id` `topic_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_visits`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `visitdate` `visitdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `last_visitdate` `last_visitdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `globalsearch_buzzwords`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `global_resource_locks`
                  CHANGE COLUMN `lock_id` `lock_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `begin` `begin` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end` `end` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `grading_definitions`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `grading_instances`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_content`
                  CHANGE COLUMN `global_content_id` `global_content_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `custom` `custom` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tours`
                  CHANGE COLUMN `global_tour_id` `global_tour_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tour_audiences`
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tour_settings`
                  CHANGE COLUMN `tour_id` `tour_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `active` `active` TINYINT(1) UNSIGNED NOT NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tour_steps`
                  CHANGE COLUMN `interactive` `interactive` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tour_user`
                  CHANGE COLUMN `tour_id` `tour_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `completed` `completed` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `i18n`
                  CHANGE COLUMN `object_id` `object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `Institute`
                  CHANGE COLUMN `Institut_id` `Institut_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `fakultaets_id` `fakultaets_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `srienabled` `srienabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `institute_plan_columns`
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `kategorien`
                  CHANGE COLUMN `kategorie_id` `kategorie_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `limitedadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_time` `start_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lockedadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lock_rules`
                  CHANGE COLUMN `lock_id` `lock_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `loginbackgrounds`
                  CHANGE COLUMN `mobile` `mobile` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `desktop` `desktop` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `in_release` `in_release` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `log_actions`
                  CHANGE COLUMN `action_id` `action_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `expires` `expires` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `log_events`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `action_id` `action_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `affected_range_id` `affected_range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `coaffected_range_id` `coaffected_range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lti_data`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lti_grade`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lti_tool`
                  CHANGE COLUMN `allow_custom_url` `allow_custom_url` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `deep_linking` `deep_linking` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `send_lis_person` `send_lis_person` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mail_queue_entries`
                  CHANGE COLUMN `mail_queue_id` `mail_queue_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `message_id` `message_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `tries` `tries` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `last_try` `last_try` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `media_cache`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `message`
                  CHANGE COLUMN `message_id` `message_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `autor_id` `autor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `show_adressees` `show_adressees` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `message_tags`
                  CHANGE COLUMN `message_id` `message_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `message_user`
                  CHANGE COLUMN `readed` `readed` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `deleted` `deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `answered` `answered` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_abschl_kategorie`
                  CHANGE COLUMN `kategorie_id` `kategorie_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_abschl_zuord`
                  CHANGE COLUMN `abschluss_id` `abschluss_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `kategorie_id` `kategorie_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_aufbaustudiengang`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_contacts`
                  CHANGE COLUMN `contact_id` `contact_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_contacts_ranges`
                  CHANGE COLUMN `contact_range_id` `contact_range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `contact_id` `contact_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_extern_contacts`
                  CHANGE COLUMN `extern_contact_id` `extern_contact_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_fach_inst`
                  CHANGE COLUMN `fach_id` `fach_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `institut_id` `institut_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_files`
                  CHANGE COLUMN `mvvfile_id` `mvvfile_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `extern_visible` `extern_visible` TINYINT(1) UNSIGNED NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_files_filerefs`
                  CHANGE COLUMN `mvvfile_id` `mvvfile_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `fileref_id` `fileref_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_files_ranges`
                  CHANGE COLUMN `mvvfile_id` `mvvfile_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_lvgruppe`
                  CHANGE COLUMN `lvgruppe_id` `lvgruppe_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_lvgruppe_modulteil`
                  CHANGE COLUMN `lvgruppe_id` `lvgruppe_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modulteil_id` `modulteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `fn_id` `fn_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_lvgruppe_seminar`
                  CHANGE COLUMN `lvgruppe_id` `lvgruppe_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modul`
                  CHANGE COLUMN `modul_id` `modul_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `beschlussdatum` `beschlussdatum` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil`
                  CHANGE COLUMN `modulteil_id` `modulteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modul_id` `modul_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `ausgleichbar` `ausgleichbar` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `pflicht` `pflicht` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil_deskriptor`
                  CHANGE COLUMN `deskriptor_id` `deskriptor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modulteil_id` `modulteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil_language`
                  CHANGE COLUMN `modulteil_id` `modulteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil_stgteilabschnitt`
                  CHANGE COLUMN `modulteil_id` `modulteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `abschnitt_id` `abschnitt_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modul_deskriptor`
                  CHANGE COLUMN `deskriptor_id` `deskriptor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modul_id` `modul_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modul_inst`
                  CHANGE COLUMN `modul_id` `modul_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `institut_id` `institut_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modul_language`
                  CHANGE COLUMN `modul_id` `modul_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_ovl_conflicts`
                  CHANGE COLUMN `base_abschnitt_id` `base_abschnitt_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `base_modulteil_id` `base_modulteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `base_course_id` `base_course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `base_metadate_id` `base_metadate_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_abschnitt_id` `comp_abschnitt_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_modulteil_id` `comp_modulteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_course_id` `comp_course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_metadate_id` `comp_metadate_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_ovl_excludes`
                  CHANGE COLUMN `selection_id` `selection_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `course_id` `course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_ovl_selections`
                  CHANGE COLUMN `selection_id` `selection_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `semester_id` `semester_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `base_version_id` `base_version_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_version_id` `comp_version_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `show_excluded` `show_excluded` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteil`
                  CHANGE COLUMN `stgteil_id` `stgteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `fach_id` `fach_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteilabschnitt`
                  CHANGE COLUMN `abschnitt_id` `abschnitt_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `version_id` `version_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteilabschnitt_modul`
                  CHANGE COLUMN `abschnitt_modul_id` `abschnitt_modul_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `abschnitt_id` `abschnitt_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modul_id` `modul_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteilversion`
                  CHANGE COLUMN `version_id` `version_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `stgteil_id` `stgteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_sem` `start_sem` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `end_sem` `end_sem` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `beschlussdatum` `beschlussdatum` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteil_bez`
                  CHANGE COLUMN `stgteil_bez_id` `stgteil_bez_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stg_stgteil`
                  CHANGE COLUMN `studiengang_id` `studiengang_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `stgteil_id` `stgteil_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `stgteil_bez_id` `stgteil_bez_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_studiengang`
                  CHANGE COLUMN `studiengang_id` `studiengang_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `abschluss_id` `abschluss_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `institut_id` `institut_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `start` `start` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `end` `end` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `beschlussdatum` `beschlussdatum` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_studycourse_language`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_studycourse_type`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `news`
                  CHANGE COLUMN `news_id` `news_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `date` `date` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `expire` `expire` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `allow_comments` `allow_comments` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate_uid` `chdate_uid` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `news_range`
                  CHANGE COLUMN `news_id` `news_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `object_contentmodules`
                  CHANGE COLUMN `object_id` `object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `object_user_visits`
                  CHANGE COLUMN `visitdate` `visitdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `last_visitdate` `last_visitdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `object_views`
                  CHANGE COLUMN `object_id` `object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `views` `views` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `opengraphdata`
                  CHANGE COLUMN `is_opengraph` `is_opengraph` TINYINT(1) UNSIGNED NULL,
                  CHANGE COLUMN `last_update` `last_update` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `participantrestrictedadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `distribution_time` `distribution_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `passwordadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_time` `start_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `personal_notifications_user`
                  CHANGE COLUMN `seen` `seen` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `plugins_activated`
                  CHANGE COLUMN `state` `state` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `plugins_default_activations`
                  CHANGE COLUMN `institutid` `institutid` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `prefadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `favor_semester` `favor_semester` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `prefadmission_condition`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `condition_id` `condition_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `priorities`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `set_id` `set_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaires`
                  CHANGE COLUMN `questionnaire_id` `questionnaire_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `startdate` `startdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `stopdate` `stopdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `anonymous` `anonymous` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `editanswers` `editanswers` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `copyable` `copyable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaire_anonymous_answers`
                  CHANGE COLUMN `anonymous_answer_id` `anonymous_answer_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `questionnaire_id` `questionnaire_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaire_answers`
                  CHANGE COLUMN `answer_id` `answer_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `question_id` `question_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaire_assignments`
                  CHANGE COLUMN `assignment_id` `assignment_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `questionnaire_id` `questionnaire_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaire_questions`
                  CHANGE COLUMN `question_id` `question_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `questionnaire_id` `questionnaire_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `range_tree`
                  CHANGE COLUMN `item_id` `item_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `studip_object_id` `studip_object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resources`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `category_id` `category_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_bookings`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `resource_id` `resource_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `begin` `begin` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end` `end` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `repeat_end` `repeat_end` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `booking_user_id` `booking_user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_booking_intervals`
                  CHANGE COLUMN `interval_id` `interval_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `booking_id` `booking_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `begin` `begin` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end` `end` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_categories`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_category_properties`
                  CHANGE COLUMN `category_id` `category_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `property_id` `property_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_permissions`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `resource_id` `resource_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_properties`
                  CHANGE COLUMN `resource_id` `resource_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `property_id` `property_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_property_definitions`
                  CHANGE COLUMN `property_id` `property_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_property_groups`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_requests`
                  CHANGE COLUMN `id` `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `course_id` `course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `termin_id` `termin_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `metadate_id` `metadate_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `last_modified_by` `last_modified_by` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `resource_id` `resource_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `category_id` `category_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_request_appointments`
                  CHANGE COLUMN `request_id` `request_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `appointment_id` `appointment_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_request_properties`
                  CHANGE COLUMN `request_id` `request_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `property_id` `property_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_temporary_permissions`
                  CHANGE COLUMN `resource_id` `resource_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `schedule`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `schedule_seminare`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `metadate_id` `metadate_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `scm`
                  CHANGE COLUMN `scm_id` `scm_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `semester_data`
                  CHANGE COLUMN `semester_id` `semester_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `beginn` `beginn` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `ende` `ende` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `vorles_beginn` `vorles_beginn` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `vorles_ende` `vorles_ende` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `semester_holiday`
                  CHANGE COLUMN `holiday_id` `holiday_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `semester_id` `semester_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `beginn` `beginn` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `ende` `ende` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminare`
                  CHANGE COLUMN `Seminar_id` `Seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
                  CHANGE COLUMN `Institut_id` `Institut_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
                  CHANGE COLUMN `status` `status` INT(11) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `start_time` `start_time` INT(11) UNSIGNED NULL DEFAULT '0',
                  CHANGE COLUMN `duration_time` `duration_time` INT(11) NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `parent_course` `parent_course` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_courseset`
                  CHANGE COLUMN `set_id` `set_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_cycle_dates`
                  CHANGE COLUMN `metadate_id` `metadate_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_inst`
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `institut_id` `institut_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_sem_tree`
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `sem_tree_id` `sem_tree_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_user`
                  CHANGE COLUMN `Seminar_id` `Seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `bind_calendar` `bind_calendar` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_userdomains`
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `userdomain_id` `userdomain_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sem_classes`
                  CHANGE COLUMN `only_inst_user` `only_inst_user` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `bereiche` `bereiche` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `module` `module` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `show_browse` `show_browse` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `write_access_nobody` `write_access_nobody` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `topic_create_autor` `topic_create_autor` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `course_creation_forbidden` `course_creation_forbidden` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `studygroup_mode` `studygroup_mode` TINYINT(1) UNSIGNED NOT NULL,
                  CHANGE COLUMN `show_raumzeit` `show_raumzeit` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `is_group` `is_group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sem_tree`
                  CHANGE COLUMN `sem_tree_id` `sem_tree_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `studip_object_id` `studip_object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sem_types`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `separable_rooms`
                  CHANGE COLUMN `building_id` `building_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `separable_room_parts`
                  CHANGE COLUMN `room_id` `room_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `smiley`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `statusgruppen`
                  CHANGE COLUMN `statusgruppe_id` `statusgruppe_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `selfassign` `selfassign` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `selfassign_start` `selfassign_start` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `selfassign_end` `selfassign_end` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `statusgruppe_user`
                  CHANGE COLUMN `statusgruppe_id` `statusgruppe_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `inherit` `inherit` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `studygroup_invitations`
                  CHANGE COLUMN `sem_id` `sem_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `termine`
                  CHANGE COLUMN `termin_id` `termin_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `autor_id` `autor_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `date` `date` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `topic_id` `topic_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `metadate_id` `metadate_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `termin_related_groups`
                  CHANGE COLUMN `termin_id` `termin_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `statusgruppe_id` `statusgruppe_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `termin_related_persons`
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `themen`
                  CHANGE COLUMN `issue_id` `issue_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `seminar_id` `seminar_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `author_id` `author_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `themen_termine`
                  CHANGE COLUMN `issue_id` `issue_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `termin_id` `termin_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `timedadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_time` `start_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `userdomains`
                  CHANGE COLUMN `userdomain_id` `userdomain_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `restricted_access` `restricted_access` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `userfilter`
                  CHANGE COLUMN `filter_id` `filter_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `userfilter_fields`
                  CHANGE COLUMN `field_id` `field_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `filter_id` `filter_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `userlimits`
                  CHANGE COLUMN `rule_id` `rule_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_factorlist`
                  CHANGE COLUMN `list_id` `list_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_info`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `smsforward_copy` `smsforward_copy` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `smsforward_rec` `smsforward_rec` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `email_forward` `email_forward` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_inst`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
                  CHANGE COLUMN `Institut_id` `Institut_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
                  CHANGE COLUMN `externdefault` `externdefault` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_online`
                  CHANGE COLUMN `last_lifesign` `last_lifesign` INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_studiengang`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `fach_id` `fach_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `version_id` `version_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_token`
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_userdomains`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `userdomain_id` `userdomain_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_visibility`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `online` `online` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `search` `search` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `email` `email` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_visibility_settings`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `webservice_access_rules`
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `widget_user`
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `wiki`
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `wiki_locks`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `chdate` `chdate` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `wiki_page_config`
                  CHANGE COLUMN `read_restricted` `read_restricted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `edit_restricted` `edit_restricted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL,
                  ADD COLUMN `chdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `abschluss`
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(20) NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `activities`
                  CHANGE COLUMN `context_id` `context_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admissionfactor`
                  CHANGE COLUMN `list_id` `list_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `owner_id` `owner_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admissionrules`
                  CHANGE COLUMN `active` `active` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admissionrule_compat`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admissionrule_inst`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `institute_id` `institute_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admission_condition`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `filter_id` `filter_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `conditiongroup_id` `conditiongroup_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admission_conditiongroup`
                  CHANGE COLUMN `conditiongroup_id` `conditiongroup_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `admission_seminar_user`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `archiv`
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `start_time` `start_time` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `heimat_inst_id` `heimat_inst_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `archiv_file_id` `archiv_file_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `archiv_protected_file_id` `archiv_protected_file_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `archiv_user`
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `auth_extern`
                  CHANGE COLUMN `studip_user_id` `studip_user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `auth_user_md5`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `auto_insert_user`
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `aux_lock_rules`
                  CHANGE COLUMN `lock_id` `lock_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `banner_ads`
                  CHANGE COLUMN `ad_id` `ad_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `startdate` `startdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `enddate` `enddate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `priority` `priority` INT(4) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `views` `views` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `clicks` `clicks` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_comments`
                  CHANGE COLUMN `external_contact` `external_contact` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_events_queue`
                  CHANGE COLUMN `item_id` `item_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_external_contact`
                  CHANGE COLUMN `external_contact_id` `external_contact_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_follower`
                  CHANGE COLUMN `studip_user_id` `studip_user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `external_contact_id` `external_contact_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `left_follows_right` `left_follows_right` TINYINT(1) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_mentions`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `external_contact` `external_contact` TINYINT(4) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_tags`
                  CHANGE COLUMN `topic_id` `topic_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_threads`
                  CHANGE COLUMN `context_id` `context_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `external_contact` `external_contact` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `visible_in_stream` `visible_in_stream` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `commentable` `commentable` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NULL";
        DBManager::get()->exec($query);

        try {
            $query = "ALTER TABLE `blubber_threads_unfollow`
                  CHANGE COLUMN `thread_id` `thread_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NULL";
            DBManager::get()->exec($query);
        } catch (Exception $e) {
            $query = "ALTER TABLE `blubber_threads_followstates`
                  CHANGE COLUMN `thread_id` `thread_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NULL";
            DBManager::get()->exec($query);
        }

        $query = "ALTER TABLE `calendar_event`
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `event_id` `event_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `group_status` `group_status` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `calendar_user`
                  CHANGE COLUMN `owner_id` `owner_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `comments`
                  CHANGE COLUMN `comment_id` `comment_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `object_id` `object_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `conditionaladmissions`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_time` `start_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `conditions_stopped` `conditions_stopped` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `config`
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `config_values`
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `contact`
                  CHANGE COLUMN `owner_id` `owner_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  DROP COLUMN `mkdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `content_terms_of_use_entries`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `is_default` `is_default` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `coursememberadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `start_time` `start_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `course_id` `course_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `coursesets`
                  CHANGE COLUMN `set_id` `set_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `algorithm_run` `algorithm_run` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `private` `private` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `courseset_factorlist`
                  CHANGE COLUMN `set_id` `set_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `factorlist_id` `factorlist_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `courseset_institute`
                  CHANGE COLUMN `set_id` `set_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `institute_id` `institute_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `courseset_rule`
                  CHANGE COLUMN `set_id` `set_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `coursewizardsteps`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `enabled` `enabled` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `cronjobs_schedules`
                  CHANGE COLUMN `active` `active` TINYINT(1) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `cronjobs_tasks`
                  CHANGE COLUMN `active` `active` TINYINT(1) NOT NULL DEFAULT '0',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `datafields`
                  CHANGE COLUMN `datafield_id` `datafield_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `institut_id` `institut_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `is_required` `is_required` TINYINT(4) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `is_userfilter` `is_userfilter` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `datafields_entries`
                  CHANGE COLUMN `datafield_id` `datafield_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `sec_range_id` `sec_range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `deputies`
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `edit_about` `edit_about` TINYINT(1) NOT NULL DEFAULT '0',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_assignments`
                  CHANGE COLUMN `start` `start` INT(11) NULL,
                  CHANGE COLUMN `end` `end` INT(11) NULL,
                  CHANGE COLUMN `active` `active` TINYINT(1) NOT NULL,
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_assignment_attempts`
                  CHANGE COLUMN `start` `start` INT(11) NULL,
                  CHANGE COLUMN `end` `end` INT(11) NULL,
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_assignment_ranges`
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_responses`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_tasks`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_tests`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `etask_test_tasks`
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval`
                  CHANGE COLUMN `eval_id` `eval_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `startdate` `startdate` INT(20) NULL,
                  CHANGE COLUMN `stopdate` `stopdate` INT(20) NULL,
                  CHANGE COLUMN `timespan` `timespan` INT(20) NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `anonymous` `anonymous` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `visible` `visible` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `shared` `shared` TINYINT(1) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `evalanswer`
                  CHANGE COLUMN `evalanswer_id` `evalanswer_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `rows` `rows` TINYINT(4) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `counter` `counter` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `residual` `residual` TINYINT(1) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `evalanswer_user`
                  CHANGE COLUMN `evalanswer_id` `evalanswer_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `evaldate` `evaldate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `evalgroup`
                  CHANGE COLUMN `evalgroup_id` `evalgroup_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mandatory` `mandatory` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `template_id` `template_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `evalquestion`
                  CHANGE COLUMN `evalquestion_id` `evalquestion_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `multiplechoice` `multiplechoice` TINYINT(1) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_group_template`
                  CHANGE COLUMN `evalgroup_id` `evalgroup_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_range`
                  CHANGE COLUMN `eval_id` `eval_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_templates`
                  CHANGE COLUMN `template_id` `template_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `institution_id` `institution_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `show_questions` `show_questions` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `show_total_stats` `show_total_stats` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `show_graphics` `show_graphics` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `show_questionblock_headline` `show_questionblock_headline` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `show_group_headline` `show_group_headline` TINYINT(1) NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_templates_eval`
                  CHANGE COLUMN `eval_id` `eval_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `template_id` `template_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_templates_user`
                  CHANGE COLUMN `eval_id` `eval_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `template_id` `template_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `eval_user`
                  CHANGE COLUMN `eval_id` `eval_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `event_data`
                  CHANGE COLUMN `event_id` `event_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `start` `start` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end` `end` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `ts` `ts` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `expire` `expire` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `importdate` `importdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `extern_config`
                  CHANGE COLUMN `config_id` `config_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `is_standard` `is_standard` INT(4) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `ex_termine`
                  CHANGE COLUMN `termin_id` `termin_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `autor_id` `autor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `date` `date` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `topic_id` `topic_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `metadate_id` `metadate_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `resource_id` `resource_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `fach`
                  CHANGE COLUMN `fach_id` `fach_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `feedback`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `course_id` `course_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `results_visible` `results_visible` TINYINT(4) UNSIGNED NOT NULL,
                  CHANGE COLUMN `commentable` `commentable` TINYINT(4) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `feedback_entries`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `files`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `files_search_attributes`
                  CHANGE COLUMN `file_ref_mkdate` `file_ref_mkdate` INT(10) UNSIGNED NOT NULL,
                  CHANGE COLUMN `file_ref_chdate` `file_ref_chdate` INT(10) UNSIGNED NOT NULL,
                  CHANGE COLUMN `semester_start` `semester_start` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `semester_end` `semester_end` INT(20) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `files_search_index`
                  CHANGE COLUMN `file_ref_id` `file_ref_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `file_refs`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `file_id` `file_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `folder_id` `folder_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `content_terms_of_use_id` `content_terms_of_use_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `folders`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `parent_id` `parent_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_abo_users`
                  CHANGE COLUMN `topic_id` `topic_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_categories`
                  CHANGE COLUMN `category_id` `category_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_categories_entries`
                  CHANGE COLUMN `category_id` `category_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `topic_id` `topic_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_entries`
                  CHANGE COLUMN `topic_id` `topic_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL,
                  CHANGE COLUMN `latest_chdate` `latest_chdate` INT(11) NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL,
                  CHANGE COLUMN `closed` `closed` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `sticky` `sticky` INT(1) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_entries_issues`
                  CHANGE COLUMN `topic_id` `topic_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `issue_id` `issue_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_favorites`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `topic_id` `topic_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_likes`
                  CHANGE COLUMN `topic_id` `topic_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `forum_visits`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `visitdate` `visitdate` INT(11) NOT NULL,
                  CHANGE COLUMN `last_visitdate` `last_visitdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `globalsearch_buzzwords`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `global_resource_locks`
                  CHANGE COLUMN `lock_id` `lock_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `begin` `begin` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end` `end` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `grading_definitions`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `grading_instances`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_content`
                  CHANGE COLUMN `global_content_id` `global_content_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `custom` `custom` TINYINT(4) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `visible` `visible` TINYINT(4) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tours`
                  CHANGE COLUMN `global_tour_id` `global_tour_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tour_audiences`
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tour_settings`
                  CHANGE COLUMN `tour_id` `tour_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `active` `active` TINYINT(4) NOT NULL,
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tour_steps`
                  CHANGE COLUMN `interactive` `interactive` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `help_tour_user`
                  CHANGE COLUMN `tour_id` `tour_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `completed` `completed` TINYINT(4) NOT NULL DEFAULT '0',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `i18n`
                  CHANGE COLUMN `object_id` `object_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `Institute`
                  CHANGE COLUMN `Institut_id` `Institut_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `fakultaets_id` `fakultaets_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `srienabled` `srienabled` TINYINT(4) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `institute_plan_columns`
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `kategorien`
                  CHANGE COLUMN `kategorie_id` `kategorie_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `limitedadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_time` `start_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lockedadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lock_rules`
                  CHANGE COLUMN `lock_id` `lock_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `loginbackgrounds`
                  CHANGE COLUMN `mobile` `mobile` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `desktop` `desktop` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `in_release` `in_release` TINYINT(1) NOT NULL DEFAULT '0',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `log_actions`
                  CHANGE COLUMN `action_id` `action_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `active` `active` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `expires` `expires` INT(20) NOT NULL DEFAULT '0',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `log_events`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `action_id` `action_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `affected_range_id` `affected_range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `coaffected_range_id` `coaffected_range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lti_data`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lti_grade`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `lti_tool`
                  CHANGE COLUMN `allow_custom_url` `allow_custom_url` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `deep_linking` `deep_linking` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `send_lis_person` `send_lis_person` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mail_queue_entries`
                  CHANGE COLUMN `mail_queue_id` `mail_queue_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `message_id` `message_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `tries` `tries` INT(11) NOT NULL,
                  CHANGE COLUMN `last_try` `last_try` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `media_cache`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `message`
                  CHANGE COLUMN `message_id` `message_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `autor_id` `autor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `show_adressees` `show_adressees` TINYINT(4) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `message_tags`
                  CHANGE COLUMN `message_id` `message_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `message_user`
                  CHANGE COLUMN `readed` `readed` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `deleted` `deleted` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `answered` `answered` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_abschl_kategorie`
                  CHANGE COLUMN `kategorie_id` `kategorie_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_abschl_zuord`
                  CHANGE COLUMN `abschluss_id` `abschluss_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `kategorie_id` `kategorie_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_aufbaustudiengang`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_contacts`
                  CHANGE COLUMN `contact_id` `contact_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_contacts_ranges`
                  CHANGE COLUMN `contact_range_id` `contact_range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `contact_id` `contact_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_extern_contacts`
                  CHANGE COLUMN `extern_contact_id` `extern_contact_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_fach_inst`
                  CHANGE COLUMN `fach_id` `fach_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `institut_id` `institut_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_files`
                  CHANGE COLUMN `mvvfile_id` `mvvfile_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `extern_visible` `extern_visible` TINYINT(1) NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_files_filerefs`
                  CHANGE COLUMN `mvvfile_id` `mvvfile_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `fileref_id` `fileref_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_files_ranges`
                  CHANGE COLUMN `mvvfile_id` `mvvfile_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_lvgruppe`
                  CHANGE COLUMN `lvgruppe_id` `lvgruppe_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_lvgruppe_modulteil`
                  CHANGE COLUMN `lvgruppe_id` `lvgruppe_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modulteil_id` `modulteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `fn_id` `fn_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_lvgruppe_seminar`
                  CHANGE COLUMN `lvgruppe_id` `lvgruppe_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modul`
                  CHANGE COLUMN `modul_id` `modul_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `beschlussdatum` `beschlussdatum` INT(11) NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil`
                  CHANGE COLUMN `modulteil_id` `modulteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modul_id` `modul_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `ausgleichbar` `ausgleichbar` INT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `pflicht` `pflicht` INT(2) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil_deskriptor`
                  CHANGE COLUMN `deskriptor_id` `deskriptor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modulteil_id` `modulteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil_language`
                  CHANGE COLUMN `modulteil_id` `modulteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modulteil_stgteilabschnitt`
                  CHANGE COLUMN `modulteil_id` `modulteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `abschnitt_id` `abschnitt_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modul_deskriptor`
                  CHANGE COLUMN `deskriptor_id` `deskriptor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modul_id` `modul_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modul_inst`
                  CHANGE COLUMN `modul_id` `modul_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `institut_id` `institut_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_modul_language`
                  CHANGE COLUMN `modul_id` `modul_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_ovl_conflicts`
                  CHANGE COLUMN `base_abschnitt_id` `base_abschnitt_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `base_modulteil_id` `base_modulteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `base_course_id` `base_course_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `base_metadate_id` `base_metadate_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_abschnitt_id` `comp_abschnitt_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_modulteil_id` `comp_modulteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_course_id` `comp_course_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_metadate_id` `comp_metadate_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_ovl_excludes`
                  CHANGE COLUMN `selection_id` `selection_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `course_id` `course_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_ovl_selections`
                  CHANGE COLUMN `selection_id` `selection_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `semester_id` `semester_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `base_version_id` `base_version_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `comp_version_id` `comp_version_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `show_excluded` `show_excluded` INT(1) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteil`
                  CHANGE COLUMN `stgteil_id` `stgteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `fach_id` `fach_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteilabschnitt`
                  CHANGE COLUMN `abschnitt_id` `abschnitt_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `version_id` `version_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteilabschnitt_modul`
                  CHANGE COLUMN `abschnitt_modul_id` `abschnitt_modul_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `abschnitt_id` `abschnitt_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `modul_id` `modul_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteilversion`
                  CHANGE COLUMN `version_id` `version_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `stgteil_id` `stgteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_sem` `start_sem` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `end_sem` `end_sem` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `beschlussdatum` `beschlussdatum` INT(11) NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stgteil_bez`
                  CHANGE COLUMN `stgteil_bez_id` `stgteil_bez_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_stg_stgteil`
                  CHANGE COLUMN `studiengang_id` `studiengang_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `stgteil_id` `stgteil_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `stgteil_bez_id` `stgteil_bez_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_studiengang`
                  CHANGE COLUMN `studiengang_id` `studiengang_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `abschluss_id` `abschluss_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `institut_id` `institut_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `start` `start` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `end` `end` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `beschlussdatum` `beschlussdatum` INT(11) NULL,
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `editor_id` `editor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_studycourse_language`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_studycourse_type`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `news`
                  CHANGE COLUMN `news_id` `news_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `date` `date` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `expire` `expire` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `allow_comments` `allow_comments` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate_uid` `chdate_uid` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `news_range`
                  CHANGE COLUMN `news_id` `news_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `object_contentmodules`
                  CHANGE COLUMN `object_id` `object_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `object_user_visits`
                  CHANGE COLUMN `visitdate` `visitdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `last_visitdate` `last_visitdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `object_views`
                  CHANGE COLUMN `object_id` `object_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `views` `views` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `opengraphdata`
                  CHANGE COLUMN `is_opengraph` `is_opengraph` TINYINT(2) NULL,
                  CHANGE COLUMN `last_update` `last_update` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `participantrestrictedadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `distribution_time` `distribution_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `passwordadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_time` `start_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `personal_notifications_user`
                  CHANGE COLUMN `seen` `seen` TINYINT(1) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `plugins_activated`
                  CHANGE COLUMN `state` `state` TINYINT(1) NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `plugins_default_activations`
                  CHANGE COLUMN `institutid` `institutid` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `prefadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `favor_semester` `favor_semester` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `prefadmission_condition`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `condition_id` `condition_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `priorities`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `set_id` `set_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaires`
                  CHANGE COLUMN `questionnaire_id` `questionnaire_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `startdate` `startdate` BIGINT(20) NULL,
                  CHANGE COLUMN `stopdate` `stopdate` BIGINT(20) NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `anonymous` `anonymous` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `editanswers` `editanswers` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `copyable` `copyable` TINYINT(4) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaire_anonymous_answers`
                  CHANGE COLUMN `anonymous_answer_id` `anonymous_answer_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `questionnaire_id` `questionnaire_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaire_answers`
                  CHANGE COLUMN `answer_id` `answer_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `question_id` `question_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaire_assignments`
                  CHANGE COLUMN `assignment_id` `assignment_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `questionnaire_id` `questionnaire_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `questionnaire_questions`
                  CHANGE COLUMN `question_id` `question_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `questionnaire_id` `questionnaire_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `range_tree`
                  CHANGE COLUMN `item_id` `item_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `studip_object_id` `studip_object_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resources`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `category_id` `category_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_bookings`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `resource_id` `resource_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `begin` `begin` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end` `end` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `repeat_end` `repeat_end` INT(20) NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `booking_user_id` `booking_user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_booking_intervals`
                  CHANGE COLUMN `interval_id` `interval_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `booking_id` `booking_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `begin` `begin` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end` `end` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_categories`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_category_properties`
                  CHANGE COLUMN `category_id` `category_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `property_id` `property_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_permissions`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `resource_id` `resource_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_properties`
                  CHANGE COLUMN `resource_id` `resource_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `property_id` `property_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_property_definitions`
                  CHANGE COLUMN `property_id` `property_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_property_groups`
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_requests`
                  CHANGE COLUMN `id` `id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `course_id` `course_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `termin_id` `termin_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `metadate_id` `metadate_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `last_modified_by` `last_modified_by` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `resource_id` `resource_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `category_id` `category_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(20) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_request_appointments`
                  CHANGE COLUMN `request_id` `request_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `appointment_id` `appointment_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_request_properties`
                  CHANGE COLUMN `request_id` `request_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `property_id` `property_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(20) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_temporary_permissions`
                  CHANGE COLUMN `resource_id` `resource_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `schedule`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `schedule_seminare`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `metadate_id` `metadate_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(1) NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `scm`
                  CHANGE COLUMN `scm_id` `scm_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `semester_data`
                  CHANGE COLUMN `semester_id` `semester_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `beginn` `beginn` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `ende` `ende` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `vorles_beginn` `vorles_beginn` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `vorles_ende` `vorles_ende` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `semester_holiday`
                  CHANGE COLUMN `holiday_id` `holiday_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `semester_id` `semester_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `beginn` `beginn` INT(20) UNSIGNED NULL,
                  CHANGE COLUMN `ende` `ende` INT(20) UNSIGNED NOT NULL DEFAULT '0',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminare`
                  CHANGE COLUMN `Seminar_id` `Seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
                  CHANGE COLUMN `Institut_id` `Institut_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
                  CHANGE COLUMN `status` `status` TINYINT(4) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `start_time` `start_time` INT(20) NULL DEFAULT '0',
                  CHANGE COLUMN `duration_time` `duration_time` INT(20) NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `visible` `visible` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1',
                  CHANGE COLUMN `parent_course` `parent_course` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_courseset`
                  CHANGE COLUMN `set_id` `set_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_cycle_dates`
                  CHANGE COLUMN `metadate_id` `metadate_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_inst`
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `institut_id` `institut_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_sem_tree`
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `sem_tree_id` `sem_tree_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_user`
                  CHANGE COLUMN `Seminar_id` `Seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `bind_calendar` `bind_calendar` TINYINT(1) NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_userdomains`
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `userdomain_id` `userdomain_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sem_classes`
                  CHANGE COLUMN `only_inst_user` `only_inst_user` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `bereiche` `bereiche` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `module` `module` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `show_browse` `show_browse` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `write_access_nobody` `write_access_nobody` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `topic_create_autor` `topic_create_autor` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `visible` `visible` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `course_creation_forbidden` `course_creation_forbidden` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `studygroup_mode` `studygroup_mode` TINYINT(4) NOT NULL,
                  CHANGE COLUMN `show_raumzeit` `show_raumzeit` TINYINT(4) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `is_group` `is_group` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sem_tree`
                  CHANGE COLUMN `sem_tree_id` `sem_tree_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `parent_id` `parent_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `studip_object_id` `studip_object_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sem_types`
                  CHANGE COLUMN `mkdate` `mkdate` BIGINT(20) NOT NULL,
                  CHANGE COLUMN `chdate` `chdate` BIGINT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `separable_rooms`
                  CHANGE COLUMN `building_id` `building_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `separable_room_parts`
                  CHANGE COLUMN `room_id` `room_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `smiley`
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `statusgruppen`
                  CHANGE COLUMN `statusgruppe_id` `statusgruppe_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `selfassign` `selfassign` TINYINT(4) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `selfassign_start` `selfassign_start` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `selfassign_end` `selfassign_end` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `statusgruppe_user`
                  CHANGE COLUMN `statusgruppe_id` `statusgruppe_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `visible` `visible` TINYINT(4) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `inherit` `inherit` TINYINT(4) NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `studygroup_invitations`
                  CHANGE COLUMN `sem_id` `sem_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `termine`
                  CHANGE COLUMN `termin_id` `termin_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `autor_id` `autor_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `date` `date` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `topic_id` `topic_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `metadate_id` `metadate_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `termin_related_groups`
                  CHANGE COLUMN `termin_id` `termin_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `statusgruppe_id` `statusgruppe_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `termin_related_persons`
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `themen`
                  CHANGE COLUMN `issue_id` `issue_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `seminar_id` `seminar_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `author_id` `author_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(10) UNSIGNED NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `themen_termine`
                  CHANGE COLUMN `issue_id` `issue_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `termin_id` `termin_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `timedadmissions`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `start_time` `start_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `end_time` `end_time` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `userdomains`
                  CHANGE COLUMN `userdomain_id` `userdomain_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `restricted_access` `restricted_access` TINYINT(1) NOT NULL DEFAULT '1'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `userfilter`
                  CHANGE COLUMN `filter_id` `filter_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `userfilter_fields`
                  CHANGE COLUMN `field_id` `field_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `filter_id` `filter_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `userlimits`
                  CHANGE COLUMN `rule_id` `rule_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_factorlist`
                  CHANGE COLUMN `list_id` `list_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(11) NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_info`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `chdate` `chdate` INT(20) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `smsforward_copy` `smsforward_copy` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `smsforward_rec` `smsforward_rec` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `email_forward` `email_forward` TINYINT(4) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_inst`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
                  CHANGE COLUMN `Institut_id` `Institut_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
                  CHANGE COLUMN `externdefault` `externdefault` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
                  CHANGE COLUMN `visible` `visible` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_online`
                  CHANGE COLUMN `last_lifesign` `last_lifesign` INT(10) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_studiengang`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `fach_id` `fach_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `version_id` `version_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_token`
                  DROP COLUMN `mkdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_userdomains`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `userdomain_id` `userdomain_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_visibility`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  CHANGE COLUMN `online` `online` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `search` `search` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `email` `email` TINYINT(1) NOT NULL DEFAULT '1',
                  CHANGE COLUMN `mkdate` `mkdate` INT(20) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_visibility_settings`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `webservice_access_rules`
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `widget_user`
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `wiki`
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
                  CHANGE COLUMN `chdate` `chdate` INT(11) NULL,
                  DROP COLUMN `mkdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `wiki_locks`
                  CHANGE COLUMN `user_id` `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  CHANGE COLUMN `chdate` `chdate` INT(11) NOT NULL DEFAULT '0'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `wiki_page_config`
                  CHANGE COLUMN `read_restricted` `read_restricted` TINYINT(1) NOT NULL DEFAULT '0',
                  CHANGE COLUMN `edit_restricted` `edit_restricted` TINYINT(1) NOT NULL DEFAULT '0',
                  DROP COLUMN `mkdate`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);
    }
}
