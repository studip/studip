--
-- Remove participant restricted admissions.
--
DROP TABLE `participantrestrictedadmissions`;

--
-- Delete courseset assignments.
--
DELETE FROM `courseset_rule` WHERE `type`='ParticipantRestrictedAdmission';