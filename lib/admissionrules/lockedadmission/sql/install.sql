--
-- Main table for storing locked admissions.
--
CREATE TABLE IF NOT EXISTS `lockedadmissions` (
  `rule_id` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT 0,
  `chdate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;