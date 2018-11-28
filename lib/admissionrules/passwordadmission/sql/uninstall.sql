--
-- Remove password admissions.
--
DROP TABLE `passwordadmissions`;

--
-- Delete courseset assignments.
--
DELETE FROM `courseset_rule` WHERE `type`='PasswordAdmission';