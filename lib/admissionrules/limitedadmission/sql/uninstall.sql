--
-- Remove limited admissions.
--
DROP TABLE `limitedadmissions`;

--
-- Remove custom user defined limits.
DROP TABLE `userlimits`;

--
-- Delete courseset assignments.
--
DELETE FROM `courseset_rule` WHERE `type`='LimitedAdmission';