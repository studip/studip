--
-- Main table for storing timed admissions.
--
CREATE TABLE IF NOT EXISTS `participantrestrictedadmissions` (
  `rule_id` varchar(32),
  `message` text NOT NULL,
  `distribution_time` int(11) NOT NULL DEFAULT 0,
  `mkdate` int(11) NOT NULL DEFAULT 0,
  `chdate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;
