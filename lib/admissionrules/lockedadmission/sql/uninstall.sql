--
-- Remove locked admissions.
--
DROP TABLE `lockedadmissions`;

--
-- Delete courseset assignments.
--
DELETE FROM `courseset_rule` WHERE `type`='LockedAdmission';