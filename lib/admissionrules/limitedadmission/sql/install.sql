--
-- Main table for storing limited admissions.
--
CREATE TABLE IF NOT EXISTS `limitedadmissions` (
  `rule_id` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `start_time` INT(11) NOT NULL DEFAULT 0,
  `end_time` INT(11) NOT NULL DEFAULT 0,
  `maxnumber` tinyint(11) NOT NULL DEFAULT 0,
  `mkdate` int(11) NOT NULL DEFAULT 0,
  `chdate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

CREATE TABLE IF NOT EXISTS `userlimits` (
  `rule_id` VARCHAR(32) NOT NULL ,
  `user_id` VARCHAR(32) NOT NULL ,
  `maxnumber` INT NOT NULL DEFAULT 0,
  `mkdate` INT NOT NULL 0,
  `chdate` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`, `user_id`)
) ENGINE = MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;