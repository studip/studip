--
-- Dumping data for table `clipboards`
--

REPLACE INTO `clipboards` (`id`, `user_id`, `name`, `handler`, `allowed_item_class`, `mkdate`, `chdate`) VALUES(1, '76ed43ef286fb55cf9e41beadb484a9f', 'HS', 'Clipboard', 'StudipItem', 1591715351, 1591715351);
REPLACE INTO `clipboards` (`id`, `user_id`, `name`, `handler`, `allowed_item_class`, `mkdate`, `chdate`) VALUES(2, '76ed43ef286fb55cf9e41beadb484a9f', 'SR', 'Clipboard', 'StudipItem', 1591715364, 1591715364);

--
-- Dumping data for table `clipboard_items`
--

REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(1, 1, '728f1578de643fb08b32b4b8afb2db77', 'Room', 1591715354, 1591715354);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(2, 1, 'b17c4ea6e053f2fffba8a5517fc277b3', 'Room', 1591715356, 1591715356);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(3, 1, '2f98bf64830043fd98a39fbbe2068678', 'Room', 1591715357, 1591715357);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(4, 2, '51ad4b7100d3a8a1db61c7b099f052a6', 'Room', 1591715367, 1591715367);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(5, 2, 'a8c03520e8ad9dc90fb2d161ffca7d7b', 'Room', 1591715368, 1591715368);
REPLACE INTO `clipboard_items` (`id`, `clipboard_id`, `range_id`, `range_type`, `mkdate`, `chdate`) VALUES(6, 2, '5ead77812be3b601e2f08ed5da4c5630', 'Room', 1591715370, 1591715370);

--
-- Dumping data for table `resources`
--

REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('2760740189890f47537537ed7fa51a05', '', '05278c70d89ae99404727408ef111963', NULL, 'Stud.IP', '', 0, 1591713936, 1591713936, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('2f98bf64830043fd98a39fbbe2068678', '8a57860ca2be4cc3a77c06c1d346ea57', '85d62e2a8a87a2924db8fc4ed3fde09d', 2, 'Hörsaal 3', '', 1, 1084640542, 1084640555, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '6350c6ae2ec6fd8bd852d505789d0666', '5a72dfe3f0c0295a8fe4e12c86d4c8f4', 2, 'Seminarraum 1', '', 1, 1084640567, 1084640578, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '6350c6ae2ec6fd8bd852d505789d0666', '5a72dfe3f0c0295a8fe4e12c86d4c8f4', 2, 'Seminarraum 3', '', 1, 1084640611, 1084723704, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', '2760740189890f47537537ed7fa51a05', '3cbcc99c39476b8e2c8eef5381687461', 1, 'Übungsgebäude', '', 1, 1084640386, 1591715302, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('728f1578de643fb08b32b4b8afb2db77', '8a57860ca2be4cc3a77c06c1d346ea57', '85d62e2a8a87a2924db8fc4ed3fde09d', 2, 'Hörsaal 1', '', 1, 1084640456, 1084640468, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', '2760740189890f47537537ed7fa51a05', '3cbcc99c39476b8e2c8eef5381687461', 1, 'Hörsaalgebäude', '', 1, 1084640042, 1591715222, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '6350c6ae2ec6fd8bd852d505789d0666', '5a72dfe3f0c0295a8fe4e12c86d4c8f4', 2, 'Seminarraum 2', '', 1, 1084640590, 1084640599, 0);
REPLACE INTO `resources` (`id`, `parent_id`, `category_id`, `level`, `name`, `description`, `requestable`, `mkdate`, `chdate`, `sort_position`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '8a57860ca2be4cc3a77c06c1d346ea57', '85d62e2a8a87a2924db8fc4ed3fde09d', 2, 'Hörsaal 2', '', 1, 1084640520, 1084640528, 0);

--
-- Dumping data for table `resource_bookings`
--

REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('027fc3aab9dbfe5f3523fa42ac5c88c0', '728f1578de643fb08b32b4b8afb2db77', 'ea1c909ebb579cb36f20644be179af0d', '', 1607932800, 1607940000, NULL, NULL, 1607705853, 1607705853, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('08cd29015524985ac4217e9b9a1d02ae', '728f1578de643fb08b32b4b8afb2db77', '30bff5862583627e22245f9cc04abad9', '', 1604908800, 1604916000, NULL, NULL, 1607705853, 1607705853, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('166de94dd8d68e51dc82dd0ab7736e41', '728f1578de643fb08b32b4b8afb2db77', '62db4120526756d53f3d7a67fcdc3d45', '', 1605513600, 1605520800, NULL, NULL, 1607705853, 1607705853, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('207fa0f1e10e2dfbda1ed032c214a83b', '728f1578de643fb08b32b4b8afb2db77', '3cba067912d87c7b18fd2f24d07d6250', '', 1604304000, 1604311200, NULL, NULL, 1607705853, 1607705853, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('742b08bb636ef331da5841d0901b2ea3', '728f1578de643fb08b32b4b8afb2db77', 'f77250391dc627c527b1bb1efe6de9c0', '', 1607328000, 1607335200, NULL, NULL, 1607705853, 1607705853, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('889559e59a2c761137badd974a54b90d', '728f1578de643fb08b32b4b8afb2db77', '33dac8bb7b8b404c2337bbaf036e4018', '', 1610956800, 1610964000, NULL, NULL, 1607705854, 1607705854, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('a186ae1bc7875b24e83f16d5ea3378ff', '728f1578de643fb08b32b4b8afb2db77', '72752e09d7b593dcc1a6fa59f906e571', '', 1606723200, 1606730400, NULL, NULL, 1607705853, 1607705853, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('a7ab89c550bb460fbcd5bcd8cf07396d', '728f1578de643fb08b32b4b8afb2db77', 'dc66d3ce8ac166b7332599372b1561aa', '', 1610352000, 1610359200, NULL, NULL, 1607705854, 1607705854, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('b147be5d293bc99dbb3a5354da5556b5', '51ad4b7100d3a8a1db61c7b099f052a6', '', 'Entseuchung', 1609228800, 1609239600, NULL, NULL, 1607705959, 1607705959, '', 0, 1, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('b64f7aa6d55ab38a1347c982b13dddee', '728f1578de643fb08b32b4b8afb2db77', 'ea08bd23013de27be12e18892d90b227', '', 1611561600, 1611568800, NULL, NULL, 1607705854, 1607705854, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('c0faa097a74db1694d90dcc0c5a498b6', '728f1578de643fb08b32b4b8afb2db77', 'af699ff6cab47680e8f4d06239d5fc49', '', 1606118400, 1606125600, NULL, NULL, 1607705853, 1607705853, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('cc0e4643412a3fc49e1e79529ae62a48', '728f1578de643fb08b32b4b8afb2db77', '4b848f06d1f3983ffd9c227db08e6147', '', 1608537600, 1608544800, NULL, NULL, 1607705853, 1607705853, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('e8d37c25a27ddfc01c860373f7791406', '728f1578de643fb08b32b4b8afb2db77', '414a1c7ff481c76b7dbc01ac95e2015e', '', 1603699200, 1603706400, NULL, NULL, 1607705853, 1607705853, '', 0, 0, '76ed43ef286fb55cf9e41beadb484a9f', '');

--
-- Dumping data for table `resource_booking_intervals`
--

REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('02eef055bcbeee5848629bad3d1f899a', '728f1578de643fb08b32b4b8afb2db77', 'b64f7aa6d55ab38a1347c982b13dddee', 1611561600, 1611568800, 1607705854, 1607705854, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('2237ee0f74c7381149029643cf385ecc', '728f1578de643fb08b32b4b8afb2db77', 'a7ab89c550bb460fbcd5bcd8cf07396d', 1610352000, 1610359200, 1607705854, 1607705854, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('223de79c5e3ce9041773f6322ad9935c', '728f1578de643fb08b32b4b8afb2db77', 'a186ae1bc7875b24e83f16d5ea3378ff', 1606723200, 1606730400, 1607705853, 1607705853, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('691a93432404814b5e1c75fc969323fc', '728f1578de643fb08b32b4b8afb2db77', 'cc0e4643412a3fc49e1e79529ae62a48', 1608537600, 1608544800, 1607705853, 1607705853, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('6c3bb2b3844fdb62461501aa7411efd9', '728f1578de643fb08b32b4b8afb2db77', 'e8d37c25a27ddfc01c860373f7791406', 1603699200, 1603706400, 1607705853, 1607705853, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('82dcaf23e29301178525a74fc73e4105', '728f1578de643fb08b32b4b8afb2db77', '889559e59a2c761137badd974a54b90d', 1610956800, 1610964000, 1607705854, 1607705854, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('8a2988600d1d78e430bfc709e609beab', '51ad4b7100d3a8a1db61c7b099f052a6', 'b147be5d293bc99dbb3a5354da5556b5', 1609228800, 1609239600, 1607705959, 1607705959, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('a02a29f78e71a76ae3a030b391baa672', '728f1578de643fb08b32b4b8afb2db77', 'c0faa097a74db1694d90dcc0c5a498b6', 1606118400, 1606125600, 1607705853, 1607705853, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('a3eeadd9e3de6180a9a0554f27ea619f', '728f1578de643fb08b32b4b8afb2db77', '166de94dd8d68e51dc82dd0ab7736e41', 1605513600, 1605520800, 1607705853, 1607705853, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('acd66a58890977405526da9525484491', '728f1578de643fb08b32b4b8afb2db77', '08cd29015524985ac4217e9b9a1d02ae', 1604908800, 1604916000, 1607705853, 1607705853, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('d9b8887fd978d66d0edbc69edf9f8597', '728f1578de643fb08b32b4b8afb2db77', '027fc3aab9dbfe5f3523fa42ac5c88c0', 1607932800, 1607940000, 1607705853, 1607705853, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('eec56b6250a99393969c6ee0f27af11a', '728f1578de643fb08b32b4b8afb2db77', '207fa0f1e10e2dfbda1ed032c214a83b', 1604304000, 1604311200, 1607705853, 1607705853, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('f7cbc12497a2d29ede9755fcf7bf0bc3', '728f1578de643fb08b32b4b8afb2db77', '742b08bb636ef331da5841d0901b2ea3', 1607328000, 1607335200, 1607705853, 1607705853, 1);

--
-- Dumping data for table `resource_permissions`
--

REPLACE INTO `resource_permissions` (`user_id`, `resource_id`, `perms`, `mkdate`, `chdate`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', '2f98bf64830043fd98a39fbbe2068678', 'admin', 1084640542, 1084640555);
REPLACE INTO `resource_permissions` (`user_id`, `resource_id`, `perms`, `mkdate`, `chdate`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', '51ad4b7100d3a8a1db61c7b099f052a6', 'admin', 1084640567, 1084640578);
REPLACE INTO `resource_permissions` (`user_id`, `resource_id`, `perms`, `mkdate`, `chdate`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', '5ead77812be3b601e2f08ed5da4c5630', 'admin', 1084640611, 1084723704);
REPLACE INTO `resource_permissions` (`user_id`, `resource_id`, `perms`, `mkdate`, `chdate`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', '728f1578de643fb08b32b4b8afb2db77', 'admin', 1084640456, 1084640468);
REPLACE INTO `resource_permissions` (`user_id`, `resource_id`, `perms`, `mkdate`, `chdate`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 'a8c03520e8ad9dc90fb2d161ffca7d7b', 'admin', 1084640590, 1084640599);
REPLACE INTO `resource_permissions` (`user_id`, `resource_id`, `perms`, `mkdate`, `chdate`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 'b17c4ea6e053f2fffba8a5517fc277b3', 'admin', 1084640520, 1084640528);

--
-- Dumping data for table `resource_properties`
--

REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2760740189890f47537537ed7fa51a05', '674ea21ef56fd973bb30ee6f247c0723', '+0.0+0.0+0.0CRSWGS_84/', 1591714592, 1591714592);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '28addfe18e86cc3587205734c8bc2372', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640542, 1084640555);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '44fd30e8811d0d962582fa1a9c452bdd', '25', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '613cfdf6aa1072e21a1edfcfb0445c69', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('2f98bf64830043fd98a39fbbe2068678', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '28addfe18e86cc3587205734c8bc2372', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640567, 1084640578);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '44fd30e8811d0d962582fa1a9c452bdd', '25', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '613cfdf6aa1072e21a1edfcfb0445c69', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('51ad4b7100d3a8a1db61c7b099f052a6', 'afb8675e2257c03098aa34b2893ba686', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '1f8cef2b614382e36eaa4a29f6027edf', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '28addfe18e86cc3587205734c8bc2372', '0', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640611, 1084723704);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '44fd30e8811d0d962582fa1a9c452bdd', '15', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('5ead77812be3b601e2f08ed5da4c5630', 'afb8675e2257c03098aa34b2893ba686', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', '674ea21ef56fd973bb30ee6f247c0723', '+51.5398160+9.9367200+0.0000000CRSWGS_84/', 1591714594, 1591715302);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', 'c4f13691419a6c12d38ad83daa926c7c', 'Liebigstr. 1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('6350c6ae2ec6fd8bd852d505789d0666', 'e141f19ca6da2938d4c51cc59462884b', '', 1591714589, 1591714589);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '1f8cef2b614382e36eaa4a29f6027edf', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '28addfe18e86cc3587205734c8bc2372', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640456, 1084640468);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '44fd30e8811d0d962582fa1a9c452bdd', '500', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '613cfdf6aa1072e21a1edfcfb0445c69', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '72723662c924e785a6662f42c84b8bb4', '', 1591714470, 1591714470);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '7c1a8f6001cfdcb9e9c33eeee0ef343d', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', 'afb8675e2257c03098aa34b2893ba686', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', '674ea21ef56fd973bb30ee6f247c0723', '+51.5407270+9.9354050+0.0000000CRSWGS_84/', 1591714991, 1591715222);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', 'c4f13691419a6c12d38ad83daa926c7c', 'Universitätsstr. 1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', 'e141f19ca6da2938d4c51cc59462884b', '', 1591714589, 1591714589);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '28addfe18e86cc3587205734c8bc2372', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640590, 1084640599);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '44fd30e8811d0d962582fa1a9c452bdd', '30', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '613cfdf6aa1072e21a1edfcfb0445c69', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', '7c1a8f6001cfdcb9e9c33eeee0ef343d', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', 'afb8675e2257c03098aa34b2893ba686', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('a8c03520e8ad9dc90fb2d161ffca7d7b', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '2650f839a2a02d99f82d4a6c019da329', '1', 1591713936, 1591713936);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '28addfe18e86cc3587205734c8bc2372', '0', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '3089b4bf392b42e8d21218f29b24f799', '76ed43ef286fb55cf9e41beadb484a9f', 1084640520, 1084640528);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '44fd30e8811d0d962582fa1a9c452bdd', '150', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '72723662c924e785a6662f42c84b8bb4', '', 1591714586, 1591714586);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', '7c1a8f6001cfdcb9e9c33eeee0ef343d', '1', 0, 0);
REPLACE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('b17c4ea6e053f2fffba8a5517fc277b3', 'b79b77f40706ed598f5403f953c1f791', '1', 0, 0);

--
-- Dumping data for table `resource_requests`
--

REPLACE INTO `resource_requests` (`id`, `course_id`, `termin_id`, `metadate_id`, `user_id`, `last_modified_by`, `resource_id`, `category_id`, `comment`, `reply_comment`, `reply_recipients`, `closed`, `mkdate`, `chdate`, `begin`, `end`, `preparation_time`, `marked`) VALUES('287715ad7156792ee8a1c4a00a23831a', 'a07535cf2f8a72df33c12ddfa4b53dde', '9ff59e18112a686c553412761a5df85c', '', '76ed43ef286fb55cf9e41beadb484a9f', '', '', '5a72dfe3f0c0295a8fe4e12c86d4c8f4', NULL, NULL, 'requester', 0, 1591714392, 1591714392, 0, 0, 900, 0);

--
-- Dumping data for table `resource_request_properties`
--

REPLACE INTO `resource_request_properties` (`request_id`, `property_id`, `state`, `mkdate`, `chdate`) VALUES('287715ad7156792ee8a1c4a00a23831a', '44fd30e8811d0d962582fa1a9c452bdd', '20', 1591714392, 1591714392);
