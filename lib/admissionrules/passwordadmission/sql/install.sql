--
-- Main table for storing password admissions.
--
CREATE TABLE IF NOT EXISTS `passwordadmissions` (
  `rule_id` varchar(32),
  `message` text NOT NULL,
  `start_time` INT(11) NOT NULL DEFAULT 0,
  `end_time` INT(11) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT 0,
  `chdate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;