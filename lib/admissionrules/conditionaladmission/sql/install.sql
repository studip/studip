--
-- Main table for storing conditional admissions.
--
CREATE TABLE IF NOT EXISTS `conditionaladmissions` (
  `rule_id` varchar(32),
  `message` text NOT NULL,
  `start_time` INT(11) NOT NULL DEFAULT 0,
  `end_time` INT(11) NOT NULL DEFAULT 0,
  `mkdate` int(11) NOT NULL DEFAULT 0,
  `chdate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

--
-- Associations between StudipConditions and conditional admissions.
--
CREATE TABLE IF NOT EXISTS `admission_condition` (
  `rule_id` varchar(32) NOT NULL,
  `condition_id` varchar(32) NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`,`condition_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;
