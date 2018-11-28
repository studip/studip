--
-- Remove conditional admissions.
--
DROP TABLE `conditionaladmissions`;

--
-- Delete associated StudipConditions and their fields.
--
DELETE FROM `conditionfields`
WHERE `condition_id` IN (
    SELECT `condition_id` FROM `admission_condition`
);

DELETE FROM `conditions`
WHERE `condition_id` IN (
    SELECT `condition_id` FROM `admission_condition`
);

DROP TABLE `admission_condition`;

--
-- Delete courseset assignments.
--
DELETE FROM `courseset_rule` WHERE `type`='ConditionalAdmission';