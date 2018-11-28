--
-- Delete associated StudipConditions and their fields.
--
DELETE FROM `conditionfields`
WHERE `condition_id` IN (
    SELECT `condition_id` FROM `prefadmission_condition`
);

DELETE FROM `conditions`
WHERE `condition_id` IN (
    SELECT `condition_id` FROM `prefadmission_condition`
);

DROP TABLE `prefadmission_condition`;

--
-- Remove favored admissions.
--
DROP TABLE `prefadmissions`;

--
-- Delete courseset assignments.
--
DELETE FROM `courseset_rule` WHERE `type`='PreferentialAdmission';