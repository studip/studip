--
-- Remove timed admissions.
--
DROP TABLE `timedadmissions`;

--
-- Delete courseset assignments.
--
DELETE FROM `courseset_rule` WHERE `type`='TimedAdmission';