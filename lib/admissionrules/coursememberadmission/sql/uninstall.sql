DROP TABLE `coursememberadmissions`;

--
-- Delete courseset assignments.
--
DELETE FROM `courseset_rule` WHERE `type`='CourseMemberAdmission';