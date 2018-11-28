--
-- Daten für Tabelle `config`
--

REPLACE INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`) VALUES
('RESOURCES_ENABLE', 'studip', '1', 1530292001, 1530292001, '');

--
-- Daten für Tabelle `resources_categories`
--

INSERT INTO `resources_categories` (`category_id`, `name`, `description`, `system`, `is_room`, `iconnr`) VALUES ('3cbcc99c39476b8e2c8eef5381687461', 'Gebäude', '', 0, 0, 1);
INSERT INTO `resources_categories` (`category_id`, `name`, `description`, `system`, `is_room`, `iconnr`) VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', 'Hörsaal', '', 0, 1, 1);
INSERT INTO `resources_categories` (`category_id`, `name`, `description`, `system`, `is_room`, `iconnr`) VALUES ('f3351baeca8776d4ffe4b672f568cbed', 'Gerät', '', 0, 0, 1);
INSERT INTO `resources_categories` (`category_id`, `name`, `description`, `system`, `is_room`, `iconnr`) VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', 'Übungsraum', '', 0, 1, 1);

--
-- Daten für Tabelle `resources_categories_properties`
--

INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('3cbcc99c39476b8e2c8eef5381687461', 'c4f13691419a6c12d38ad83daa926c7c', 0, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', 'afb8675e2257c03098aa34b2893ba686', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '7c1a8f6001cfdcb9e9c33eeee0ef343d', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('3cbcc99c39476b8e2c8eef5381687461', 'b79b77f40706ed598f5403f953c1f791', 0, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '1f8cef2b614382e36eaa4a29f6027edf', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '44fd30e8811d0d962582fa1a9c452bdd', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '613cfdf6aa1072e21a1edfcfb0445c69', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', '28addfe18e86cc3587205734c8bc2372', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '7c1a8f6001cfdcb9e9c33eeee0ef343d', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', 'afb8675e2257c03098aa34b2893ba686', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', 'b79b77f40706ed598f5403f953c1f791', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '1f8cef2b614382e36eaa4a29f6027edf', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '44fd30e8811d0d962582fa1a9c452bdd', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '613cfdf6aa1072e21a1edfcfb0445c69', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('5a72dfe3f0c0295a8fe4e12c86d4c8f4', '28addfe18e86cc3587205734c8bc2372', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('85d62e2a8a87a2924db8fc4ed3fde09d', 'b79b77f40706ed598f5403f953c1f791', 1, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('f3351baeca8776d4ffe4b672f568cbed', 'cb8140efbc2af5362b1159c65deeec9e', 0, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('f3351baeca8776d4ffe4b672f568cbed', 'c4352a580051a81830ef5980941c9e06', 0, 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('f3351baeca8776d4ffe4b672f568cbed', '39c73942e1c1650fa20c7259be96b3f3', 0, 0);

--
-- Daten für Tabelle `resources_properties`
--

INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('44fd30e8811d0d962582fa1a9c452bdd', 'Sitzplätze', '', 'num', '', 2);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('c4f13691419a6c12d38ad83daa926c7c', 'Adresse', '', 'text', '', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('7c1a8f6001cfdcb9e9c33eeee0ef343d', 'Beamer', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('b79b77f40706ed598f5403f953c1f791', 'behindertengerecht', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('613cfdf6aa1072e21a1edfcfb0445c69', 'Tageslichtprojektor', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('afb8675e2257c03098aa34b2893ba686', 'Dozentenrechner', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('1f8cef2b614382e36eaa4a29f6027edf', 'Audio-Anlage', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('c4352a580051a81830ef5980941c9e06', 'Seriennummer', '', 'num', '', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('cb8140efbc2af5362b1159c65deeec9e', 'Hersteller', '', 'select', 'Sony;Philips;Technics;Telefunken;anderer', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('39c73942e1c1650fa20c7259be96b3f3', 'Inventarnummer', '', 'num', '', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('28addfe18e86cc3587205734c8bc2372', 'Verdunklung', '', 'bool', 'vorhanden', 0);
