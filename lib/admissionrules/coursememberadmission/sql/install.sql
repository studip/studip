CREATE TABLE IF NOT EXISTS `coursememberadmissions` (
  `rule_id` varchar(32),
  `message` text NOT NULL,
  `start_time` int(11) NOT NULL DEFAULT 0,
  `end_time` int(11) NOT NULL DEFAULT 0,
  `course_id` varchar(32) NOT NULL DEFAULT '',
  `modus` tinyint(1) NOT NULL DEFAULT 0,
  `mkdate` int(11) NOT NULL DEFAULT 0,
  `chdate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM;
