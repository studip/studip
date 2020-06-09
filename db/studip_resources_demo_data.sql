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

REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('2b78c73b9c594ef0a6fd457eb2269f0d', '51ad4b7100d3a8a1db61c7b099f052a6', '03626188114055538dbf693be5885252', '', 1587366000, 1587373200, 1587373200, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('2c41292b3d86289b3dfb7c6cdfd34149', '51ad4b7100d3a8a1db61c7b099f052a6', '7afe4f7ab26e81a89ee185ae2edd920a', '', 1592809200, 1592816400, 1592816400, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('545ac3371fd10ea113869163d86af41a', '51ad4b7100d3a8a1db61c7b099f052a6', 'aabc75c01f0afdc86fa983314ae48f2f', '', 1592204400, 1592211600, 1592211600, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('5528bf551c45ba2a890da3ac02ac718c', '51ad4b7100d3a8a1db61c7b099f052a6', '4d48d87e876b00c1c6a0a20c7faafd54', '', 1588575600, 1588582800, 1588582800, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('5a3b508465e5a6d77ba0b3cddd5bc38a', '51ad4b7100d3a8a1db61c7b099f052a6', '8385a788f8e67d20c8042814a056e34f', '', 1593414000, 1593421200, 1593421200, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('74d04a48f4f1a293dd204aab2c132df8', '51ad4b7100d3a8a1db61c7b099f052a6', '38afd00c489bfaa133a8274d8b9e3e80', '', 1590390000, 1590397200, 1590397200, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('8ec168fbd5081ca57717af3c01cbcc92', '51ad4b7100d3a8a1db61c7b099f052a6', '78959e3630ba91331cecc8f0a86bebb5', '', 1587970800, 1587978000, 1587978000, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('a1dd28925be46413b78b5403a5223329', '51ad4b7100d3a8a1db61c7b099f052a6', 'a9828c0e7fd4fb8a07c546ab485de285', '', 1591599600, 1591606800, 1591606800, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('b88523c2fd47c42088e924dff762cf5f', '51ad4b7100d3a8a1db61c7b099f052a6', '32015cac311883bc9ff552d4fdfaf3a4', '', 1594018800, 1594026000, 1594026000, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('e0aeb6d54582b331674c4a8df1edd34c', '51ad4b7100d3a8a1db61c7b099f052a6', '529a4259af99a226bb35cef9fdcf5bb0', '', 1594623600, 1594630800, 1594630800, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('f04bbe400e3eddfe9d6ba92fe1e27c56', '51ad4b7100d3a8a1db61c7b099f052a6', '4d17fb2feeafc97ee96d2362522ea478', '', 1589785200, 1589792400, 1589792400, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');
REPLACE INTO `resource_bookings` (`id`, `resource_id`, `range_id`, `description`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `mkdate`, `chdate`, `internal_comment`, `preparation_time`, `booking_type`, `booking_user_id`, `repetition_interval`) VALUES('ffe43af0f19de867b3bb3072be27853a', '51ad4b7100d3a8a1db61c7b099f052a6', '9b8992bc23019378e21158a333f98b4f', '', 1589180400, 1589187600, 1589187600, 0, 1573239488, 1573239488, NULL, 0, 0, '', '');

--
-- Dumping data for table `resource_booking_intervals`
--

REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('0d4a1b08c17a018e6df1a1a97d1765e1', '51ad4b7100d3a8a1db61c7b099f052a6', '5528bf551c45ba2a890da3ac02ac718c', 1588575600, 1588582800, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('202b4d6339bdc0c2d6bfccfb2ebc8f61', '51ad4b7100d3a8a1db61c7b099f052a6', 'e0aeb6d54582b331674c4a8df1edd34c', 1594623600, 1594630800, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('4b2dac06d2a38d9d2497e8c341a3cb7e', '51ad4b7100d3a8a1db61c7b099f052a6', '2c41292b3d86289b3dfb7c6cdfd34149', 1592809200, 1592816400, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('59310086170ac07d72c68a2a02ff74b7', '51ad4b7100d3a8a1db61c7b099f052a6', '8ec168fbd5081ca57717af3c01cbcc92', 1587970800, 1587978000, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('846fad3994f75f5d406cd7b086dab16b', '51ad4b7100d3a8a1db61c7b099f052a6', 'a1dd28925be46413b78b5403a5223329', 1591599600, 1591606800, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('a00bb15aded9280001a959076ce2b39b', '51ad4b7100d3a8a1db61c7b099f052a6', '2b78c73b9c594ef0a6fd457eb2269f0d', 1587366000, 1587373200, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('a18d8698e56b97dd9f1d9fec7a39fead', '51ad4b7100d3a8a1db61c7b099f052a6', '74d04a48f4f1a293dd204aab2c132df8', 1590390000, 1590397200, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('b53f866982567b1aa175ee27cde468ae', '51ad4b7100d3a8a1db61c7b099f052a6', 'ffe43af0f19de867b3bb3072be27853a', 1589180400, 1589187600, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('cf2612068dfcd7eb4bb9ce57d4709802', '51ad4b7100d3a8a1db61c7b099f052a6', 'b88523c2fd47c42088e924dff762cf5f', 1594018800, 1594026000, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('d2ab1744e9b0a399d03b79bf6508ae95', '51ad4b7100d3a8a1db61c7b099f052a6', '5a3b508465e5a6d77ba0b3cddd5bc38a', 1593414000, 1593421200, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('dbe5dcd3c3f1444a38d7017c00b4b8ce', '51ad4b7100d3a8a1db61c7b099f052a6', '545ac3371fd10ea113869163d86af41a', 1592204400, 1592211600, 1591713936, 1591713936, 1);
REPLACE INTO `resource_booking_intervals` (`interval_id`, `resource_id`, `booking_id`, `begin`, `end`, `mkdate`, `chdate`, `takes_place`) VALUES('e5111878c92a2b2ea02129f372ec94ca', '51ad4b7100d3a8a1db61c7b099f052a6', 'f04bbe400e3eddfe9d6ba92fe1e27c56', 1589785200, 1589792400, 1591713936, 1591713936, 1);

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
