--
-- Main table for storing preferential admissions.
--
CREATE TABLE IF NOT EXISTS `prefadmissions` (
  `rule_id` VARCHAR(32),
  `favor_semester` TINYINT(1) NOT NULL DEFAULT 0,
  `mkdate` int(11) NOT NULL DEFAULT 0,
  `chdate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB;

--
-- Associations between StudipConditions and conditional admissions.
--
CREATE TABLE IF NOT EXISTS `prefadmission_condition` (
  `rule_id` VARCHAR(32) NOT NULL,
  `condition_id` VARCHAR(32) NOT NULL,
  `chance` INT(4) NOT NULL DEFAULT 1,
  `mkdate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`,`condition_id`)
) ENGINE=InnoDB;
