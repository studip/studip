--
-- Daten für Tabelle `config`
--

REPLACE INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`) VALUES
('RESOURCES_ENABLE', 'studip', '1', 1530292001, 1530292001, '');

--
-- Dumping data for table `resource_categories`
--

INSERT INTO `resource_categories` (`id`, `name`, `description`, `system`, `iconnr`, `class_name`, `mkdate`, `chdate`) VALUES('05278c70d89ae99404727408ef111963', 'Standort', '', 1, 0, 'Location', 0, 0);
INSERT INTO `resource_categories` (`id`, `name`, `description`, `system`, `iconnr`, `class_name`, `mkdate`, `chdate`) VALUES('3cbcc99c39476b8e2c8eef5381687461', 'Gebäude', '', 1, 1, 'Building', 0, 0);
INSERT INTO `resource_categories` (`id`, `name`, `description`, `system`, `iconnr`, `class_name`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', 'Übungsraum', '', 1, 1, 'Room', 0, 0);
INSERT INTO `resource_categories` (`id`, `name`, `description`, `system`, `iconnr`, `class_name`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', 'Hörsaal', '', 1, 1, 'Room', 0, 0);

--
-- Dumping data for table `resource_category_properties`
--

INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('05278c70d89ae99404727408ef111963', '282bd47d19f9df6469777fa5f46f57f0', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('3cbcc99c39476b8e2c8eef5381687461', '282bd47d19f9df6469777fa5f46f57f0', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('3cbcc99c39476b8e2c8eef5381687461', '5c01db06907efbcdc556b5688e70a6de', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('3cbcc99c39476b8e2c8eef5381687461', 'b79b77f40706ed598f5403f953c1f791', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('3cbcc99c39476b8e2c8eef5381687461', 'c4f13691419a6c12d38ad83daa926c7c', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '1f8cef2b614382e36eaa4a29f6027edf', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '28addfe18e86cc3587205734c8bc2372', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '44fd30e8811d0d962582fa1a9c452bdd', 1, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '613cfdf6aa1072e21a1edfcfb0445c69', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '6ea541162f844090000d016740677385', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '6fc3efd459a0d38ceb5d85eaf1f4451d', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '7c1a8f6001cfdcb9e9c33eeee0ef343d', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '94514a9ff5b3336a03cb8b82c8eaf148', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', 'afb8675e2257c03098aa34b2893ba686', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('5a72dfe3f0c0295a8fe4e12c86d4c8f4', 'b79b77f40706ed598f5403f953c1f791', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', '1f8cef2b614382e36eaa4a29f6027edf', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', '28addfe18e86cc3587205734c8bc2372', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', '44fd30e8811d0d962582fa1a9c452bdd', 1, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', '613cfdf6aa1072e21a1edfcfb0445c69', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', '6ea541162f844090000d016740677385', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', '6fc3efd459a0d38ceb5d85eaf1f4451d', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', '7c1a8f6001cfdcb9e9c33eeee0ef343d', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', '94514a9ff5b3336a03cb8b82c8eaf148', 0, 0, 1, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', 'afb8675e2257c03098aa34b2893ba686', 1, 0, 0, NULL, 0, 0);
INSERT INTO `resource_category_properties` (`category_id`, `property_id`, `requestable`, `protected`, `system`, `form_text`, `mkdate`, `chdate`) VALUES('85d62e2a8a87a2924db8fc4ed3fde09d', 'b79b77f40706ed598f5403f953c1f791', 1, 0, 0, NULL, 0, 0);

--
-- Dumping data for table `resource_property_definitions`
--

INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('1f8cef2b614382e36eaa4a29f6027edf', 'has_loudspeakers', '', 'bool', 'vorhanden', 1, 0, 'Audio-Anlage', 1, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('282bd47d19f9df6469777fa5f46f57f0', 'geo_coordinates', NULL, 'position', '', 1, 0, '', 0, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('28addfe18e86cc3587205734c8bc2372', 'is_dimmable', '', 'bool', 'vorhanden', 1, 0, 'Verdunklung', 1, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('44fd30e8811d0d962582fa1a9c452bdd', 'seats', '', 'num', '', 1, 0, 'Sitzplätze', 1, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('5c01db06907efbcdc556b5688e70a6de', 'number', NULL, 'text', '', 1, 0, '', 0, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('613cfdf6aa1072e21a1edfcfb0445c69', 'has_overhead_projector', '', 'bool', 'vorhanden', 1, 0, 'Tageslichtprojektor', 1, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('6ea541162f844090000d016740677385', 'responsible_person', '', 'user', '', 1, 1, 'Raumverantwortung', 0, 0, 'admin-global', NULL, NULL, 1591630778, 1591630778);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('6fc3efd459a0d38ceb5d85eaf1f4451d', 'room_type', NULL, 'select', '', 1, 0, '', 0, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('7c1a8f6001cfdcb9e9c33eeee0ef343d', 'has_projector', '', 'bool', 'vorhanden', 1, 0, 'Beamer', 1, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('94514a9ff5b3336a03cb8b82c8eaf148', 'booking_plan_is_public', NULL, 'bool', '', 1, 0, '', 0, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('afb8675e2257c03098aa34b2893ba686', 'has_computer', '', 'bool', 'vorhanden', 1, 0, 'Dozentenrechner', 1, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('b79b77f40706ed598f5403f953c1f791', 'accessible', '', 'bool', 'vorhanden', 1, 0, 'behindertengerecht', 1, 0, 'admin-global', NULL, NULL, 0, 0);
INSERT INTO `resource_property_definitions` (`property_id`, `name`, `description`, `type`, `options`, `system`, `info_label`, `display_name`, `searchable`, `range_search`, `write_permission_level`, `property_group_id`, `property_group_pos`, `mkdate`, `chdate`) VALUES('c4f13691419a6c12d38ad83daa926c7c', 'address', '', 'text', '', 1, 0, 'Adresse', 0, 0, 'admin-global', NULL, NULL, 0, 0);
