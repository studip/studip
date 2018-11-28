-- MySQL dump 10.13  Distrib 5.5.40, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: tgloeggl_trunk
-- ------------------------------------------------------
-- Server version	5.5.40-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Institute`
--

DROP TABLE IF EXISTS `Institute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Institute` (
  `Institut_id` varchar(32) NOT NULL DEFAULT '',
  `Name` varchar(255) NOT NULL DEFAULT '',
  `fakultaets_id` varchar(32) NOT NULL DEFAULT '',
  `Strasse` varchar(255) NOT NULL DEFAULT '',
  `Plz` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT 'http://www.studip.de',
  `telefon` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `fax` varchar(32) NOT NULL DEFAULT '',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `modules` int(10) unsigned DEFAULT NULL,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `lit_plugin_name` varchar(255) DEFAULT NULL,
  `srienabled` tinyint(4) NOT NULL DEFAULT '0',
  `lock_rule` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`Institut_id`),
  KEY `fakultaets_id` (`fakultaets_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Institute`
--

LOCK TABLES `Institute` WRITE;
/*!40000 ALTER TABLE `Institute` DISABLE KEYS */;
INSERT INTO `Institute` VALUES ('1535795b0d6ddecac6813f5f6ac47ef2','Test Fakult','1535795b0d6ddecac6813f5f6ac47ef2','Geismar Landstr. 17b','37083 G?ttingen','http://www.studip.de','0551 / 381 985 0','testfakultaet@studip.de','0551 / 381 985 3',1,16,1156516698,1156516698,'Studip',0,''),('2560f7c7674942a7dce8eeb238e15d93','Test Einrichtung','1535795b0d6ddecac6813f5f6ac47ef2','','','','','','',1,16,1156516698,1156516698,'Studip',0,''),('536249daa596905f433e1f73578019db','Test Lehrstuhl','1535795b0d6ddecac6813f5f6ac47ef2','','','','','','',3,16,1156516698,1156516698,'Studip',0,''),('f02e2b17bc0e99fc885da6ac4c2532dc','Test Abteilung','1535795b0d6ddecac6813f5f6ac47ef2','','','','','','',4,16,1156516698,1156516698,'Studip',0,''),('ec2e364b28357106c0f8c282733dbe56','externe Bildungseinrichtungen','ec2e364b28357106c0f8c282733dbe56','','','','','','',1,16,1156516698,1156516698,'Studip',0,''),('7a4f19a0a2c321ab2b8f7b798881af7c','externe Einrichtung A','ec2e364b28357106c0f8c282733dbe56','','','','','','',1,16,1156516698,1156516698,'Studip',0,''),('110ce78ffefaf1e5f167cd7019b728bf','externe Einrichtung B','ec2e364b28357106c0f8c282733dbe56','','','','','','',1,16,1156516698,1156516698,'Studip',0,'');
/*!40000 ALTER TABLE `Institute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `abschluss`
--

DROP TABLE IF EXISTS `abschluss`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abschluss` (
  `abschluss_id` char(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `beschreibung` text,
  `mkdate` int(20) DEFAULT NULL,
  `chdate` int(20) DEFAULT NULL,
  PRIMARY KEY (`abschluss_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `abschluss`
--

LOCK TABLES `abschluss` WRITE;
/*!40000 ALTER TABLE `abschluss` DISABLE KEYS */;
INSERT INTO `abschluss` VALUES ('228234544820cdf75db55b42d1ea3ecc','Bachelor','',1311416359,1311416359),('c7f569e815a35cf24a515a0e67928072','Master','',1311416385,1311416385);
/*!40000 ALTER TABLE `abschluss` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admission_condition`
--

DROP TABLE IF EXISTS `admission_condition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admission_condition` (
  `rule_id` varchar(32) NOT NULL,
  `filter_id` varchar(32) NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`,`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admission_condition`
--

LOCK TABLES `admission_condition` WRITE;
/*!40000 ALTER TABLE `admission_condition` DISABLE KEYS */;
/*!40000 ALTER TABLE `admission_condition` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admission_seminar_user`
--

DROP TABLE IF EXISTS `admission_seminar_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admission_seminar_user` (
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `seminar_id` varchar(32) NOT NULL DEFAULT '',
  `status` varchar(16) NOT NULL DEFAULT '',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `position` int(5) DEFAULT NULL,
  `comment` tinytext,
  `visible` enum('yes','no','unknown') NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`user_id`,`seminar_id`),
  KEY `seminar_id` (`seminar_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admission_seminar_user`
--

LOCK TABLES `admission_seminar_user` WRITE;
/*!40000 ALTER TABLE `admission_seminar_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `admission_seminar_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admissionfactor`
--

DROP TABLE IF EXISTS `admissionfactor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admissionfactor` (
  `list_id` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `factor` decimal(5,2) NOT NULL DEFAULT '1.00',
  `owner_id` varchar(32) NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admissionfactor`
--

LOCK TABLES `admissionfactor` WRITE;
/*!40000 ALTER TABLE `admissionfactor` DISABLE KEYS */;
/*!40000 ALTER TABLE `admissionfactor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admissionrule_inst`
--

DROP TABLE IF EXISTS `admissionrule_inst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admissionrule_inst` (
  `rule_id` varchar(32) NOT NULL,
  `institute_id` varchar(32) NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`,`institute_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admissionrule_inst`
--

LOCK TABLES `admissionrule_inst` WRITE;
/*!40000 ALTER TABLE `admissionrule_inst` DISABLE KEYS */;
/*!40000 ALTER TABLE `admissionrule_inst` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admissionrules`
--

DROP TABLE IF EXISTS `admissionrules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admissionrules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ruletype` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruletype` (`ruletype`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admissionrules`
--

LOCK TABLES `admissionrules` WRITE;
/*!40000 ALTER TABLE `admissionrules` DISABLE KEYS */;
INSERT INTO `admissionrules` VALUES (1,'ConditionalAdmission',1,1399476073),(2,'LimitedAdmission',1,1399476073),(3,'LockedAdmission',1,1399476073),(4,'PasswordAdmission',1,1399476073),(5,'TimedAdmission',1,1399476073),(6,'ParticipantRestrictedAdmission',1,1399476073),(7,'CourseMemberAdmission',1,1417519749);
/*!40000 ALTER TABLE `admissionrules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_consumer_permissions`
--

DROP TABLE IF EXISTS `api_consumer_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_consumer_permissions` (
  `route_id` char(32) NOT NULL,
  `consumer_id` char(32) NOT NULL DEFAULT '',
  `method` char(6) NOT NULL,
  `granted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `route_id` (`route_id`,`consumer_id`,`method`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_consumer_permissions`
--

LOCK TABLES `api_consumer_permissions` WRITE;
/*!40000 ALTER TABLE `api_consumer_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_consumer_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_consumers`
--

DROP TABLE IF EXISTS `api_consumers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_consumers` (
  `consumer_id` char(32) NOT NULL DEFAULT '',
  `consumer_type` enum('http','studip','oauth') NOT NULL DEFAULT 'studip',
  `auth_key` varchar(64) DEFAULT NULL,
  `auth_secret` varchar(64) DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` enum('website','mobile','desktop') DEFAULT 'website',
  `title` varchar(128) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `callback` varchar(255) DEFAULT NULL,
  `commercial` tinyint(1) DEFAULT NULL,
  `description` text,
  `priority` int(11) unsigned NOT NULL DEFAULT '0',
  `notes` text,
  `mkdate` int(11) unsigned NOT NULL,
  `chdate` int(11) unsigned NOT NULL,
  PRIMARY KEY (`consumer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_consumers`
--

LOCK TABLES `api_consumers` WRITE;
/*!40000 ALTER TABLE `api_consumers` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_consumers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_oauth_user_mapping`
--

DROP TABLE IF EXISTS `api_oauth_user_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_oauth_user_mapping` (
  `oauth_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(32) NOT NULL DEFAULT '',
  `mkdate` int(11) unsigned NOT NULL,
  PRIMARY KEY (`oauth_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_oauth_user_mapping`
--

LOCK TABLES `api_oauth_user_mapping` WRITE;
/*!40000 ALTER TABLE `api_oauth_user_mapping` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_oauth_user_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_user_permissions`
--

DROP TABLE IF EXISTS `api_user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_user_permissions` (
  `user_id` char(32) NOT NULL DEFAULT '',
  `consumer_id` char(32) NOT NULL DEFAULT '',
  `granted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `mkdate` int(11) unsigned NOT NULL,
  `chdate` int(11) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`consumer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_user_permissions`
--

LOCK TABLES `api_user_permissions` WRITE;
/*!40000 ALTER TABLE `api_user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archiv`
--

DROP TABLE IF EXISTS `archiv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archiv` (
  `seminar_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `untertitel` varchar(255) NOT NULL DEFAULT '',
  `beschreibung` text NOT NULL,
  `start_time` int(20) NOT NULL DEFAULT '0',
  `semester` varchar(16) NOT NULL DEFAULT '',
  `heimat_inst_id` varchar(32) NOT NULL DEFAULT '',
  `institute` varchar(255) NOT NULL DEFAULT '',
  `dozenten` varchar(255) NOT NULL DEFAULT '',
  `fakultaet` varchar(255) NOT NULL DEFAULT '',
  `dump` mediumtext NOT NULL,
  `archiv_file_id` varchar(32) NOT NULL DEFAULT '',
  `archiv_protected_file_id` varchar(32) NOT NULL DEFAULT '',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `forumdump` longtext NOT NULL,
  `wikidump` longtext,
  `studienbereiche` text NOT NULL,
  `VeranstaltungsNummer` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`),
  KEY `heimat_inst_id` (`heimat_inst_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archiv`
--

LOCK TABLES `archiv` WRITE;
/*!40000 ALTER TABLE `archiv` DISABLE KEYS */;
/*!40000 ALTER TABLE `archiv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archiv_user`
--

DROP TABLE IF EXISTS `archiv_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archiv_user` (
  `seminar_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `status` enum('user','autor','tutor','dozent') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`seminar_id`,`user_id`),
  KEY `user_id` (`user_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archiv_user`
--

LOCK TABLES `archiv_user` WRITE;
/*!40000 ALTER TABLE `archiv_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `archiv_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_extern`
--

DROP TABLE IF EXISTS `auth_extern`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_extern` (
  `studip_user_id` varchar(32) NOT NULL DEFAULT '',
  `external_user_id` varchar(32) NOT NULL DEFAULT '',
  `external_user_name` varchar(64) NOT NULL DEFAULT '',
  `external_user_password` varchar(32) NOT NULL DEFAULT '',
  `external_user_category` varchar(32) NOT NULL DEFAULT '',
  `external_user_system_type` varchar(32) NOT NULL DEFAULT '',
  `external_user_type` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`studip_user_id`,`external_user_system_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_extern`
--

LOCK TABLES `auth_extern` WRITE;
/*!40000 ALTER TABLE `auth_extern` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_extern` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_user_md5`
--

DROP TABLE IF EXISTS `auth_user_md5`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_user_md5` (
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `password` varbinary(64) NOT NULL DEFAULT '',
  `perms` enum('user','autor','tutor','dozent','admin','root') NOT NULL DEFAULT 'user',
  `Vorname` varchar(64) DEFAULT NULL,
  `Nachname` varchar(64) DEFAULT NULL,
  `Email` varchar(64) DEFAULT NULL,
  `validation_key` varchar(10) NOT NULL DEFAULT '',
  `auth_plugin` varchar(64) DEFAULT 'standard',
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lock_comment` varchar(255) DEFAULT NULL,
  `locked_by` varchar(32) DEFAULT NULL,
  `visible` enum('global','always','yes','unknown','no','never') NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `k_username` (`username`),
  KEY `perms` (`perms`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_user_md5`
--

LOCK TABLES `auth_user_md5` WRITE;
/*!40000 ALTER TABLE `auth_user_md5` DISABLE KEYS */;
INSERT INTO `auth_user_md5` VALUES ('76ed43ef286fb55cf9e41beadb484a9f','root@studip','$2a$08$SRoCYxAhWPFVF8V8CO15TOyzr.PpLRfVD9lVWVrmmBw4brkRTE/2G','root','Root','Studip','root@localhost','','standard',0,NULL,NULL,'unknown'),('205f3efb7997a0fc9755da2b535038da','test_dozent','$2a$08$ajIvgEjd17MiiDcFr6msc.xldknH/tTGajUXVhDxDKNJVX0H0iv0i','dozent','Testaccount','Dozent','dozent@studip.de','','standard',0,NULL,NULL,'unknown'),('6235c46eb9e962866ebdceece739ace5','test_admin','$2a$08$svvSma20vIxIR4J5gc0jIu31gws1WibmiQ/HDhCTukFA5GqhscY1G','admin','Testaccount','Admin','admin@studip.de','','standard',0,NULL,NULL,'unknown'),('7e81ec247c151c02ffd479511e24cc03','test_tutor','$2a$08$mGhBl85TPsiItumZ4xjbgOnQ1vqIhLAC9giCfWcFzpkE1jqe4lmby','tutor','Testaccount','Tutor','tutor@studip.de','','standard',0,NULL,NULL,'unknown'),('e7a0a84b161f3e8c09b4a0a2e8a58147','test_autor','$2a$08$xvbrvPhkcsvkzPZsNh.kceLw2IIwiNJ.1jGOwY3.H/dR2f8PG5X3O','autor','Testaccount','Autor','autor@studip.de','','standard',0,NULL,NULL,'unknown');
/*!40000 ALTER TABLE `auth_user_md5` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auto_insert_sem`
--

DROP TABLE IF EXISTS `auto_insert_sem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_insert_sem` (
  `seminar_id` char(32) NOT NULL,
  `status` enum('autor','tutor','dozent') NOT NULL DEFAULT 'autor',
  `domain_id` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`,`status`,`domain_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_insert_sem`
--

LOCK TABLES `auto_insert_sem` WRITE;
/*!40000 ALTER TABLE `auto_insert_sem` DISABLE KEYS */;
/*!40000 ALTER TABLE `auto_insert_sem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auto_insert_user`
--

DROP TABLE IF EXISTS `auto_insert_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_insert_user` (
  `seminar_id` char(32) NOT NULL,
  `user_id` char(32) NOT NULL,
  `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`seminar_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_insert_user`
--

LOCK TABLES `auto_insert_user` WRITE;
/*!40000 ALTER TABLE `auto_insert_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `auto_insert_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aux_lock_rules`
--

DROP TABLE IF EXISTS `aux_lock_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aux_lock_rules` (
  `lock_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `attributes` text NOT NULL,
  `sorting` text NOT NULL,
  PRIMARY KEY (`lock_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aux_lock_rules`
--

LOCK TABLES `aux_lock_rules` WRITE;
/*!40000 ALTER TABLE `aux_lock_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `aux_lock_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banner_ads`
--

DROP TABLE IF EXISTS `banner_ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banner_ads` (
  `ad_id` varchar(32) NOT NULL DEFAULT '',
  `banner_path` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `alttext` varchar(255) DEFAULT NULL,
  `target_type` enum('url','seminar','inst','user','none') NOT NULL DEFAULT 'url',
  `target` varchar(255) NOT NULL DEFAULT '',
  `startdate` int(20) NOT NULL DEFAULT '0',
  `enddate` int(20) NOT NULL DEFAULT '0',
  `priority` int(4) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banner_ads`
--

LOCK TABLES `banner_ads` WRITE;
/*!40000 ALTER TABLE `banner_ads` DISABLE KEYS */;
/*!40000 ALTER TABLE `banner_ads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blubber`
--

DROP TABLE IF EXISTS `blubber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blubber` (
  `topic_id` varchar(32) NOT NULL DEFAULT '',
  `parent_id` varchar(32) NOT NULL DEFAULT '',
  `root_id` varchar(32) NOT NULL DEFAULT '',
  `context_type` enum('public','private','course') NOT NULL DEFAULT 'public',
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `author_host` varchar(255) DEFAULT NULL,
  `Seminar_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `external_contact` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `parent_id` (`parent_id`),
  KEY `chdate` (`chdate`),
  KEY `mkdate` (`mkdate`),
  KEY `user_id` (`user_id`,`Seminar_id`),
  KEY `root_id` (`root_id`,`mkdate`),
  KEY `Seminar_id` (`Seminar_id`,`context_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blubber`
--

LOCK TABLES `blubber` WRITE;
/*!40000 ALTER TABLE `blubber` DISABLE KEYS */;
/*!40000 ALTER TABLE `blubber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blubber_events_queue`
--

DROP TABLE IF EXISTS `blubber_events_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blubber_events_queue` (
  `event_type` varchar(32) NOT NULL,
  `item_id` varchar(32) NOT NULL,
  `mkdate` int(11) NOT NULL,
  PRIMARY KEY (`event_type`,`item_id`,`mkdate`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blubber_events_queue`
--

LOCK TABLES `blubber_events_queue` WRITE;
/*!40000 ALTER TABLE `blubber_events_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `blubber_events_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blubber_external_contact`
--

DROP TABLE IF EXISTS `blubber_external_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blubber_external_contact` (
  `external_contact_id` varchar(32) NOT NULL,
  `mail_identifier` varchar(256) DEFAULT NULL,
  `contact_type` varchar(16) NOT NULL DEFAULT 'anonymous',
  `name` varchar(256) NOT NULL,
  `data` text,
  `chdate` bigint(20) NOT NULL,
  `mkdate` bigint(20) NOT NULL,
  PRIMARY KEY (`external_contact_id`),
  KEY `mail_identifier` (`mail_identifier`),
  KEY `contact_type` (`contact_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blubber_external_contact`
--

LOCK TABLES `blubber_external_contact` WRITE;
/*!40000 ALTER TABLE `blubber_external_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `blubber_external_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blubber_follower`
--

DROP TABLE IF EXISTS `blubber_follower`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blubber_follower` (
  `studip_user_id` varchar(32) NOT NULL,
  `external_contact_id` varchar(32) NOT NULL,
  `left_follows_right` tinyint(1) NOT NULL,
  KEY `studip_user_id` (`studip_user_id`),
  KEY `external_contact_id` (`external_contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blubber_follower`
--

LOCK TABLES `blubber_follower` WRITE;
/*!40000 ALTER TABLE `blubber_follower` DISABLE KEYS */;
/*!40000 ALTER TABLE `blubber_follower` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blubber_mentions`
--

DROP TABLE IF EXISTS `blubber_mentions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blubber_mentions` (
  `topic_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `external_contact` tinyint(4) NOT NULL DEFAULT '0',
  `mkdate` int(11) NOT NULL,
  UNIQUE KEY `unique_users_per_topic` (`topic_id`,`user_id`,`external_contact`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blubber_mentions`
--

LOCK TABLES `blubber_mentions` WRITE;
/*!40000 ALTER TABLE `blubber_mentions` DISABLE KEYS */;
/*!40000 ALTER TABLE `blubber_mentions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blubber_reshares`
--

DROP TABLE IF EXISTS `blubber_reshares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blubber_reshares` (
  `topic_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `external_contact` tinyint(4) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL,
  UNIQUE KEY `unique_reshares` (`topic_id`,`user_id`,`external_contact`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blubber_reshares`
--

LOCK TABLES `blubber_reshares` WRITE;
/*!40000 ALTER TABLE `blubber_reshares` DISABLE KEYS */;
/*!40000 ALTER TABLE `blubber_reshares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blubber_streams`
--

DROP TABLE IF EXISTS `blubber_streams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blubber_streams` (
  `stream_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` enum('activity','age') NOT NULL DEFAULT 'age',
  `defaultstream` tinyint(2) NOT NULL DEFAULT '0',
  `pool_courses` text,
  `pool_groups` text,
  `pool_hashtags` text,
  `filter_type` text,
  `filter_courses` text,
  `filter_groups` text,
  `filter_users` text,
  `filter_hashtags` text,
  `filter_nohashtags` text,
  `chdate` bigint(20) NOT NULL,
  `mkdate` bigint(20) NOT NULL,
  PRIMARY KEY (`stream_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blubber_streams`
--

LOCK TABLES `blubber_streams` WRITE;
/*!40000 ALTER TABLE `blubber_streams` DISABLE KEYS */;
/*!40000 ALTER TABLE `blubber_streams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blubber_tags`
--

DROP TABLE IF EXISTS `blubber_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blubber_tags` (
  `topic_id` varchar(32) NOT NULL,
  `tag` varchar(128) NOT NULL,
  PRIMARY KEY (`topic_id`,`tag`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blubber_tags`
--

LOCK TABLES `blubber_tags` WRITE;
/*!40000 ALTER TABLE `blubber_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `blubber_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_events` (
  `event_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `autor_id` varchar(32) NOT NULL DEFAULT '',
  `editor_id` varchar(32) NOT NULL,
  `uid` varchar(255) NOT NULL DEFAULT '',
  `start` int(10) unsigned NOT NULL DEFAULT '0',
  `end` int(10) unsigned NOT NULL DEFAULT '0',
  `summary` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `class` enum('PUBLIC','PRIVATE','CONFIDENTIAL') NOT NULL DEFAULT 'PRIVATE',
  `categories` tinytext,
  `category_intern` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `location` tinytext,
  `ts` int(10) unsigned NOT NULL DEFAULT '0',
  `linterval` smallint(5) unsigned DEFAULT NULL,
  `sinterval` smallint(5) unsigned DEFAULT NULL,
  `wdays` varchar(7) DEFAULT NULL,
  `month` tinyint(3) unsigned DEFAULT NULL,
  `day` tinyint(3) unsigned DEFAULT NULL,
  `rtype` enum('SINGLE','DAILY','WEEKLY','MONTHLY','YEARLY') NOT NULL DEFAULT 'SINGLE',
  `duration` smallint(5) unsigned NOT NULL DEFAULT '0',
  `count` smallint(5) DEFAULT '0',
  `expire` int(10) unsigned NOT NULL DEFAULT '0',
  `exceptions` text,
  `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
  `chdate` int(10) unsigned NOT NULL DEFAULT '0',
  `importdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `uid_range` (`uid`,`range_id`),
  KEY `autor_id` (`autor_id`),
  KEY `range_id` (`range_id`,`class`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `comment_id` varchar(32) NOT NULL DEFAULT '',
  `object_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `object_id` (`object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conditionaladmissions`
--

DROP TABLE IF EXISTS `conditionaladmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conditionaladmissions` (
  `rule_id` varchar(32) NOT NULL,
  `message` text,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `conditions_stopped` tinyint(1) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conditionaladmissions`
--

LOCK TABLES `conditionaladmissions` WRITE;
/*!40000 ALTER TABLE `conditionaladmissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `conditionaladmissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `config_id` varchar(32) NOT NULL DEFAULT '',
  `parent_id` varchar(32) NOT NULL DEFAULT '',
  `field` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  `type` enum('boolean','integer','string','array') NOT NULL DEFAULT 'boolean',
  `range` enum('global','user') NOT NULL DEFAULT 'global',
  `section` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `message_template` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`config_id`),
  KEY `parent_id` (`parent_id`),
  KEY `field` (`field`,`range`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES ('7291d64d9cc4ea43ee9e8260f05a4111','','MAIL_NOTIFICATION_ENABLE','0',1,'boolean','global','',0,1122996278,1122996278,'Informationen ?ber neue Inhalte per email verschicken','',''),('9f6d7e248f58d1b211314dfb26c77d63','','RESOURCES_ALLOW_DELETE_REQUESTS','1',1,'boolean','global','resources',0,1136826903,1136826903,'Erlaubt das L?schen von Raumanfragen f?r globale Ressourcenadmins','',''),('25bdaf939c88ee79bf3da54165d61a48','','MAINTENANCE_MODE_ENABLE','0',1,'boolean','global','',0,1130840930,1130840930,'Schaltet das System in den Wartungsmodus, so dass nur noch Administratoren Zugriff haben','',''),('88c038ca4fb36764ff6486d72379e1ae','','ZIP_UPLOAD_MAX_FILES','100',1,'integer','global','files',0,1130840930,1130840930,'Die maximale Anzahl an Dateien, die bei einem Zipupload automatisch entpackt werden','',''),('c1f9ef95f501893c73e2654296c425f2','','ZIP_UPLOAD_ENABLE','1',1,'boolean','global','files',0,1130840930,1130840930,'Erm?glicht es, ein Zip Archiv hochzuladen, welches automatisch entpackt wird','',''),('d733eb0f9ef6db9fb3b461dd4df22376','','ZIP_UPLOAD_MAX_DIRS','10',1,'integer','global','files',0,1130840962,1130840962,'Die maximale Anzahl an Verzeichnissen, die bei einem Zipupload automatisch entpackt werden','',''),('1c07aa46c6fe6fea26d9b0cfd8fbcd19','','SENDFILE_LINK_MODE','normal',1,'string','global','files',0,1141212096,1141212096,'Format der Downloadlinks: normal=sendfile.php?parameter=x, old=sendfile.php?/parameter=x, rewrite=download/parameter/file.txt','',''),('9d4956b4eac20f03b60b17d7ac30b40a','','SEMESTER_TIME_SWITCH','0',1,'integer','global','',0,1140013696,1140013696,'Anzahl der Wochen vor Semesterende zu dem das vorgew?hlte Semester umspringt','',''),('06cdb765fb8f0853e3ebe08f51c3596e','','RESOURCES_ENABLE','0',1,'boolean','global','',0,0,0,'Enable the Stud.IP resource management module','',''),('3d415eca6003321f09e59407e4a7994d','','RESOURCES_LOCKING_ACTIVE','',1,'boolean','global','resources',0,0,1100709567,'Schaltet in der Ressourcenverwaltung das Blockieren der Bearbeitung f?r einen Zeitraum aus (nur Admins d?rfen in dieser Zeit auf die Belegung zugreifen)','',''),('b7a2817d142443245df2f5ac587fe218','','RESOURCES_ALLOW_ROOM_REQUESTS','1',1,'boolean','global','resources',0,0,1100709567,'Schaltet in der Ressourcenverwaltung das System zum Stellen und Bearbeiten von Raumanfragen ein oder aus','',''),('d821ffbff29ce636c6763ffe3fd8b427','','RESOURCES_ALLOW_CREATE_ROOMS','2',1,'integer','global','resources',0,0,1100709567,'Welche Rechstufe darf  R?ume anlegen? 1 = Nutzer ab Status tutor, 2 = Nutzer ab Status admin, 3 = nur Ressourcenadministratoren','',''),('5a6e2342b90530ed50ad8497054420c0','','RESOURCES_ALLOW_ROOM_PROPERTY_REQUESTS','1',1,'boolean','global','resources',0,0,1074780851,'Schaltet in der Ressourcenverwaltung die M?glichkeit, im Rahmen einer Anfrage Raumeigenschaften zu w?nschen, ein oder aus','',''),('e4123cf9158cd0b936144f0f4cf8dfa3','','RESOURCES_INHERITANCE_PERMS_ROOMS','1',1,'integer','global','resources',0,0,1100709567,'Art der Rechtevererbung in der Ressourcenverwaltung f?r R?ume: 1 = lokale Rechte der Einrichtung und Veranstaltung werden ?bertragen, 2 = nur Autorenrechte werden vergeben, 3 = es werden keine Rechte vergeben','',''),('45856b1e3407ce565d87ec9b8fd32d7d','','RESOURCES_INHERITANCE_PERMS','1',1,'integer','global','resources',0,0,1100709567,'Art der Rechtevererbung in der Ressourcenverwaltung f?r Ressourcen (nicht R?ume): 1 = lokale Rechte der Einrichtung und Veranstaltung werden ?bertragen, 2 = nur Autorenrechte werden vergeben, 3 = es werden keine Rechte vergeben','',''),('c353c73d8f37e3c301ae34898c837af4','','RESOURCES_ENABLE_ORGA_CLASSIFY','1',1,'boolean','global','resources',0,0,1100709567,'Schaltet in der Ressourcenverwaltung das Einordnen von Ressourcen in Orga-Struktur (ohne Rechtevergabe) ein oder aus','',''),('0821671742242add144595b1112399fb','','RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE','50',1,'integer','global','resources',0,0,1100709567,'Wert (in Prozent), ab dem ein Raum mit Einzelbelegungen (statt Serienbelegungen) gef?llt wird, wenn dieser Anteil an m?glichen Belegungen bereits durch andere Belegungen zu ?berschneidungen f?hrt','',''),('94d1643209a8f404dfe71228aad5345d','','RESOURCES_ALLOW_SINGLE_DATE_GROUPING','5',1,'integer','global','resources',0,0,1100709567,'Anzahl an Einzeltermine, ab der diese als Gruppe zusammengefasst bearbeitet werden','',''),('d29daf70897da5f89d44013260f26abd','','AJAX_AUTOCOMPLETE_DISABLED','0',1,'boolean','global','',0,1293118060,1293118060,'Sollen alle QuickSearches deaktiviertes Autocomplete haben? Wenn es zu Performanceproblemen kommt, kann es sich lohnen, diese Variable auf true zu stellen.','',''),('f2f8a47ea69ed9ccba5573e85a15662c','','ACCESSKEY_ENABLE','',1,'boolean','user','',0,0,0,'Schaltet die Nutzung von Shortcuts f?r einen User ein oder aus, Systemdefault','',''),('0b00c75bc76abe0dd132570403b38e5c','','NEWS_RSS_EXPORT_ENABLE','1',1,'boolean','global','',0,0,0,'Schaltet die M?glichkeit des rss-Export von privaten News global ein oder aus','',''),('42d237f9dfd852318cdc66319043536d','','FOAF_SHOW_IDENTITY','',1,'boolean','user','privacy',0,0,0,'Schaltet f?r einen User ein oder aus, ob dieser in FOAS-Ketten angezeigt wird, Systemdefault','',''),('6ae7aecf299930cbb8a5e89bbab4da55','','FOAF_ENABLE','1',1,'boolean','global','',0,0,0,'FOAF Feature benutzen?','',''),('a52e3b62ac0bee819b782d8979960b7b','','RESOURCES_ENABLE_GROUPING','1',1,'boolean','global','resources',0,0,1121861801,'Schaltet in der Ressourcenverwaltung die Funktion zur Verwaltung von Raumgruppen ein oder aus','',''),('76cac679fa57fdbb3f9d6cee20bf8c6f','','RESOURCES_ENABLE_SEM_SCHEDULE','1',1,'boolean','global','resources',0,0,0,'Schaltet in der Ressourcenverwaltung ein, ob ein Semesterbelegungsplan erstellt werden kann','',''),('3af783748f92cdf99b066d4227f8dffc','','RESOURCES_SEARCH_ONLY_REQUESTABLE_PROPERTY','1',1,'boolean','global','resources',0,0,0,'Schaltet in der Suche der Ressourcenverwaltun das Durchsuchen von nicht w?nschbaren Eigenschaften ein oder aus','',''),('fe498bb91a4cbfdfd5078915e979153c','','RESOURCES_ENABLE_VIRTUAL_ROOM_GROUPS','1',1,'boolean','global','resources',0,0,0,'Schaltet in der Ressourcenverwaltung automatische gebildete Raumgruppen neben per Konfigurationsdatei definierten Gruppen ein oder aus','',''),('68b127dde744085637d221e11d4e8cf2','','RESOURCES_ALLOW_CREATE_TOP_LEVEL','',1,'boolean','global','resources',0,0,0,'Schaltet f?r die Ressourcenverwaltung ein, ob neue Hierachieebenen von anderen Nutzern als Admins angelegt werden k?nnen oder nicht','',''),('b16359d5514b13794689eab669124c69','','ALLOW_DOZENT_VISIBILITY','',1,'boolean','global','permissions',0,0,0,'Schaltet ein oder aus, ob ein Dozent eigene Veranstaltungen selbst verstecken darf oder nicht','',''),('e8cd96580149cde65ad69b6cf18d5c39','','ALLOW_DOZENT_ARCHIV','',1,'boolean','global','permissions',0,0,1109946684,'Schaltet ein oder aus, ob ein Dozent eigene Veranstaltungen selbst archivieren darf oder nicht','',''),('24ecbeb431826c61fd8b53b3aa41bfa6','','SHOWSEM_ENABLE','1',1,'boolean','user','',0,1122461027,1122461027,'Einstellung f?r Nutzer, ob Semesterangaben in der ?bersicht \"Meine Veranstaltung\" nach dem Titel der Veranstaltung gemacht werden; Systemdefault','',''),('91e6e53b3748a53c42440453e8045be3','','RESOURCES_ALLOW_SEMASSI_SKIP_REQUEST','1',1,'boolean','global','resources',0,1122565305,1122565305,'Schaltet das Pflicht, eine Raumanfrage beim Anlegen einer Veranstaltung machen zu m?ssen, ein oder aus','',''),('f32367b1542a1d513ecee8a26e26d239','','RESOURCES_SCHEDULE_EXPLAIN_USER_NAME','1',1,'boolean','global','resources',0,1123516671,1123516671,'Schaltet in der Ressourcenverwaltung die Anzeige der Namen des Belegers in der Ausgabe von Belegungspl?nen ein oder aus','',''),('4c52bfa598daa03944a401b66c53d828','','NEWS_DISABLE_GARBAGE_COLLECT','0',1,'boolean','global','',0,1123751948,1123751948,'Schaltet den Garbage-Collect f?r News ein oder aus','',''),('9e0579653e585a688665a6ea2e2d7c90','','EVAL_AUSWERTUNG_CONFIG_ENABLE','1',1,'boolean','global','evaluation',0,1141225624,1141225624,'Erm?glicht es dem Nutzer, die grafische Darstellung der Evaluationsauswertung zu konfigurieren','',''),('0ad11a4cafa548d3c72a3dc1776568d8','','EVAL_AUSWERTUNG_GRAPH_FORMAT','png',1,'string','global','evaluation',0,1141225624,1141225624,'Das Format, in dem die Diagramme der grafischen Evaluationsauswertung erstellt werden (jpg, png, gif).','',''),('781e0998a1b5c998ebbc02a4f0d907ac','','USER_VISIBILITY_UNKNOWN','1',1,'boolean','global','privacy',0,1153815901,1153815901,'Sollen Nutzer mit Sichtbarkeit \"unknown\" wie sichtbare behandelt werden?','',''),('819e4437029e6734fa04e8fa239c5e01','','MAILQUEUE_ENABLE','0',1,'boolean','global','global',0,1403258017,1403258017,'Aktiviert bzw. deaktiviert die Mailqueue','',''),('54ad03142e6704434976c9a0df8329c8','','ONLINE_NAME_FORMAT','full_rev',1,'string','user','',0,1153814980,1153814980,'Default-Wert f?r wer-ist-online Namensformatierung','',''),('8a147b2d487d7ae91264f03cab5d8c07','','ADMISSION_PRELIM_COMMENT_ENABLE','0',1,'boolean','global','',0,1153814966,1153814966,'Schaltet ein oder aus, ob ein Nutzer im Modus \"Vorl?ufiger Eintrag\" eine Bemerkung hinterlegen kann','',''),('a93eb21bb08719b3a522b7e238bd8b7e','','EXTERNAL_HELP','1',1,'boolean','global','',0,1155128579,1155128579,'Schaltet das externe Hilfesystem ein','',''),('10367c279370c7f78552d2747c2b169c','','EXTERNAL_HELP_LOCATIONID','default',1,'string','global','',0,1155128579,1155128579,'Eine eindeutige ID zur Identifikation der gew?nschten Hilfeseiten, leer bedeutet Standardhilfe','',''),('6679a9cf02e56c0fce92e91b8f696005','','EXTERNAL_HELP_URL','http://hilfe.studip.de/index.php/%s',1,'string','global','',0,1155128579,1155128579,'URL Template f?r das externe Hilfesystem','',''),('4cd2cd3cc207ffc0ae92721c291cd906','','RESOURCES_SHOW_ROOM_NOT_BOOKED_HINT','0',1,'boolean','global','resources',0,1168444600,1168444600,'Einstellung, ob bei aktivierter Raumverwaltung Raumangaben die nicht gebucht sind gekennzeichnet werden','',''),('3b6a1623b8e0913430d6a27bfda976fd','','ADMISSION_ALLOW_DISABLE_WAITLIST','1',1,'boolean','global','',0,1170242650,1170242650,'Schaltet ein oder aus, ob die Warteliste in Zugangsbeschr?nkten Veranstaltungen deaktiviert werden kann','',''),('08f085d9ef2ee7d8b355dcc35282ab8c','','ENABLE_SKYPE_INFO','1',1,'boolean','global','privacy',0,1170242666,1170242666,'Erm?glicht die Eingabe / Anzeige eines Skype Namens ','',''),('615e92cdf78c1436c3fc1f60a8cd944e','','SEM_VISIBILITY_PERM','root',1,'string','global','permissions',0,1170242706,1170242706,'Bestimmt den globalen Nutzerstatus, ab dem versteckte Veranstaltungen in der Suche gefunden werden (root,admin,dozent)','',''),('4158d433b57052b20fd66d84b71c7324','','SEM_CREATE_PERM','dozent',1,'string','global','permissions',0,1170242930,1170242930,'Bestimmt den globalen Nutzerstatus, ab dem Veranstaltungen angelegt werden d?rfen (root,admin,dozent)','',''),('93da66ca9e2d17df5bc61bd56406add7','','RESOURCES_ROOM_REQUEST_DEFAULT_ACTION','NO_ROOM_INFO_ACTION',1,'string','global','resources',0,0,0,'Designates the pre-selected action for the room request dialog','Valid values are: NO_ROOM_INFO_ACTION, ROOM_REQUEST_ACTION, BOOKING_OF_ROOM_ACTION, FREETEXT_ROOM_ACTION',''),('0d3f84ed4dd6b7147b504ffb5b6fbc2c','','RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW','0',1,'boolean','global','resources',0,12,12,'Enables the expert view of the course schedules','',''),('bc3004618b17b29dc65e10e89be9a7a0','','RESOURCES_ENABLE_BOOKINGSTATUS_COLORING','1',1,'boolean','global','resources',0,0,0,'Enable the colored presentation of the room booking status of a date','',''),('cb92d5bb08f346567dbd394d0d553454','','EMAIL_DOMAIN_RESTRICTION','',1,'string','global','',0,1157107088,1157107088,'Beschr?nkt die g?ltigkeit von Email-Adressen bei freier Registrierung auf die angegebenen Domains. Komma-separierte Liste von Domains ohne vorangestelltes @.','',''),('791c632089d80d63bf910e661e378fca','','MAIL_AS_HTML','0',1,'boolean','user','',0,1293118060,1293118060,'Benachrichtigungen werden im HTML-Format versandt','',''),('3acf297f781b0c0aefd551ec304b902d','','DOCUMENTS_EMBEDD_FLASH_MOVIES','deny',1,'string','global','files',0,1157107088,1157107088,'Sollen im Dateibereich Flash-Filme direkt in einem Player angezeigt werden? deny=nicht erlaubt, allow=erlaubt, autoload=Film wird beim aufklappen geladen (incrementiert Downloads), autoplay=Film wird sofort abgespielt','',''),('e2d53231d99575a728cb84b0defc3569','','ZIP_DOWNLOAD_MAX_FILES','100',1,'integer','global','files',0,1219328498,1219328498,'Die maximale Anzahl an Dateien, die gezippt heruntergeladen werden kann','',''),('be1cd744e51e87d8c0c1cb9a6c171887','','ZIP_DOWNLOAD_MAX_SIZE','100',1,'integer','global','files',0,1219328498,1219328498,'Die maximale Gr??e aller Dateien, die zusammen in einem Zip heruntergeladen werden kann (in Megabytes).','',''),('b8faa6e4bdb8ec8d095f5ea1d04950c0','','RANGE_TREE_ADMIN_PERM','admin',1,'string','global','permissions',0,1219328498,1219328498,'mit welchem Status darf die Einrichtungshierarchie bearbeitet werden (admin oder root)','',''),('e0b4d32bd6da9a430c2644bd5ea3ab3b','','SEM_TREE_ADMIN_PERM','admin',1,'string','global','permissions',0,1219328498,1219328498,'mit welchem Status darf die Veranstaltungshierarchie bearbeitet werden (admin oder root)','',''),('fb5d5b3f1a0b70ded3c5770f30ec2ac1','','SEMESTER_ADMINISTRATION_ENABLE','1',1,'boolean','global','',0,1219328498,1219328498,'schaltet die Semesterverwaltung ein oder aus','',''),('34f348c06bbd5d9fc7bb36a8d829e12e','','SEM_TREE_ALLOW_BRANCH_ASSIGN','1',1,'boolean','global','',0,1222947575,1222947575,'Diese Option beeinflusst die M?glichkeit, Veranstaltungen entweder nur an die Bl?tter oder ?berall in der Veranstaltungshierarchie einh?ngen zu d?rfen.','',''),('b213cfe252348f6934b3be424d97eebf','','MESSAGE_PRIORITY','0',1,'boolean','global','',0,1240427632,1240427632,'If enabled, messages of high priority are displayed reddish','',''),('9212fb9cfd117f462c36b276838493f2','','RESTRICTED_USER_MANAGEMENT','0',1,'boolean','global','permissions',0,1240427632,1240427632,'Schr?nkt Zugriff auf die globale Nutzerverwaltung auf root ein','',''),('664fba27a9f0d8be2a68db04ecc919e1','','AUX_RULE_ADMIN_PERM','admin',1,'string','global','permissions',0,1240427632,1240427632,'mit welchem Status d?rfen Zusatzangaben definiert werden (admin, root)','',''),('759849f5dbc68a74df6905a8187ee59a','','LOCK_RULE_ADMIN_PERM','admin',1,'string','global','permissions',0,1240427632,1240427632,'mit welchem Status d?rfen Sperrebenen angepasst werden (admin, root)','',''),('62ccc1c5408de882f38de933cecaf88f','','ALLOW_SELFASSIGN_INSTITUTE','1',1,'boolean','global','permissions',0,1240427632,1240427632,'Wenn eingeschaltet, d?rfen Studenten sich selbst Einrichtungen an denen sie studieren zuordnen.','',''),('7fc613594ac8a9f44b6280c6dd3d653e','','ALLOW_ADMIN_USERACCESS','1',1,'boolean','global','permissions',0,1240427632,1240427632,'Wenn eingeschaltet, d?rfen Administratoren sensible persönliche Angaben wie z.B. Passw?rter ?ndern.','',''),('cd1ba192616b66967b51f8e8f8155fb5','','SEM_TREE_SHOW_EMPTY_AREAS_PERM','user',1,'string','global','permissions',0,1240427632,1240427632,'Bestimmt den globalen Nutzerstatus, ab dem in der Veranstaltungssuche auch Bereiche angezeigt werden, denen keine Veranstaltungen zugewiesen sind.','',''),('06d703c3de37cdae942c66e18f7dcd02','','ASSI_SEMESTER_PRESELECT','1',1,'boolean','global','',0,1257956185,1257956185,'Wenn ausgeschaltet wird im admin_seminare_assi beimErstellen einer Veranstaltung als Semester bitte ausw?hlen angezeigt und nicht das voreingestellte Semester.','',''),('274e61e0b19ab9edadb4be9764aa17a2','','RESOURCES_HIDE_PAST_SINGLE_DATES','1',1,'boolean','global','resources',0,1257956185,1257956185,'Schaltet in der Ressourcenverwaltung ein,ob bereits vergangene Terminen bei der Buchung und Planung br?cksichtigt werden sollen','',''),('64031f867c3688ecf1c697bd79eff230','','RESOURCES_ALLOW_ROOM_REQUESTS_ALL_ROOMS','1',1,'boolean','global','resources',0,1257956185,1257956185,'Schaltet in der Ressourcenverwaltung ein,ob alle R?ume gew?nscht werden k?nnen, oder nur eigene und \'Global\' gesetzte','',''),('f2ce635d994363fad26e010f5dbae2c4','','STUDYGROUPS_ENABLE','0',1,'boolean','global','studygroups',0,1257956185,1293118059,'Schaltet ein oder aus, ob die Studiengruppen global verf?gbar sind.','',''),('ebe2dd4ffa77bb1966c5940272d7c8dd','','STUDYGROUP_TERMS','Mir ist bekannt, dass ich die Gruppe nicht zu rechtswidrigen Zwecken nutzen darf. Dazu z?hlen u.a. Urheberrechtsverletzungen, Beleidigungen und andere Pers?nlichkeitsdelikte.\n\nIch erkl?re mich damit einverstanden, dass Admins die Inhalte der Gruppe zu Kontrollzwecken einsehen d?rfen.',1,'string','global','studygroups',0,1257956185,1257956185,'Hier werden die Nutzungsbedinungen der Studiengruppen hinterlegt.','',''),('678424efcbb858b401edbb5f98b43dac','','HOMEPAGEPLUGIN_DEFAULT_ACTIVATION','1',1,'boolean','global','privacy',0,1403258014,1403258014,'Sollen neu installierte Homepageplugins automatisch f?r Benutzer aktiviert sein?','',''),('da0a01b50f472296f940de98c56b5a9f','','STUDYGROUP_DEFAULT_INST','',1,'string','global','studygroups',0,1258042892,1258042892,'Die Standardeinrichtung f?r Studiengruppen kann hier gesetzt werden.','',''),('e776a479dc024fb9b6478be3f60455da','','ENABLE_PROTECTED_DOWNLOAD_RESTRICTION','0',1,'boolean','global','files',0,1257956185,1257956185,'Schaltet die ?berpr?fung (fester Teilnehmerkreis) bei Download von als gesch?tzt markierten Dateien ein','',''),('9e601ed3a973474c0aac3d7a18c5bf02','','DOZENT_ALWAYS_VISIBLE','1',1,'boolean','global','privacy',0,1293118059,1293118059,'Legt fest, ob Personen mit Dozentenrechten immer global sichtbar sind und das auch nicht selbst ?ndern k?nnen.','',''),('80ccace5c451cec67d88da1edb4b5eec','','HOMEPAGE_VISIBILITY_DEFAULT','VISIBILITY_STUDIP',1,'string','global','privacy',0,1293118059,1293118059,'Standardsichtbarkeit f?r Homepageelemente, falls der Benutzer nichts anderes eingestellt hat. G?ltige Werte sind: VISIBILITY_ME, VISIBILITY_BUDDIES, VISIBILITY_DOMAIN, VISIBILITY_STUDIP, VISIBILITY_EXTERN','',''),('1115645eb5439f998a8cc6f4a3a9dccf','','FORUM_ANONYMOUS_POSTINGS','0',1,'boolean','global','privacy',0,1293118059,1293118059,'Legt fest, ob Forenbeitr?ge anonym verfasst werden d?rfen (Root sieht aber immer den Urheber).','',''),('d1eef8315d340d78d7eca557139937f1','','INST_FAK_ADMIN_PERMS','all',1,'string','global','permissions',0,1293118059,1293118059,'\"none\" Fakult?tsadmin darf Einrichtungen weder anlegen noch l?schen, \"create\" Fakult?tsadmin darf Einrichtungen anlegen, aber nicht l?schen, \"all\" Fakult?tsadmin darf Einrichtungen anlegen und l?schen.','',''),('45e7816a1bfb1521e7a1d6311a964b00','','CALENDAR_ENABLE','1',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob der Kalender global verf?gbar ist.','',''),('be154136cfaf6ed1c753a6cc26c2ec66','','EXPORT_ENABLE','1',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob der Export global verf?gbar ist.','',''),('8481ee7faec9805d577de600a0835bc3','','EXTERN_ENABLE','1',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob die externen Seiten global verf?gbar sind.','',''),('ded48fe2ca250e104dc33fb6f0b42c67','','VOTE_ENABLE','1',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob die Umfragen global verf?gbar sind.','',''),('46a02bfefc7b643a1ea40b7e10d4c037','','ELEARNING_INTERFACE_ENABLE','0',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob die Lernmodule global verf?gbar sind.','',''),('55241293c1a418caa62c72af8b3c2506','','MY_COURSES_ENABLE_STUDYGROUPS','0',0,'boolean','global','MeineVeranstaltungen',0,1416496224,1416496224,'Sollen Studiengruppen in einem eigenen Bereich angezeigt werden (Neues Navigationelement in Meine Veranstaltungen)?.','',''),('ad82e1d6bd97320244362974c460bc7d','','WIKI_ENABLE','1',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob das Wiki global verf?gbar ist.','',''),('250306a36a09d6465ee3fd24c66932f4','','SMILEYADMIN_ENABLE','1',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob die Administration der Smileys verf?gbar ist.','',''),('bdc9e8e9f5174f3ef668a87adef906eb','','LOG_ENABLE','1',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob das Log global verf?gbar ist.','',''),('826a49fd9f9aaea20e85adbad9b095af','','SCM_ENABLE','1',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob freie Informationsseiten global verf?gbar sind.','',''),('edc2bf96acd7bac3bf923a71a12abb13','','BANNER_ADS_ENABLE','0',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob die Bannerwerbung global verf?gbar ist.','',''),('0109868144360b658367d61aa6f16906','','LITERATURE_ENABLE','1',1,'boolean','global','modules',0,1293118059,1293118059,'Schaltet ein oder aus, ob die Literaturverwaltung global verf?gbar ist.','',''),('0faeba67819f6cc2016f4a5b017c36f6','','MY_COURSES_FORCE_GROUPING','sem_number',1,'string','global','',0,1293118059,1293118059,'Legt fest, ob die pers?nliche Veranstaltungs?bersicht systemweit zwangsgruppiert werden soll, wenn keine eigene Gruppierung eingestellt ist. Werte: not_grouped, sem_number, sem_tree_id, sem_status, gruppe, dozent_id.','',''),('e274953d097e32023b477d1ad895dfc8','','DEPUTIES_ENABLE','0',1,'boolean','global','deputies',0,1293118059,1293118059,'Legt fest, ob die Funktion Vertretung aktiviert ist.','',''),('18b2970268e2143f8ee142cbadf38de5','','DEPUTIES_DEFAULTENTRY_ENABLE','0',1,'boolean','global','deputies',0,1293118059,1293118059,'Dürfen Lehrende Standardvertretungen festlegen? Diese werden automatisch bei Hinzufügen der Lehrenden als Vertretung in Veranstaltungen eingetragen.','',''),('8e596c37d6a921e38de1576eca50d5b6','','DEPUTIES_EDIT_ABOUT_ENABLE','1',1,'boolean','global','deputies',0,1293118059,1293118059,'Dürfen Lehrende ihren Standardvertretungen erlauben, ihr Profil zu bearbeiten?','',''),('dada821b887f0fca1a45e9e339a5aff7','','FILESYSTEM_MULTICOPY_ENABLE','1',1,'boolean','global','',0,1293118059,1293118059,'Soll es erlaubt sein, das Dozenten Ordner oder Dateien in mehrere Veranstaltungen bzw. Institute verschieben oder kopieren d?rfen?','',''),('dc99e1c9623b6971629a304f174cd3b0','','ALLOW_METADATE_SORTING','0',1,'boolean','global','permissions',0,1293118059,1293118059,'Soll es erlaubt sein, dass regelm??ige Zeiten einer Veranstaltung frei sortiert werden k?nnen?','',''),('c304d6f383297f9506d7acf7ee42c460','','LOAD_EXTERNAL_MEDIA','deny',1,'string','global','',0,1293118060,1293118060,'Sollen externe Medien ?ber [img/flash/audio/video] eingebunden werden? deny=nicht erlaubt, allow=erlaubt, proxy=proxy benutzen.','',''),('ece3432a2267af28f4e7729d549ca9ea','','ENTRIES_PER_PAGE','20',1,'integer','global','global',0,1311411856,1311411856,'Anzahl von Eintr?gen pro Seite','',''),('ce272afc2347f03fcbc69568f8a0098a','','PDF_LOGO','',1,'string','global','global',0,1311411856,1311411856,'Geben Sie hier den absoluten Pfad auf Ihrem Server (also ohne http) zu einem Logo an, das bei PDF-Exporten im Kopfbereich verwendet wird.','',''),('86bbdf3b3ac03a5ad6be7c3c7cd402d4','','AUTO_INSERT_SEM_PARTICIPANTS_VIEW_PERM','tutor',1,'string','global','global',0,1311411856,1311411856,'Ab welchem Status soll in Veranstaltungen mit automatisch eingetragenen Nutzern der Teilnehmerreiter zu sehen sein?','',''),('f2469d9595be17d9ef4dd8b45b51f794','','SKIPLINKS_ENABLE','',1,'boolean','user','privacy',0,1311411856,1311411856,'W?hlen Sie diese Option, um Skiplinks beim ersten Dr?cken der Tab-Taste anzuzeigen (Systemdefault).','',''),('e4983898ecf465efde62b32ffdc9b12b','','CRONJOBS_ENABLE','1',1,'boolean','global','global',0,1403258015,1403258015,'Schaltet die Cronjobs an','',''),('b9f480020414ca0d41b3e8844b8af5e7','','CRONJOBS_ESCALATION','6000',1,'integer','global','global',0,1403258015,1403258015,'Gibt an, nach wievielen Sekunden ein Cronjob als steckengeblieben angesehen wird','',''),('7f6084adb44da8c24647109544db4d1d','','EMAIL_VISIBILITY_DEFAULT','1',1,'boolean','global','privacy',0,1326799691,1326799691,'Ist die eigene Emailadresse sichtbar, falls der Nutzer nichts anderes eingestellt hat?','',''),('5591ed8d2ff2afeb186d5f788cbb7e2f','','ONLINE_VISIBILITY_DEFAULT','1',1,'boolean','global','privacy',0,1326799691,1326799691,'Sind Nutzer sichtbar in der Wer ist online-Liste, falls sie nichts anderes eingestellt haben?','',''),('94bca8fa4c2ad851ddaf26df8b7ec1d3','','SEARCH_VISIBILITY_DEFAULT','1',1,'boolean','global','privacy',0,1326799691,1326799691,'Sind Nutzer auffindbar in der Personensuche, falls sie nichts anderes eingestellt haben?','',''),('e414e0834adc8c2815787da81ed29a51','','PROPOSED_TEACHER_LABELS','',1,'string','global','global',0,1326799692,1326799692,'Write a list of comma separated possible labels for teachers and tutor here.','',''),('88a3a312b783c9d890b77e84a51853fa','','SCHEDULE_ENABLE','1',1,'boolean','global','modules',0,1326799692,1326799692,'Schaltet ein oder aus, ob der Stundenplan global verf?gbar ist.','',''),('4a38babf107742cf71c8f7d23fea2c9a','','CALENDAR_GROUP_ENABLE','0',1,'boolean','global','modules',0,1326799692,1326799692,'Schaltet die Gruppenterminkalender-Funktionen ein.','',''),('d7332ad7fba147d81bd1ccae6211284f','','COURSE_CALENDAR_ENABLE','0',1,'boolean','global','modules',0,1326799692,1326799692,'Kalender als Inhaltselement in Veranstaltungen.','',''),('2034a3eb8484cc4744fddf65767674cd','','PERSONAL_NOTIFICATIONS_ACTIVATED','1',1,'boolean','global','privacy',0,1403258015,1403258015,'Sollen pers?nliche Benachrichtigungen aktiviert sein?','',''),('ae429c21b4ce1c354745e52ef671f1ff','','CALENDAR_SETTINGS','{\"view\":\"showweek\",\"start\":9,\"end\":20,\"step_day\":900,\"step_week\":3600,\"type_week\":\"LONG\",\"holidays\":true,\"sem_data\":true,\"delete\":0}',1,'array','user','',0,1403258015,1403258015,'pers?nliche Einstellungen des Kalenders','',''),('d924152d8c38aca6a6363eb2a4f51ecb','','MESSAGING_SETTINGS','{\"show_only_buddys\":false,\"delete_messages_after_logout\":false,\"timefilter\":\"30d\",\"opennew\":1,\"logout_markreaded\":false,\"openall\":false,\"addsignature\":false,\"save_snd\":true,\"sms_sig\":\"\",\"send_view\":false,\"confirm_reading\":3,\"send_as_email\":false,\"folder\":{\"in\":[\"dummy\"],\"out\":[\"dummy\"]}}',1,'array','user','',0,1403258015,1403258015,'pers?nliche Einstellungen Nachrichtenbereich','',''),('135bc61587772882a8dba504b5aa9f05','','FORUM_SETTINGS','{\"neuauf\":false,\"rateallopen\":true,\"showimages\":true,\"sortthemes\":\"last\",\"themeview\":\"mixed\",\"presetview\":\"mixed\",\"shrink\":604800}',1,'array','user','',0,1403258015,1403258015,'pers?nliche Einstellungen Forum','',''),('1f284b41a375d9e9707a3af416d03a0e','','SCHEDULE_SETTINGS','{\"glb_start_time\":8,\"glb_end_time\":19,\"glb_days\":{\"1\":1,\"2\":2,\"3\":3,\"4\":4,\"5\":5,\"6\":6,\"0\":0},\"glb_sem\":null,\"converted\":true}',1,'array','user','',0,1403258015,1403258015,'pers?nliche Einstellungen Stundenplan','',''),('062039db1a9873d253ec8bb8ac956166','','PROFILE_LAST_VISIT','0',1,'integer','user','',0,1403258015,1403258015,'Zeitstempel des letzten Besuchs der Profilseite','',''),('8f794863e1e9f6940eea8030a75f2da1','','CURRENT_LOGIN_TIMESTAMP','0',1,'integer','user','',0,1403258015,1403258015,'Zeitstempel des Logins','',''),('12721c47c36f3b0f91cea4b51350530d','','LAST_LOGIN_TIMESTAMP','0',1,'integer','user','',0,1403258015,1403258015,'Zeitstempel des vorherigen Logins','',''),('04d58f9f2944cdfb31452aa9fc5baf9c','','PERSONAL_STARTPAGE','0',1,'integer','user','',0,1403258015,1403258015,'Pers?nliche Startseite','',''),('ff78074546ad3589c1a25fa0d06c0e9a','','MY_COURSES_GROUPING','',1,'string','user','',0,1403258015,1403258015,'Gruppierung der Veranstaltungs?bersicht','',''),('859f6640b59468bee5a11084feb34d54','','MY_COURSES_OPEN_GROUPS','[]',1,'array','user','',0,1403258015,1403258015,'ge?ffnete Gruppen der Veranstaltungs?bersicht','',''),('78da757c0e141b11cfda80126cc56c40','','MY_INSTITUTES_DEFAULT','',1,'string','user','',0,1403258015,1403258015,'Standard Einrichtung in der Veranstaltungs?bersicht f?r Admins','',''),('b8215b4d5a8b5646b07ee86612b5104f','','OPENGRAPH_ENABLE','1',1,'boolean','global','global',0,1403258018,1403258018,'De-/Aktiviert OpenGraph-Informationen und deren Abrufen.','',''),('12d6f9ae68ece3eb2f8f60099fc66655','','IMPORTANT_SEMNUMBER','0',1,'boolean','global','global',0,1403258018,1403258018,'Zeigt die Veranstaltungsnummer prominenter in der Suche und auf der Meine Veranstaltungen Seite an','',''),('d819c9eb9d740c65fae6b84b4fb8e796','','STUDYGROUPS_INVISIBLE_ALLOWED','0',1,'boolean','global','studygroups',0,1403258018,1403258018,'Erm?glicht unsichtbare Studiengruppen','',''),('24b3fd4877137b9f1eb59483812b620f','','API_ENABLED','0',1,'boolean','global','global',0,1403258019,1403258019,'Schaltet die REST-API an','',''),('a1872a693290df30144391ef6e0ea4be','','API_OAUTH_AUTH_PLUGIN','Standard',1,'string','global','global',0,1403258019,1403258019,'Definiert das f?r OAuth verwendete Authentifizierungsverfahren','',''),('18a9792e58d6b4a4badc0f383e29a22f','','ALLOW_DOZENT_COURSESET_ADMIN','0',1,'boolean','global','coursesets',0,1403258021,1403258021,'Sollen Lehrende einrichtungsweite Anmeldesets anlegen und bearbeiten d?rfen?','',''),('38328e236221a3f14ddaee121e30e48a','','ENABLE_COURSESET_FCFS','0',1,'boolean','global','coursesets',0,1403258021,1403258021,'Soll first-come-first-served (Windhundverfahren) bei der Anmeldung erlaubt sein?','',''),('e8cd96580149cde65ad69b6cf18d5c4A','','WYSIWYG','0',1,'boolean','global','global',0,1403258021,1403258021,'Aktiviert den WYSIWYG Editor im JavaScript.','',''),('e6b6b8be6caf8abf0904c29e30e9b129','','SCORE_ENABLE','1',1,'boolean','global','modules',0,1403258021,1403258021,'Schaltet ein oder aus, ob die Rangliste und die Score-Funktion global verf?gbar sind.','',''),('aff56f98d59aabf19d82b38e06487b1a','','TOURS_ENABLE','1',1,'boolean','global','global',0,1416496223,1416496223,'Aktiviert die Funktionen zum Anbieten von Touren in Stud.IP','',''),('3a7d9b0a0c876fd30594637dd7c070ec','','MY_COURSES_ENABLE_ALL_SEMESTERS','0',0,'boolean','global','MeineVeranstaltungen',0,1416496224,1416496224,'Erm?glicht die Anzeige von allen Semestern unter meine Veranstaltungen.','',''),('8aea17b0efc1ea0f2398c9994dacbf6b','','PERSONALDOCUMENT_ENABLE','0',1,'boolean','global','files',0,1416496270,1416496270,'Aktiviert den persoenlichen Dateibereich','',''),('c2ccf01a7f229f94b76cd37ec9360a7d','','COURSE_SEM_TREE_DISPLAY','0',1,'boolean','global','global',0,1416496270,1416496270,'Zeigt den Studienbereichsbaum als Baum an','',''),('c0c57c05aebfb9b54953bea7948a0578','','COURSE_SEM_TREE_CLOSED_LEVELS','[1]',1,'array','global','global',0,1416496270,1416496270,'Gibt an, welche Ebenen der Studienbereichszuordnung geschlossen bleiben sollen','',''),('008df4d1780faecb957bbee3d3140918','','HELP_CONTENT_CURRENT_VERSION','3.1',1,'string','global','global',0,1416496271,1416496271,'Aktuelle Version der Helpbar-Eintr?ge in Stud.IP','',''),('48f849a4927f8ac5231da5352076f16a','','STUDYGROUPS_ENABLE','1',0,'boolean','global','',0,1268739461,1268739461,'Studiengruppen','Studiengruppen',''),('9f1c998d46f55ac38da3a53072a4086b','','STUDYGROUP_DEFAULT_INST','ec2e364b28357106c0f8c282733dbe56',0,'string','global','',0,1268739461,1268739461,'Studiengruppen','',''),('bcd4820eebd8e027cef91bc761ab9a75','','STUDYGROUP_TERMS','Mir ist bekannt, dass ich die Gruppe nicht zu rechtswidrigen Zwecken nutzen darf. Dazu z?hlen u.a. Urheberrechtsverletzungen, Beleidigungen und andere Pers?nlichkeitsdelikte.\r\n\r\nIch erkl?re mich damit einverstanden, dass Admins die Inhalte der Gruppe zu Kontrollzwecken einsehen d?rfen.',0,'string','global','',0,1268739461,1268739461,'Studiengruppen','',''),('ba2ca9978d2178f24bbea87469692c84','','STUDIP_SHORT_NAME','Stud.IP',1,'string','global','global',0,1417519749,1417519749,'Studip Kurzname','','');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `contact_id` varchar(32) NOT NULL DEFAULT '',
  `owner_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `buddy` tinyint(4) NOT NULL DEFAULT '1',
  `calpermission` tinyint(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`contact_id`),
  KEY `owner_id` (`owner_id`,`buddy`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_userinfo`
--

DROP TABLE IF EXISTS `contact_userinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_userinfo` (
  `userinfo_id` varchar(32) NOT NULL DEFAULT '',
  `contact_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userinfo_id`),
  KEY `contact_id` (`contact_id`),
  KEY `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_userinfo`
--

LOCK TABLES `contact_userinfo` WRITE;
/*!40000 ALTER TABLE `contact_userinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_userinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coursememberadmissions`
--

DROP TABLE IF EXISTS `coursememberadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coursememberadmissions` (
  `rule_id` varchar(32) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `course_id` varchar(32) NOT NULL DEFAULT '',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coursememberadmissions`
--

LOCK TABLES `coursememberadmissions` WRITE;
/*!40000 ALTER TABLE `coursememberadmissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `coursememberadmissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courseset_factorlist`
--

DROP TABLE IF EXISTS `courseset_factorlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courseset_factorlist` (
  `set_id` varchar(32) NOT NULL,
  `factorlist_id` varchar(32) NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`factorlist_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courseset_factorlist`
--

LOCK TABLES `courseset_factorlist` WRITE;
/*!40000 ALTER TABLE `courseset_factorlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `courseset_factorlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courseset_institute`
--

DROP TABLE IF EXISTS `courseset_institute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courseset_institute` (
  `set_id` varchar(32) NOT NULL,
  `institute_id` varchar(32) NOT NULL,
  `mkdate` int(11) DEFAULT NULL,
  `chdate` int(11) DEFAULT NULL,
  PRIMARY KEY (`set_id`,`institute_id`),
  KEY `institute_id` (`institute_id`,`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courseset_institute`
--

LOCK TABLES `courseset_institute` WRITE;
/*!40000 ALTER TABLE `courseset_institute` DISABLE KEYS */;
/*!40000 ALTER TABLE `courseset_institute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courseset_rule`
--

DROP TABLE IF EXISTS `courseset_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courseset_rule` (
  `set_id` varchar(32) NOT NULL,
  `rule_id` varchar(32) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `mkdate` int(11) DEFAULT NULL,
  PRIMARY KEY (`set_id`,`rule_id`),
  KEY `type` (`set_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courseset_rule`
--

LOCK TABLES `courseset_rule` WRITE;
/*!40000 ALTER TABLE `courseset_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `courseset_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coursesets`
--

DROP TABLE IF EXISTS `coursesets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coursesets` (
  `set_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `infotext` text NOT NULL,
  `algorithm` varchar(255) NOT NULL,
  `algorithm_run` tinyint(1) NOT NULL DEFAULT '0',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`),
  KEY `set_user` (`user_id`,`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coursesets`
--

LOCK TABLES `coursesets` WRITE;
/*!40000 ALTER TABLE `coursesets` DISABLE KEYS */;
/*!40000 ALTER TABLE `coursesets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cronjobs_logs`
--

DROP TABLE IF EXISTS `cronjobs_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cronjobs_logs` (
  `log_id` char(32) NOT NULL DEFAULT '',
  `schedule_id` char(32) NOT NULL DEFAULT '',
  `scheduled` int(11) unsigned NOT NULL,
  `executed` int(11) unsigned NOT NULL,
  `exception` text,
  `output` text,
  `duration` float NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `schedule_id` (`schedule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cronjobs_logs`
--

LOCK TABLES `cronjobs_logs` WRITE;
/*!40000 ALTER TABLE `cronjobs_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `cronjobs_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cronjobs_schedules`
--

DROP TABLE IF EXISTS `cronjobs_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cronjobs_schedules` (
  `schedule_id` char(32) NOT NULL DEFAULT '',
  `task_id` char(32) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(4096) DEFAULT NULL,
  `parameters` text,
  `priority` enum('low','normal','high') NOT NULL DEFAULT 'normal',
  `type` enum('periodic','once') NOT NULL DEFAULT 'periodic',
  `minute` tinyint(2) DEFAULT NULL,
  `hour` tinyint(2) DEFAULT NULL,
  `day` tinyint(2) DEFAULT NULL,
  `month` tinyint(2) DEFAULT NULL,
  `day_of_week` tinyint(1) unsigned DEFAULT NULL,
  `next_execution` int(11) unsigned NOT NULL DEFAULT '0',
  `last_execution` int(11) unsigned DEFAULT NULL,
  `last_result` text,
  `execution_count` bigint(20) unsigned NOT NULL DEFAULT '0',
  `mkdate` int(11) unsigned NOT NULL,
  `chdate` int(11) unsigned NOT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `task_id` (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cronjobs_schedules`
--

LOCK TABLES `cronjobs_schedules` WRITE;
/*!40000 ALTER TABLE `cronjobs_schedules` DISABLE KEYS */;
INSERT INTO `cronjobs_schedules` VALUES ('3eb6cd006b1d27ab3dfd812c17d90f38','532b3fe76447dd85e10949a6fc5f3aa8',0,NULL,'','{\"cronjobs\":\"1\",\"cronjobs-success\":\"7\",\"cronjobs-error\":\"14\"}','normal','periodic',13,2,NULL,NULL,NULL,0,NULL,NULL,0,1403258015,1403258107),('dc849ba21c484ffbb82f7ef9edea3d7d','208619e89a59895771c2967076daf59e',0,NULL,NULL,'[]','low','periodic',-30,NULL,NULL,NULL,NULL,0,NULL,NULL,0,1403258015,1403258015),('f048bf3c13bfdb2a2a17ce867903ca0e','d19f37c382fec524b4fd51b3c5a1ada3',0,NULL,NULL,'[]','high','periodic',7,1,NULL,NULL,NULL,0,NULL,NULL,0,1403258015,1403258015),('6eef46d414b104b153402be299e16515','2f2713671892bd9624fc27866cfd4630',0,NULL,'','{\"verbose\":\"1\",\"send_messages\":\"1\"}','normal','periodic',-30,NULL,NULL,NULL,NULL,0,NULL,NULL,0,1403258015,1403258130),('cdf293c6c5ae966d87dc5ee723d9880d','823875ed4a4b2e87baca0e5137243d96',0,NULL,'','{\"verbose\":\"1\"}','normal','periodic',33,2,NULL,NULL,NULL,0,NULL,NULL,0,1403258015,1403258146),('dfd35e23a8256fee930e2e748cd53f1d','3428a64935e8c6a5ab5dcf5bf95fe556',0,NULL,NULL,'[]','normal','periodic',13,3,NULL,NULL,NULL,0,NULL,NULL,0,1403258015,1403258015),('81411d712690ab3a82032439dbcdc8c1','9c4ad2a8fe47d07e61475d25f5e539db',0,NULL,NULL,'[]','normal','periodic',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,0,1403258017,1403258017);
/*!40000 ALTER TABLE `cronjobs_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cronjobs_tasks`
--

DROP TABLE IF EXISTS `cronjobs_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cronjobs_tasks` (
  `task_id` char(32) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `execution_count` bigint(20) unsigned NOT NULL DEFAULT '0',
  `assigned_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cronjobs_tasks`
--

LOCK TABLES `cronjobs_tasks` WRITE;
/*!40000 ALTER TABLE `cronjobs_tasks` DISABLE KEYS */;
INSERT INTO `cronjobs_tasks` VALUES ('532b3fe76447dd85e10949a6fc5f3aa8','lib/cronjobs/cleanup_log.class.php','CleanupLogJob',1,0,0),('208619e89a59895771c2967076daf59e','lib/cronjobs/purge_cache.class.php','PurgeCacheJob',1,0,0),('d19f37c382fec524b4fd51b3c5a1ada3','lib/cronjobs/send_mail_notifications.class.php','SendMailNotificationsJob',1,0,0),('2f2713671892bd9624fc27866cfd4630','lib/cronjobs/check_admission.class.php','CheckAdmissionJob',1,0,0),('823875ed4a4b2e87baca0e5137243d96','lib/cronjobs/garbage_collector.class.php','GarbageCollectorJob',1,0,0),('3428a64935e8c6a5ab5dcf5bf95fe556','lib/cronjobs/session_gc.class.php','SessionGcJob',1,0,0),('9c4ad2a8fe47d07e61475d25f5e539db','lib/cronjobs/send_mail_queue.class.php','SendMailQueueJob',1,0,0);
/*!40000 ALTER TABLE `cronjobs_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datafields`
--

DROP TABLE IF EXISTS `datafields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datafields` (
  `datafield_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT NULL,
  `object_type` enum('sem','inst','user','userinstrole','usersemdata','roleinstdata') DEFAULT NULL,
  `object_class` varchar(10) DEFAULT NULL,
  `edit_perms` enum('user','autor','tutor','dozent','admin','root') DEFAULT NULL,
  `view_perms` enum('all','user','autor','tutor','dozent','admin','root') DEFAULT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mkdate` int(20) unsigned DEFAULT NULL,
  `chdate` int(20) unsigned DEFAULT NULL,
  `type` enum('bool','textline','textarea','selectbox','date','time','email','phone','radio','combo','link') NOT NULL DEFAULT 'textline',
  `typeparam` text NOT NULL,
  `is_required` tinyint(4) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  PRIMARY KEY (`datafield_id`),
  KEY `object_type` (`object_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datafields`
--

LOCK TABLES `datafields` WRITE;
/*!40000 ALTER TABLE `datafields` DISABLE KEYS */;
INSERT INTO `datafields` VALUES ('ce73a10d07b3bb13c0132d363549efda','Matrikelnummer','user','7','user','dozent',0,NULL,NULL,'textline','',0,'');
/*!40000 ALTER TABLE `datafields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datafields_entries`
--

DROP TABLE IF EXISTS `datafields_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datafields_entries` (
  `datafield_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `content` text,
  `mkdate` int(20) unsigned DEFAULT NULL,
  `chdate` int(20) unsigned DEFAULT NULL,
  `sec_range_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`datafield_id`,`range_id`,`sec_range_id`),
  KEY `range_id` (`range_id`,`datafield_id`),
  KEY `datafield_id_2` (`datafield_id`,`sec_range_id`),
  KEY `datafields_contents` (`datafield_id`,`content`(32))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datafields_entries`
--

LOCK TABLES `datafields_entries` WRITE;
/*!40000 ALTER TABLE `datafields_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `datafields_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deputies`
--

DROP TABLE IF EXISTS `deputies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deputies` (
  `range_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `gruppe` tinyint(4) NOT NULL DEFAULT '0',
  `notification` int(10) NOT NULL DEFAULT '0',
  `edit_about` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`range_id`,`user_id`),
  KEY `user_id` (`user_id`,`range_id`,`edit_about`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deputies`
--

LOCK TABLES `deputies` WRITE;
/*!40000 ALTER TABLE `deputies` DISABLE KEYS */;
/*!40000 ALTER TABLE `deputies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doc_filetype`
--

DROP TABLE IF EXISTS `doc_filetype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doc_filetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(45) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doc_filetype`
--

LOCK TABLES `doc_filetype` WRITE;
/*!40000 ALTER TABLE `doc_filetype` DISABLE KEYS */;
INSERT INTO `doc_filetype` VALUES (1,'exe',NULL),(2,'com',NULL),(3,'pif',NULL),(4,'bat',NULL),(5,'scr',NULL);
/*!40000 ALTER TABLE `doc_filetype` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doc_filetype_forbidden`
--

DROP TABLE IF EXISTS `doc_filetype_forbidden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doc_filetype_forbidden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usergroup` varchar(45) NOT NULL,
  `dateityp_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_dateityp_verbot_nutzerbereich_2_idx` (`dateityp_id`),
  KEY `fk_dateityp_verbot_nutzerbereich_1_idx` (`usergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doc_filetype_forbidden`
--

LOCK TABLES `doc_filetype_forbidden` WRITE;
/*!40000 ALTER TABLE `doc_filetype_forbidden` DISABLE KEYS */;
/*!40000 ALTER TABLE `doc_filetype_forbidden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doc_usergroup_config`
--

DROP TABLE IF EXISTS `doc_usergroup_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doc_usergroup_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usergroup` varchar(45) NOT NULL,
  `upload_quota` text NOT NULL,
  `upload_unit` varchar(45) DEFAULT NULL,
  `quota` text,
  `quota_unit` varchar(45) DEFAULT NULL,
  `upload_forbidden` int(11) NOT NULL DEFAULT '0',
  `area_close` int(11) NOT NULL DEFAULT '0',
  `area_close_text` text,
  `is_group_config` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`usergroup`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doc_usergroup_config`
--

LOCK TABLES `doc_usergroup_config` WRITE;
/*!40000 ALTER TABLE `doc_usergroup_config` DISABLE KEYS */;
INSERT INTO `doc_usergroup_config` VALUES (1,'default','5242880','MB','52428800','MB',0,0,NULL,1);
/*!40000 ALTER TABLE `doc_usergroup_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dokumente`
--

DROP TABLE IF EXISTS `dokumente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dokumente` (
  `dokument_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `seminar_id` varchar(32) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `filesize` int(20) NOT NULL DEFAULT '0',
  `autor_host` varchar(20) NOT NULL DEFAULT '',
  `downloads` int(20) NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL DEFAULT '',
  `protected` tinyint(4) NOT NULL DEFAULT '0',
  `priority` smallint(5) unsigned NOT NULL DEFAULT '0',
  `author_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`dokument_id`),
  KEY `range_id` (`range_id`),
  KEY `seminar_id` (`seminar_id`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`),
  KEY `mkdate` (`mkdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dokumente`
--

LOCK TABLES `dokumente` WRITE;
/*!40000 ALTER TABLE `dokumente` DISABLE KEYS */;
INSERT INTO `dokumente` VALUES ('6b606bd3d6d6cda829200385fa79fcbf','ca002fbae136b07e4df29e0136e3bd32','76ed43ef286fb55cf9e41beadb484a9f','a07535cf2f8a72df33c12ddfa4b53dde','Stud.IP-Produktbrosch?re im PDF-Format','','mappe_studip-el.pdf',1343924827,1343924841,314146,'127.0.0.1',0,'http://www.studip.de/download/mappe_studip-el.pdf',0,0,'Root Studip');
/*!40000 ALTER TABLE `dokumente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eval`
--

DROP TABLE IF EXISTS `eval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eval` (
  `eval_id` varchar(32) NOT NULL DEFAULT '',
  `author_id` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `startdate` int(20) DEFAULT NULL,
  `stopdate` int(20) DEFAULT NULL,
  `timespan` int(20) DEFAULT NULL,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `anonymous` tinyint(1) NOT NULL DEFAULT '1',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eval_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eval`
--

LOCK TABLES `eval` WRITE;
/*!40000 ALTER TABLE `eval` DISABLE KEYS */;
/*!40000 ALTER TABLE `eval` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eval_group_template`
--

DROP TABLE IF EXISTS `eval_group_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eval_group_template` (
  `evalgroup_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `group_type` varchar(250) NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`evalgroup_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eval_group_template`
--

LOCK TABLES `eval_group_template` WRITE;
/*!40000 ALTER TABLE `eval_group_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `eval_group_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eval_range`
--

DROP TABLE IF EXISTS `eval_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eval_range` (
  `eval_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`eval_id`,`range_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eval_range`
--

LOCK TABLES `eval_range` WRITE;
/*!40000 ALTER TABLE `eval_range` DISABLE KEYS */;
/*!40000 ALTER TABLE `eval_range` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eval_templates`
--

DROP TABLE IF EXISTS `eval_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eval_templates` (
  `template_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) DEFAULT NULL,
  `institution_id` varchar(32) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `show_questions` tinyint(1) NOT NULL DEFAULT '1',
  `show_total_stats` tinyint(1) NOT NULL DEFAULT '1',
  `show_graphics` tinyint(1) NOT NULL DEFAULT '1',
  `show_questionblock_headline` tinyint(1) NOT NULL DEFAULT '1',
  `show_group_headline` tinyint(1) NOT NULL DEFAULT '1',
  `polscale_gfx_type` varchar(255) NOT NULL DEFAULT 'bars',
  `likertscale_gfx_type` varchar(255) NOT NULL DEFAULT 'bars',
  `mchoice_scale_gfx_type` varchar(255) NOT NULL DEFAULT 'bars',
  `kurzbeschreibung` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`template_id`),
  KEY `user_id` (`user_id`,`institution_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eval_templates`
--

LOCK TABLES `eval_templates` WRITE;
/*!40000 ALTER TABLE `eval_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `eval_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eval_templates_eval`
--

DROP TABLE IF EXISTS `eval_templates_eval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eval_templates_eval` (
  `eval_id` varchar(32) NOT NULL DEFAULT '',
  `template_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`eval_id`),
  KEY `eval_id` (`eval_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eval_templates_eval`
--

LOCK TABLES `eval_templates_eval` WRITE;
/*!40000 ALTER TABLE `eval_templates_eval` DISABLE KEYS */;
/*!40000 ALTER TABLE `eval_templates_eval` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eval_templates_user`
--

DROP TABLE IF EXISTS `eval_templates_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eval_templates_user` (
  `eval_id` varchar(32) NOT NULL DEFAULT '',
  `template_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  KEY `eval_id` (`eval_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eval_templates_user`
--

LOCK TABLES `eval_templates_user` WRITE;
/*!40000 ALTER TABLE `eval_templates_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `eval_templates_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eval_user`
--

DROP TABLE IF EXISTS `eval_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eval_user` (
  `eval_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`eval_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eval_user`
--

LOCK TABLES `eval_user` WRITE;
/*!40000 ALTER TABLE `eval_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `eval_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evalanswer`
--

DROP TABLE IF EXISTS `evalanswer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evalanswer` (
  `evalanswer_id` varchar(32) NOT NULL DEFAULT '',
  `parent_id` varchar(32) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  `rows` tinyint(4) NOT NULL DEFAULT '0',
  `counter` int(11) NOT NULL DEFAULT '0',
  `residual` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`evalanswer_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evalanswer`
--

LOCK TABLES `evalanswer` WRITE;
/*!40000 ALTER TABLE `evalanswer` DISABLE KEYS */;
INSERT INTO `evalanswer` VALUES ('d67301d4f59aa35d1e3f12a9791b6885','ef227e91618878835d52cfad3e6d816b',0,'Sehr gut',1,0,0,0),('7052b76e616656e4b70f1c504c04ec81','ef227e91618878835d52cfad3e6d816b',1,'',2,0,0,0),('64152ace8f2a74d0efb67c54eff64a2b','ef227e91618878835d52cfad3e6d816b',2,'',3,0,0,0),('3a3ab5307f39ea039d41fb6f2683475e','ef227e91618878835d52cfad3e6d816b',3,'',4,0,0,0),('6115b19f694ccd3d010a0047ff8f970a','ef227e91618878835d52cfad3e6d816b',4,'Sehr Schlecht',5,0,0,0),('be4c3e5fe0b2b735bb3b2712afa8c490','ef227e91618878835d52cfad3e6d816b',5,'Keine Meinung',6,0,0,1),('84be4c31449a9c1807bf2dea0dc869f1','724244416b5d04a4d8f4eab8a86fdbf8',0,'Sehr gut',1,0,0,0),('c446970d2addd68e43c2a6cae6117bf7','724244416b5d04a4d8f4eab8a86fdbf8',1,'Gut',2,0,0,0),('3d4dcedb714dfdcfbe65cd794b4d404b','724244416b5d04a4d8f4eab8a86fdbf8',2,'Befriedigend',3,0,0,0),('fa2bf667ba73ae74794df35171c2ad2e','724244416b5d04a4d8f4eab8a86fdbf8',3,'Ausreichend',4,0,0,0),('0be387b9379a05c5578afce64b0c688f','724244416b5d04a4d8f4eab8a86fdbf8',4,'Mangelhaft',5,0,0,0),('aec07dd525f2610bdd10bf778aa1893b','724244416b5d04a4d8f4eab8a86fdbf8',5,'Nicht erteilt',6,0,0,1),('7080335582e2787a54f315ec8cef631e','95bbae27965d3404f7fa3af058850bd3',0,'trifft v?llig zu',1,0,0,0),('d68a74dc2c1f0ce226366da918dd161d','95bbae27965d3404f7fa3af058850bd3',1,'trifft ziemlich zu',2,0,0,0),('641686e7c61899b303cda106f20064e7','95bbae27965d3404f7fa3af058850bd3',2,'teilsteils',3,0,0,0),('7c36d074f2cc38765c982c9dfb769afc','95bbae27965d3404f7fa3af058850bd3',3,'trifft wenig zu',4,0,0,0),('5c4827f903168ed4483db5386a9ad5b8','95bbae27965d3404f7fa3af058850bd3',4,'trifft gar nicht zu',5,0,0,0),('c10a3f4e97f8badc5230a9900afde0c7','95bbae27965d3404f7fa3af058850bd3',5,'kann ich nicht beurteilen',6,0,0,1),('ced33706ca95aff2163c7d0381ef5717','6fddac14c1f2ac490b93681b3da5fc66',0,'Montag',1,0,0,0),('087c734855c8a5b34d99c16ad09cd312','6fddac14c1f2ac490b93681b3da5fc66',1,'Dienstag',2,0,0,0),('63f5011614f45329cc396b90d94a7096','6fddac14c1f2ac490b93681b3da5fc66',2,'Mittwoch',3,0,0,0),('ccd1eaddccca993f6789659b36f40506','6fddac14c1f2ac490b93681b3da5fc66',3,'Donnerstag',4,0,0,0),('48842cedeac739468741940982b5fe6d','6fddac14c1f2ac490b93681b3da5fc66',4,'Freitag',5,0,0,0),('21b3f7cf2de5cbb098d800f344d399ee','12e508079c4770fb13c9fce028f40cac',0,'Montag',1,0,0,0),('f0016e918b5bc5c4cf3cc62bf06fa2e9','12e508079c4770fb13c9fce028f40cac',1,'Dienstag',2,0,0,0),('c88242b50ff0bb43df32c1e15bdaca22','12e508079c4770fb13c9fce028f40cac',2,'Mittwoch',3,0,0,0),('b39860f6601899dcf87ba71944c57bc7','12e508079c4770fb13c9fce028f40cac',3,'Donnerstag',4,0,0,0),('568d6fd620642cb7395c27d145a76734','12e508079c4770fb13c9fce028f40cac',4,'Freitag',5,0,0,0),('39b98a5560d5dabaf67227e2895db8da','a68bd711902f23bd5c55a29f1ecaa095',0,'',1,5,0,0),('61ae27ab33c402316a3f1eb74e1c46ab','442e1e464e12498bd238a7767215a5a2',0,'',1,1,0,0);
/*!40000 ALTER TABLE `evalanswer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evalanswer_user`
--

DROP TABLE IF EXISTS `evalanswer_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evalanswer_user` (
  `evalanswer_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`evalanswer_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evalanswer_user`
--

LOCK TABLES `evalanswer_user` WRITE;
/*!40000 ALTER TABLE `evalanswer_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `evalanswer_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evalgroup`
--

DROP TABLE IF EXISTS `evalgroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evalgroup` (
  `evalgroup_id` varchar(32) NOT NULL DEFAULT '',
  `parent_id` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `child_type` enum('EvaluationGroup','EvaluationQuestion') NOT NULL DEFAULT 'EvaluationGroup',
  `mandatory` tinyint(1) NOT NULL DEFAULT '0',
  `template_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`evalgroup_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evalgroup`
--

LOCK TABLES `evalgroup` WRITE;
/*!40000 ALTER TABLE `evalgroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `evalgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evalquestion`
--

DROP TABLE IF EXISTS `evalquestion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evalquestion` (
  `evalquestion_id` varchar(32) NOT NULL DEFAULT '',
  `parent_id` varchar(32) NOT NULL DEFAULT '',
  `type` enum('likertskala','multiplechoice','polskala') NOT NULL DEFAULT 'multiplechoice',
  `position` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `multiplechoice` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`evalquestion_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evalquestion`
--

LOCK TABLES `evalquestion` WRITE;
/*!40000 ALTER TABLE `evalquestion` DISABLE KEYS */;
INSERT INTO `evalquestion` VALUES ('ef227e91618878835d52cfad3e6d816b','0','polskala',0,'Wertung 1-5',0),('724244416b5d04a4d8f4eab8a86fdbf8','0','likertskala',0,'Schulnoten',0),('95bbae27965d3404f7fa3af058850bd3','0','likertskala',0,'Wertung (trifft zu, ...)',0),('6fddac14c1f2ac490b93681b3da5fc66','0','multiplechoice',0,'Werktage',0),('12e508079c4770fb13c9fce028f40cac','0','multiplechoice',0,'Werktage-mehrfach',1),('a68bd711902f23bd5c55a29f1ecaa095','0','multiplechoice',0,'Freitext-Mehrzeilig',0),('442e1e464e12498bd238a7767215a5a2','0','multiplechoice',0,'Freitext-Einzeilig',0);
/*!40000 ALTER TABLE `evalquestion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex_termine`
--

DROP TABLE IF EXISTS `ex_termine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex_termine` (
  `termin_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `autor_id` varchar(32) NOT NULL DEFAULT '',
  `content` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `date` int(20) NOT NULL DEFAULT '0',
  `end_time` int(20) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `date_typ` tinyint(4) NOT NULL DEFAULT '0',
  `topic_id` varchar(32) DEFAULT NULL,
  `raum` varchar(255) DEFAULT NULL,
  `metadate_id` varchar(32) DEFAULT NULL,
  `resource_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`termin_id`),
  KEY `range_id` (`range_id`,`date`),
  KEY `metadate_id` (`metadate_id`,`date`),
  KEY `autor_id` (`autor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex_termine`
--

LOCK TABLES `ex_termine` WRITE;
/*!40000 ALTER TABLE `ex_termine` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex_termine` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extern_config`
--

DROP TABLE IF EXISTS `extern_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extern_config` (
  `config_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `config_type` int(4) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `is_standard` int(4) NOT NULL DEFAULT '0',
  `config` mediumtext NOT NULL,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`config_id`,`range_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extern_config`
--

LOCK TABLES `extern_config` WRITE;
/*!40000 ALTER TABLE `extern_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `extern_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_refs`
--

DROP TABLE IF EXISTS `file_refs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_refs` (
  `id` char(32) NOT NULL,
  `file_id` char(32) NOT NULL,
  `parent_id` char(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `downloads` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_refs`
--

LOCK TABLES `file_refs` WRITE;
/*!40000 ALTER TABLE `file_refs` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_refs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `file_id` char(32) NOT NULL,
  `user_id` char(32) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `mime_type` varchar(64) NOT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `restricted` tinyint(1) NOT NULL DEFAULT '0',
  `storage` varchar(32) NOT NULL DEFAULT 'DiskFileStorage',
  `storage_id` varchar(32) NOT NULL,
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  `chdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files_backend_studip`
--

DROP TABLE IF EXISTS `files_backend_studip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files_backend_studip` (
  `id` int(10) unsigned NOT NULL,
  `files_id` varchar(64) NOT NULL,
  `path` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files_backend_studip`
--

LOCK TABLES `files_backend_studip` WRITE;
/*!40000 ALTER TABLE `files_backend_studip` DISABLE KEYS */;
/*!40000 ALTER TABLE `files_backend_studip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files_backend_url`
--

DROP TABLE IF EXISTS `files_backend_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files_backend_url` (
  `id` int(10) unsigned NOT NULL,
  `files_id` varchar(64) NOT NULL,
  `url` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files_backend_url`
--

LOCK TABLES `files_backend_url` WRITE;
/*!40000 ALTER TABLE `files_backend_url` DISABLE KEYS */;
/*!40000 ALTER TABLE `files_backend_url` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files_share`
--

DROP TABLE IF EXISTS `files_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files_share` (
  `files_id` varchar(64) NOT NULL,
  `entity_id` varchar(32) NOT NULL,
  `description` mediumtext,
  `read_perm` tinyint(1) DEFAULT '0',
  `write_perm` tinyint(1) DEFAULT '0',
  `start_date` int(10) unsigned NOT NULL,
  `end_date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`files_id`,`entity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files_share`
--

LOCK TABLES `files_share` WRITE;
/*!40000 ALTER TABLE `files_share` DISABLE KEYS */;
/*!40000 ALTER TABLE `files_share` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `folder`
--

DROP TABLE IF EXISTS `folder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `folder` (
  `folder_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `seminar_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `permission` tinyint(3) unsigned NOT NULL DEFAULT '7',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `priority` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`folder_id`),
  KEY `user_id` (`user_id`),
  KEY `range_id` (`range_id`),
  KEY `chdate` (`chdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `folder`
--

LOCK TABLES `folder` WRITE;
/*!40000 ALTER TABLE `folder` DISABLE KEYS */;
INSERT INTO `folder` VALUES ('dad53cd0f0d9f36817c3c9c7c124bda3','ec2e364b28357106c0f8c282733dbe56','ec2e364b28357106c0f8c282733dbe56','','Allgemeiner Dateiordner','Ablage f?r allgemeine Ordner und Dokumente der Einrichtung',7,1156516698,1156516698,0),('b58081c411c76814bc8f78425fb2ab81','7a4f19a0a2c321ab2b8f7b798881af7c','7a4f19a0a2c321ab2b8f7b798881af7c','','Allgemeiner Dateiordner','Ablage f?r allgemeine Ordner und Dokumente der Einrichtung',7,1156516698,1156516698,0),('694cdcef09c2b8e70a7313b028e36fb6','110ce78ffefaf1e5f167cd7019b728bf','110ce78ffefaf1e5f167cd7019b728bf','','Allgemeiner Dateiordner','Ablage f?r allgemeine Ordner und Dokumente der Einrichtung',7,1156516698,1156516698,0),('ad8dc6a6162fb0fe022af4a62a15e309','373a72966cf45c484b4b0b07dba69a64','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','Hausaufgaben','',3,1343924873,1343924877,0),('df122112a21812ff4ffcf1965cb48fc3','2f597139a049a768dbf8345a0a0af3de','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','Dateiordner der Gruppe: Studierende','Ablage f?r Ordner und Dokumente dieser Gruppe',15,1343924860,1343924860,0),('1af61dbdcfca1b394290c5d4283371d7','7cb72dab1bf896a0b55c6aa7a70a3a86','7cb72dab1bf896a0b55c6aa7a70a3a86','76ed43ef286fb55cf9e41beadb484a9f','Allgemeiner Dateiordner','Ablage f?r allgemeine Ordner und Dokumente der Veranstaltung',7,1343924088,1343924088,0),('ca002fbae136b07e4df29e0136e3bd32','a07535cf2f8a72df33c12ddfa4b53dde','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','Allgemeiner Dateiordner','Ablage f?r allgemeine Ordner und Dokumente der Veranstaltung',5,1343924407,1343924894,0);
/*!40000 ALTER TABLE `folder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_abo_users`
--

DROP TABLE IF EXISTS `forum_abo_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_abo_users` (
  `topic_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  PRIMARY KEY (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_abo_users`
--

LOCK TABLES `forum_abo_users` WRITE;
/*!40000 ALTER TABLE `forum_abo_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_abo_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_categories`
--

DROP TABLE IF EXISTS `forum_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_categories` (
  `category_id` varchar(32) NOT NULL,
  `seminar_id` varchar(32) NOT NULL,
  `entry_name` varchar(255) NOT NULL,
  `pos` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `seminar_id` (`seminar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_categories`
--

LOCK TABLES `forum_categories` WRITE;
/*!40000 ALTER TABLE `forum_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_categories_entries`
--

DROP TABLE IF EXISTS `forum_categories_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_categories_entries` (
  `category_id` varchar(32) NOT NULL,
  `topic_id` varchar(32) NOT NULL,
  `pos` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_categories_entries`
--

LOCK TABLES `forum_categories_entries` WRITE;
/*!40000 ALTER TABLE `forum_categories_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_categories_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_entries`
--

DROP TABLE IF EXISTS `forum_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_entries` (
  `topic_id` varchar(32) NOT NULL,
  `seminar_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `area` tinyint(4) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL,
  `latest_chdate` int(11) DEFAULT NULL,
  `chdate` int(20) NOT NULL,
  `author` varchar(255) NOT NULL,
  `author_host` varchar(255) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `depth` int(11) NOT NULL,
  `anonymous` tinyint(4) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `sticky` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `seminar_id` (`seminar_id`,`lft`),
  KEY `seminar_id_2` (`seminar_id`,`rgt`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_entries`
--

LOCK TABLES `forum_entries` WRITE;
/*!40000 ALTER TABLE `forum_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_entries_issues`
--

DROP TABLE IF EXISTS `forum_entries_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_entries_issues` (
  `topic_id` varchar(32) NOT NULL,
  `issue_id` varchar(32) NOT NULL,
  PRIMARY KEY (`topic_id`,`issue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_entries_issues`
--

LOCK TABLES `forum_entries_issues` WRITE;
/*!40000 ALTER TABLE `forum_entries_issues` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_entries_issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_favorites`
--

DROP TABLE IF EXISTS `forum_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_favorites` (
  `user_id` varchar(32) NOT NULL,
  `topic_id` varchar(32) NOT NULL,
  PRIMARY KEY (`user_id`,`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_favorites`
--

LOCK TABLES `forum_favorites` WRITE;
/*!40000 ALTER TABLE `forum_favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_likes`
--

DROP TABLE IF EXISTS `forum_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_likes` (
  `topic_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  PRIMARY KEY (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_likes`
--

LOCK TABLES `forum_likes` WRITE;
/*!40000 ALTER TABLE `forum_likes` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_visits`
--

DROP TABLE IF EXISTS `forum_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_visits` (
  `user_id` varchar(32) NOT NULL,
  `seminar_id` varchar(32) NOT NULL,
  `visitdate` int(11) NOT NULL,
  `last_visitdate` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`seminar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_visits`
--

LOCK TABLES `forum_visits` WRITE;
/*!40000 ALTER TABLE `forum_visits` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_content`
--

DROP TABLE IF EXISTS `help_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_content` (
  `content_id` char(32) NOT NULL,
  `language` char(2) NOT NULL DEFAULT 'de',
  `label` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `route` varchar(255) NOT NULL,
  `studip_version` varchar(32) NOT NULL,
  `position` tinyint(4) NOT NULL DEFAULT '1',
  `custom` tinyint(4) NOT NULL DEFAULT '0',
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `author_id` char(32) NOT NULL DEFAULT '',
  `installation_id` varchar(255) NOT NULL,
  `mkdate` int(11) unsigned NOT NULL,
  PRIMARY KEY (`route`,`studip_version`,`language`,`position`,`custom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_content`
--

LOCK TABLES `help_content` WRITE;
/*!40000 ALTER TABLE `help_content` DISABLE KEYS */;
INSERT INTO `help_content` VALUES ('5a90d1219dbeb07c124156592fb5d877','de','','','In den allgemeinen Einstellungen k?nnen verschiedene Anzeigeoptionen und Benachrichtigungsfunktionen ausgew?hlt und ver?ndert werden.','dispatch.php/settings/general','3.1',0,0,1,'','',1406641688),('a202eb75df0a1da2a309ad7a4abfac59','de','','','In den Privatsph?re-Einstellungen kann die Sichtbarkeit und Auffindbarkeit des eigenen Profils eingestellt werden.','dispatch.php/settings/privacy','3.1',0,0,1,'','',1406641688),('845d1ce67a62d376ec26c8ffbb22d492','de','','','Die Einstellungen des Nachrichtensystems bieten die M?glichkeit z.B. eine Weiterleitung der in Stud.IP empfangenen Nachrichten an die E-Mail-Adresse zu veranlassen.','dispatch.php/settings/messaging','3.1',0,0,1,'','',1406641688),('85cbaa1648af330cc4420b57df4be29c','de','','','Die Einstellungen des Terminkalenders bieten die M?glichkeit, diesen an eigene Bed?rfnisse anzupassen.','dispatch.php/settings/calendar','3.1',0,0,1,'','',1406641688),('1da144f3c6f52af0566c343151a6a6ff','de','','','In den Benachrichtigungseinstellungen kann ausgew?hlt werden, bei welchen ?nderungen innerhalb einer Veranstaltung eine Benachrichtigung erfolgen soll.','dispatch.php/settings/notification','3.1',0,0,1,'','',1406641688),('01ad8998268101ad186babf43dac30a4','de','','','In den Standard-Vertretungseinstellungen können Lehrende eine Standard-Vertretung festlegen, die alle Veranstaltungen des Lehrenden verwalten und ?ndern kann.','dispatch.php/settings/deputies','3.1',0,0,1,'','',1406641688),('1c61657979ce22a9af023248a617f6b2','de','','','Die Startseite wird nach dem Einloggen angezeigt und kann an pers?nliche Bed?rfnisse mit Hilfe von Widgets angepasst werden.','dispatch.php/start','3.1',0,0,1,'','',1406641688),('74c1da86f33f5adfb43e10220bfad238','de','','','Die Veranstaltungsseite zeigt alle belegten Veranstaltungen (standardm??ig nur die der letzten beiden Semester), alle belegten Studiengruppen sowie alle Einrichtungen, denen man zugeordnet wurde. Die Anzeige l?sst sich ?ber Farbgruppierungen, Semesterfilter usw. anpassen.','dispatch.php/my_courses','3.1',0,0,1,'','',1406641688),('437c83a27473ef8139b47198101067fb','de','','','Hier erscheinen archivierte Veranstaltungen, denen der Nutzer zugeordnet ist. Inhalte k?nnen nicht mehr ver?ndert, jedoch hinterlegte Dateien als zip-Datei heruntergeladen werden.','dispatch.php/my_courses/archive','3.1',0,0,1,'','',1406641688),('04457f9a66eab07618fe502d470a9711','de','','','In der ?bersicht finden sich veranstaltungsbezogene Kurz- und Detail-Informationen, Ank?ndigungen, Termine und Umfragen.','dispatch.php/course/overview','3.1',0,0,1,'','',1406641688),('1d1323471cf21637f51284f4e6f2d135','de','','','Detaillierte Informationen ?ber die Veranstaltung werden angezeigt, wie z.B. die Veranstaltungsnummer, Zuordnungen, Lehrende, Tutor/-innen etc. In den Detail-Informationen ist unter Aktionen das Eintragen in eine Veranstaltung m?glich.','dispatch.php/course/details','3.1',0,0,1,'','',1406641688),('7f4a1f5e3dfe2a459cf0eb357667d91c','de','','','Mit den Verwaltungsfunktionen lassen sich die Eigenschaften der Veranstaltung nachtr?glich ?ndern. Unter Aktionen ist die Simulation der Studierendenansicht m?glich.','dispatch.php/course/management','3.1',0,0,1,'','',1406641688),('4698cafeb9823735c50fd3a1745950ba','de','','','In den Grunddaten k?nnen Titel, Beschreibung, Lehrende etc. geändert werden. Die Bearbeitung kann teilweise gesperrt sein, wenn Daten aus anderen Systemen (z.B. LSF/ UniVZ) ?bernommen werden.','dispatch.php/course/basicdata/view','3.1',0,0,1,'','',1406641688),('5fab81bbd1e19949f304df08ea21ca1b','de','','','Mit der Bild-Hochladen-Funktion l?sst sich das Bild der Veranstaltung ?ndern, was Studierenden bei der Unterscheidung von Veranstaltungen auf der Meine-Veranstaltungen-Seite helfen kann.','dispatch.php/course/avatar/update','3.1',0,0,1,'','',1406641688),('19c2bc232075602bd39efd4b6623d576','de','','','Mit der Studienbereiche-Funktion kann die Veranstaltung einem Studienbereich zugeordnet werden. Die Bearbeitung kann gesperrt sein, wenn Daten aus anderen Systemen (z.B. LSF/ UniVZ) ?bernommen werden.','dispatch.php/course/study_areas/show','3.1',0,0,1,'','',1406641688),('70274c459a69e34bbf520e690a8e472b','de','','','Mit der Zeiten/R?ume-Funktion k?nnen die Semester-, Termin- und Raumangaben der Veranstaltung ge?ndert werden. Die Bearbeitung kann gesperrt sein, wenn Daten aus anderen Systemen (z.B. LSF/ UniVZ) ?bernommen werden.','raumzeit.php','3.1',0,0,1,'','',1406641688),('19d47b782ac5c8b8b21bd1f94858a0fa','de','','','Mit Zugangsberechtigungen (Anmeldeverfahren) l?sst sich z.B. durch Passw?rter, Zeitsteuerung und TeilnehmerInnenbeschr?nkung der Zugang zu einer Veranstaltung regulieren.','dispatch.php/course/admission','3.1',0,0,1,'','',1406641688),('e939ac70210674f49a36ac428167a9b8','de','','','Mit der Umfragen-und-Tests-Funktion lassen sich (zeitgesteuerte) Umfragen oder einzelne Multiple-/Single-Choice-Fragen f?r Veranstaltungen, Studiengruppen oder das Profil erstellen.','admin_vote.php','3.1',0,0,1,'','',1406641688),('2689cecba24e021f05fcece5e4c96057','de','','','Mit der Evaluationen-Funktion lassen sich Befragungen mit Multiple-Choice, Likert- und Freitextfragen f?r Veranstaltungen, Studiengruppen, das eigene Profil oder Einrichtungen erstellen. Dabei k?nnen auch ?ffentliche Vorlagen anderer Personen verwendet werden. Es werden alle zuk?nftigen, laufenden und beendeten Evaluationen angezeigt.','admin_evaluation.php','3.1',0,0,1,'','',1406641688),('194874212676ced8d45e1883da1ad456','de','','','Das Forum ist eine textbasierte, zeit- und ortsunabh?ngige M?glichkeit zum Austausch von Fragen, Meinungen und Erfahrungen. Beitr?ge k?nnen abonniert, exportiert, als Favoriten gekennzeichnet und editiert werden. ?ber die Navigation links k?nnen unterschieldiche Ansichten (z.B. Neue Beitr?ge seit letztem LogIn) gew?hlt werden.','plugins.php/coreforum','3.1',0,0,1,'','',1406641688),('a20036992a06e97a984832626121d99a','de','','','Die TeilnehmerInnenliste zeigt eine Liste der Teilnehmenden dieser Veranstaltung. Weitere Teilnehmende können von Lehrenden hinzugef?gt, entfernt, herabgestuft, heraufgestuft oder selbstdefinierten Gruppen zugeordnet werden.','dispatch.php/course/members','3.1',0,0,1,'','',1406641688),('d79ca3bc4a8251862339b1c934504a54','de','','','Hier werden die selbstdefinierten Gruppen angezeigt. An diese k?nnen Nachrichten versendet werden. Ein Klick auf die orangenen Pfeile vor dem Gruppenname ordnet Sie der Gruppe zu.','statusgruppen.php','3.1',0,0,1,'','',1406641688),('e22701c71b4425fb5a95adf725866097','de','','','Hier k?nnen Gruppen erstellt und verwaltet werden. Wenn der Selbsteintrag aktiviert ist, k?nnen sich TeilnehmerInnen selbst ein- und austragen.','admin_statusgruppe.php','3.1',0,0,1,'','',1406641688),('aa77d5ee6e0f9a9e6f4a1bbabeaf4a7e','de','','','Die Anwesenheitsliste zeigt alle Sitzungstermine (Sitzung, Vorlesung, ?bung, Praktikum) des Ablaufplans und erm?glicht das Eintragen von Studierenden durch die Lehrenden in Stud.IP sowie einen Export der Liste zur ?bersicht oder als Grundlage handschriftlicher Eintragungen.','participantsattendanceplugin/show','3.1',0,0,1,'','',1406641688),('ac7326260fd5ca4fa83c1154f2ffc7b9','de','','','Die Dateiverwaltung bietet die M?glichkeit zum Hochladen, Verlinken, Verwalten und Herunterladen von Dateien. ','folder.php','3.1',0,0,1,'','',1406641688),('29c3bfa01ddbaaa998094d3ee975a06a','de','','','Der Ablaufplan zeigt Termine, Themen und R?ume der Veranstaltung an. Einzelne Termine k?nnen bearbeitet werden, z.B. k?nnen Themen zu Terminen hinzugef?gt werden.','dispatch.php/course/dates','3.1',0,0,1,'','',1406641688),('c4dee277f741cfa7d5a65fa0c6bead4c','de','','','Hier k?nnen Termine mit Themen versehen werden oder bereits eingegebene Themen ?bernommen und bearbeitet werden.','dispatch.php/course/topics','3.1',0,0,1,'','',1406641688),('c01725d6a3da568e1b07aee4e68a7e1f','de','','','Diese Seite erm?glicht das Hinterlegen von freien Informationen, Links etc.','dispatch.php/course/scm','3.1',0,0,1,'','',1406641688),('be204bdd0fce91702f51597bf8428fba','de','','','Das Wiki erm?glicht ein gemeinsames, asynchrones Erstellen und Bearbeiten von Texten. Texte lassen sich formatieren und miteinander verkn?pfen, so dass ein verzweigtes Nachschlagewerk entsteht. ','wiki.php','3.1',0,0,1,'','',1406641688),('707b0db0e45fc3bab04be7eff38c1d32','de','','','Die Literaturseite bietet Lehrenden die M?glichkeit, Literaturlisten zu erstellen oder aus Literaturverwaltungsprogrammen zu importieren. Diese Listen k?nnen in Lehrveranstaltungen kopiert und sichtbar geschaltet werden. Je nach Anbindung kann im tats?chlichen Buchbestand der Hochschule recherchiert werden. ','dispatch.php/course/literature','3.1',0,0,1,'','',1406641688),('8dd3b80d9f95218d67edc3cb570559ff','de','','','Hier lassen sich Literaturlisten bearbeiten und in der Veranstaltung sichtbar schalten (mit Klick auf das \"Auge\").','dispatch.php/literature/edit_list','3.1',0,0,1,'','',1406641688),('e29098d188ae25c298d78978de50bf09','de','','','Hier kann in Katalogen nach Literatur gesucht und diese zur Merkliste hinzugef?gt werden.','dispatch.php/literature/search','3.1',0,0,1,'','',1406641688),('a1ea37130799a59f7774473f1a681141','de','','','Die Lernmodulschnittstelle erm?glicht es, Selbstlerneinheiten oder Tests aus externen Programmen wie ILIAS und LON-CAPA in Stud.IP zur Verf?gung zu stellen.','dispatch.php/course/elearning/show','3.1',0,0,1,'','',1406641688),('595c46d86f681f7da4bd2fae780db618','de','','','W?hlen Sie das gew?nschte System und anschlie?end das Lernmodul/ den Test aus. Schreibrechte bestimmen, wer zuk?nftig das Lernmodul bearbeiten darf. In der Sidebar befindet sich die Option \"Zuordnungen aktualisieren\", um ge?nderte Inhalte z.B. im ILIAS Kurs zu Stud.IP zu ?bertragen.','dispatch.php/course/elearning/edit','3.1',0,0,1,'','',1406641688),('6529fd70b461fa4a9242e874fbf2a5d3','de','','','In DoIT! haben Lehrende die M?glichkeit, verschiedene Arten von Aufgaben zu stellen, inklusive Hochladen von Dateien, Multiple-Choice-Fragen und Peer Reviewing. Die Aufgabenbearbeitung kann zeitlich befristet werden und wahlweise in Gruppen erfolgen.','plugins.php/reloadedplugin/show','3.1',0,0,1,'','',1406641688),('95ff3a2a68dae73bcb14a4a538a8e4b5','de','','','Blubbern ist eine Mischform aus Forum und Chat, bei dem Beitr?ge der Teilnehmenden in Echtzeit angezeigt werden. Andere k?nnen ?ber einen Beitrag informiert werden, indem sie per @benutzername oder @\"Vorname Nachname\" im Beitrag erw?hnt werden.','plugins.php/blubber/streams/forum','3.1',0,0,1,'','',1406641688),('633dab120ce3969c42f33aeb3a59fcc1','de','','','Der Gruppenkalender bietet eine ?bersicht ?ber Veranstaltungstermine und personalisierte Zusatztermine f?r diese Veranstaltung. ','plugins.php/gruppenkalenderplugin/show','3.1',0,0,1,'','',1406641688),('1058f03da5b6fc6a5ff3a08c9c1fa5f7','de','','','Hier k?nnen der Veranstaltung weitere Funktionen hinzugef?gt werden.','dispatch.php/course/plus','3.1',0,0,1,'','',1406641688),('8a1d7d04c70d93be44e8fe6a8e8c3443','de','','','Das Lerntagebuch unterst?tzt den selbstgesteuerten Lernprozess der Studierenden und wird von ihnen selbstst?ndig gef?hrt. Anfragen zu Arbeitsschritten an die Lehrenden sind m?glich, bestimmte Daten k?nnen individualisiert freigegeben werden.','plugins.php/lerntagebuchplugin/overview','3.1',0,0,1,'','',1406641688),('72cec29d985f3e6d7df2b5fabb7fe666','de','','','Konfiguation des Lerntagebuchs f?r Studierende und Anlegen eines Lerntagebuchs f?r die Lehrenden.','plugins.php/lerntagebuchplugin/admin_settings','3.1',0,0,1,'','',1406641688),('2fcc672d91f2627ab5ca48499e8b1617','de','','','M?glichkeit zur Bereitstellung von Vorlesungsaufzeichnungen und Podcasts f?r Studierende der Veranstaltung (durch Verlinkung auf die Dateien auf dem Medienserver). ','plugins.php/mediacastsplugin/show','3.1',0,0,1,'','',1406641688),('bd0770f9eef5c10fc211114ac35fbe9b','de','','','Diese Seite zeigt die Studiengruppen an, denen die/der NutzerIn zugeordnet ist. Studiengruppen sind eine einfache M?glichkeit, mit Mitstudierenden, KollegInnen und anderen zusammenzuarbeiten. Jede/r NutzerIn kann Studiengruppen anlegen oder nach ihnen suchen. Die Farbgruppierung kann individuell angepasst werden.','dispatch.php/my_studygroups','3.1',0,0,1,'','',1406641688),('82a17a5f19d211268b1fa90a1ebe0894','de','','','Hier kann eine neue Studiengruppe angelegt werden. Jede/r Stud.IP-NutzerIn kann Studiengruppen anlegen und nach eigenen Bed?rfnissen konfigurieren.','dispatch.php/course/studygroup/new','3.1',0,0,1,'','',1406641688),('e03cec310c0a884aee80c2d1eea3a53e','de','','','Diese Seite zeigt alle Studiengruppen an, die in Stud.IP existieren. Studiengruppen sind eine einfache M?glichkeit, mit Mitstudierenden, KollegInnen und anderen zusammenzuarbeiten. Jede/r NutzerIn kann Studiengruppen anlegen oder nach ihnen suchen.','dispatch.php/studygroup/browse','3.1',0,0,1,'','',1406641688),('f92b5422246f585f051de1a81602dd56','de','','','Hier k?nnen Name, Funktionen und Zugangsbeschr?nkung der Studiengruppe bearbeitet werden.','dispatch.php/course/studygroup/edit','3.1',0,0,1,'','',1406641688),('1dca5b0b83f7bca92ec4add50d34b8c5','de','','','Hier k?nnen der Studiengruppe Mitglieder hinzugef?gt und Nachrichten an diese versendet werden.','dispatch.php/course/studygroup/members','3.1',0,0,1,'','',1406641688),('1f6e2f98affbffb1d12904355e9313e5','de','','','Diese Seite zeigt die Einrichtungen an, denen die/der NutzerIn zugeordnet ist.','dispatch.php/my_institutes','3.1',0,0,1,'','',1406641688),('bf9eb8f2c3842865009342b89fd35476','de','','','Die Nachrichtenseite bietet einen ?berblick ?ber erhaltene, systeminterne Nachrichten, welche mit selbstgew?hlten Schl?sselw?rtern (sog. Tags) versehen werden k?nnen, um sie sp?ter leichter wieder auffinden zu k?nnen.','dispatch.php/messages/overview','3.1',0,0,1,'','',1406641688),('6acc653cfabd3a0d4433ff0ab417bf6a','de','','','?bersicht ?ber gesendete, systeminterne Nachrichten, welche mit selbstgew?hlten Schl?sselw?rtern (sog. Tags) versehen werden k?nnen, um sie sp?ter leichter wieder auffinden zu k?nnen. ','dispatch.php/messages/sent','3.1',0,0,1,'','',1406641688),('690e6eff3e83a5f372ec99fc49cafeb2','de','','','Blubbern ist das Stud.IP Echtzeitforum, eine Mischform aus Forum und Chat. Andere k?nnen ?ber einen Beitrag informiert werden, indem sie per @benutzername oder @\"Vorname Nachname\" im Beitrag erw?hnt werden. Texte lassen sich formatieren und durch Smileys erg?nzen.','plugins.php/blubber/streams/global','3.1',0,0,1,'','',1406641688),('cd69b74cd46172785bf2147fb0582e3c','de','','','Hier kann ein benutzerdefinierter Blubber-Stream erstellt werden. Er besteht immer aus einer Sammlung von Beitr?gen aus ausgew?hlten Veranstaltungen, Kontaktgruppen und Schlagw?rten, die auf Basis einer Filterung noch weiter eingeschr?nkt werden k?nnen. Der neue benutzerdefinierte Stream findet sich nach dem Klick auf den Speichern-Button in der Navigation unter Globaler Stream.','plugins.php/blubber/streams/edit','3.1',0,0,1,'','',1406641688),('394a45f94e1d84d3744027a5a69d9e3e','de','','','Auf dieser Seite l?sst sich einsehen, welche Kontakte gerade online sind. Diesen Personen kann eine Nachricht geschickt werden. Das Klicken auf den Namen einer Person leitet zu deren Profil weiter.','dispatch.php/online','3.1',0,0,1,'','',1406641688),('245ce01d7a0175ab0b977ae822821e9e','de','','','Diese Seite bietet die M?glichkeit Stud.IP-Nutzende in das eigene Adressbuch einzutragen und alle bereits im Adressbuch befindlichen Kontakte aufzulisten.','contact.php','3.1',0,0,1,'','',1406641688),('752d441cd321b05c55c8a5d9aa48ddce','de','','','Auf dieser Seite k?nnen Kontakte aus dem Adressbuch in selbstdefinierte Gruppen sortiert werden.','contact_statusgruppen.php','3.1',0,0,1,'','',1406641688),('94a193baa212abbc9004280a1498e724','de','','','Hier k?nnen Kontaktgruppen oder das gesamte Adressbuch exportiert werden, um sie in einem externen Programm importieren zu k?nnen.','contact_export.php','3.1',0,0,1,'','',1406641688),('7ebdd278d06f9fc1d2659a54bb3171c1','de','','','Die Rangliste sortiert die Stud.IP-Nutzenden absteigend anhand ihrer Punktzahl. Die Punktzahl w?chst mit den Aktivit?ten in Stud.IP und repr?sentiert so die Erfahrung der Nutzenden mit dem System. Indem das K?stchen links mit einem Haken versehen wird, wird der eigene Wert f?r andere NutzerInnen in der Rangliste sichtbar gemacht. In der Grundeinstellung ist der eigene Wert nicht ?ffentlich sichtbar.','dispatch.php/score','3.1',0,0,1,'','',1406641688),('82537b14dd3714ec9636124ed5af3272','de','','','Die Profilseite erm?glicht die ?nderung der eigenen persönliche Angaben inkl. Profilbild und Kategorien. ?hnlich wie in Facebook k?nnen Kommentare hinterlassen werden. Das Profil von Lehrenden enth?lt Sprechstunden und Raumangaben. Daneben bietet die Seite die Verwaltung eigener Dateien.','dispatch.php/profile','3.1',0,0,1,'','',1406641688),('ebb5bc1d831d460c06e3c6662236c159','de','','','Hier kann ein Profilbild hochgeladen werden.','dispatch.php/settings/avatar','3.1',0,0,1,'','',1406641688),('25255dc15fd0d6260bc1abd1f10aecc5','de','','','Individuelle persönliche Angaben, wie bspw. E-Mail-Adresse, k?nnen auf dieser Seite ver?ndert und angepasst werden. ','dispatch.php/settings/account','3.1',0,0,1,'','',1406641688),('d704267767d4c559aa9e552be60c49b5','de','','','Hier kann das Passwort f?r den Stud.IP-Account ge?ndert werden.','dispatch.php/settings/password','3.1',0,0,1,'','',1406641688),('cbd9b2b22fc00bc92df3589018644b70','de','','','Hier k?nnen vordefinierte Informationen ?ber die eigene Person eingegeben werden, die auf der Profilseite erscheinen sollen. ','dispatch.php/settings/details','3.1',0,0,1,'','',1406641688),('4e60dd9635f3d3fddecc78e0d1f646c7','de','','','Unter \"Studiendaten\" k?nnen manuell zus?tzliche Studieng?nge und Einrichtungen hinzugef?gt werden, wenn sie nicht automatisch aus einem externen System (z.B. LSF/ UniVZ) ?bernommen wurden.','dispatch.php/settings/studies','3.1',0,0,1,'','',1406641688),('462f1447b1a8a93ab7bdb2524f968b1a','de','','','Hier kann die Zugeh?rigkeit zu Nutzerdom?nen eingesehen, aber nicht ge?ndert werden.','dispatch.php/settings/userdomains','3.1',0,0,1,'','',1406641688),('233564d01b8301ebec7ef2fe918d1290','de','','','Ansicht ?ber die der/ dem Stud.IP-NutzerIn zugeordneten Einrichtungen.','dispatch.php/settings/statusgruppen','3.1',0,0,1,'','',1406641688),('e315a4c547be7f17d427b227f0f9d982','de','','','Auf dieser Seite k?nnen selbstdefinierte Informationen ?ber die eigene Person eingegeben werden, die auf der Profilseite erscheinen sollen. ','dispatch.php/settings/categories','3.1',0,0,1,'','',1406641688),('ac5df1de9c75fc92af7718b2103d3037','de','','','Blubbern ist eine Mischform aus Forum und Chat. Nachrichten werden im ?ffentlichen Stream dargestellt. Andere Nutzer k?nnen ?ber einen Beitrag informiert werden, indem sie per @benutzername oder @\"Vorname Nachname\" im Beitrag erw?hnt werden.','plugins.php/blubber/streams/profile','3.1',0,0,1,'','',1406641688),('4151003175042b71bea3529e5adc5a9e','de','','','Mit der Terminvergabe k?nnen Termine f?r Sprechstunden, Pr?fungen usw. angelegt werden, in die sich Studierende selbst eintragen k?nnen.','plugins.php/homepageterminvergabeplugin/showadmin','3.1',0,0,1,'','',1406641688),('63c2ecb12f30816aef0fb203eab4f40a','de','','','Hier k?nnen Termine angelegt und bearbeitet werden.','plugins.php/homepageterminvergabeplugin/show_category','3.1',0,0,1,'','',1406641688),('164f77ab2cb7d38fd1ea20ed725834fd','de','','','Hier findet sich eine ?bersicht ?ber die Termine, die von Studierenden gebucht wurden.','plugins.php/homepageterminvergabeplugin/show_bookings','3.1',0,0,1,'','',1406641688),('1289e991a93dce5a0b4edd678514325e','de','','','Hier k?nnen einzelne Inhaltselemente nachtr?glich aktiviert oder deaktiviert werden. Aktivierte Inhaltselemente f?gen neue Funktionen zu Ihrem Profil oder Ihren Einstellungen hinzu. Diese werden meist als neuer Reiter im Men? erscheinen. Wenn Funktionalit?ten nicht ben?tigt werden, k?nnen diese hier deaktiviert werden. Die entsprechenden Men?punkte werden dann ausgeblendet.','dispatch.php/profilemodules','3.1',0,0,1,'','',1406641688),('b677e8b5f1bd7e8acbe474177449c4e1','de','','','Die Dateiverwaltung bietet die M?glichkeit zum Hochladen, Verwalten und Herunterladen pers?nlicher Dateien, die nicht f?r andere einsehbar sind. ','dispatch.php/document/files','3.1',0,0,1,'','',1406641688),('35b1860b95854a2533b6ecfbbf04ab71','de','','','Der Stundenplan besteht aus abonnierten Veranstaltungen, die ein- und ausgeblendet sowie in Darstellungsgr??e und -form angepasst werden k?nnen.','dispatch.php/calendar/schedule','3.1',0,0,1,'','',1406641688),('db5a995bd12ba8e2ae96adcabeb8c8f7','de','','','Der Terminkalender besteht aus abonnierten Veranstaltungen und eigenen Terminen. Er kann bearbeitet, in der Anzeige ver?ndert und mit externen Programmen (z.B. Outlook) abgeglichen werden. ','calendar.php','3.1',0,0,1,'','',1406641688),('87489a40097e5c26f1d1349c072610de','de','','','Mit der Veranstaltungssuche k?nnen Veranstaltungen, Studiengruppen usw. in verschiedenen Semestern und nach verschiedenen Suchkriterien (siehe \"Erweiterte Suche anzeigen\"in der Sidebar) gefunden werden. Das aktuelle Semester ist vorgew?hlt.','dispatch.php/search/courses','3.1',0,0,1,'','',1406641688),('74863847eec53a3d4c8264d8de526be8','de','','','Mit der Archivsuche k?nnen Veranstaltungen gefunden werden, die bereits archiviert wurden.','archiv.php','3.1',0,0,1,'','',1406641688),('14b77e9e0b7773c92db9e7344a23fcfc','de','','','Mit der Personensuche k?nnen NutzerInnen gefunden werden, solange deren Privatsph?re-Einstellung dies nicht verhindert. Die Suche kann auf bestimmte Veranstaltungen oder Einrichtungen begrenzt werden.','browse.php','3.1',0,0,1,'','',1406641688),('4f9d79fe88e81486b8c1f192d70232d5','de','','','Mit der Einrichtungssuche k?nnen Einrichtungen ?ber ein freies Suchfeld oder den Einrichtungsbaum gefunden werden.','institut_browse.php','3.1',0,0,1,'','',1406641688),('014a2106d384c0ca55d9311597029ca0','de','','','Mit der Ressourcensuche k?nnen universit?re Ressourcen wie R?ume, Geb?ude etc. gefunden werden.','resources.php','3.1',0,0,1,'','',1406641688),('60b6caf75d0004dfdb0a1adfd66027ed','de','','','Hier k?nnen Lehrende Ank?ndigungen f?r ihre Veranstaltungen, Einrichtungen und ihre Profilseite erstellen und anzeigen, wobei die Anzeige gefiltert werden kann.','dispatch.php/news/admin_news','3.1',0,0,1,'','',1406641688),('f3deb7a01205637d71a66e2b90b24cba','de','','','Hier k?nnen RSS-Feeds, d.h. Nachrichtenstr?me von externen Internetseiten, auf der Startseite eingebunden werden. Je mehr Feeds eingebunden werden, desto l?nger dauert das Laden der Startseite.','dispatch.php/admin/rss_feeds','3.1',0,0,1,'','',1406641688),('bc1d6ecab9364cfe2c549d262bfda437','de','','','Die Lernmodulschnittstelle erm?glicht es, Selbstlerneinheiten aus externen Programmen wie ILIAS und LON-CAPA in Stud.IP zur Verf?gung zu stellen. F?r jedes externe System wird ein eigener Benutzer-Account erstellt oder zugeordnet. Mit den entsprechenden Rechten k?nnen eigene Lernmodule erstellt werden.','dispatch.php/elearning/my_accounts','3.1',0,0,1,'','',1406641688),('d1de152db139d8c12552610d2f7999c2','de','','','Mit dem Export k?nnen Daten ?ber Veranstaltungen und MitarbeiterInnen in folgende Formate exportiert werden: RTF, TXT, CSV, PDF, HTML und XML.','export.php','3.1',0,0,1,'','',1406641688),('2c55eab1f52d6f7d1021880836906f5b','de','','','Hier lassen sich Literaturlisten bearbeiten und in der Veranstaltung sichtbar schalten (mit Klick auf das \"Auge\").','dispatch.php/literature/edit_list.php','3.1',0,0,1,'','',1406641688),('c8e789a0efb73f00f00dacf565524c73','en','','','Various display options and notification features can be selected and changed in the general settings.','dispatch.php/settings/general','3.1',0,0,1,'','',1412942388),('f5e59c4fc98e1df7fe29b8e9320853e7','en','','','The visibility and searchability for the own profile can be set in the privacy settings.','dispatch.php/settings/privacy','3.1',0,0,1,'','',1412942388),('3b7a4c04017fef2984ee029610194f26','en','','','The settings of the message system offer the possibility e.g. to arrange for a forwarding of the messages received in Stud.IP to your e-mail address.','dispatch.php/settings/messaging','3.1',0,0,1,'','',1412942388),('260ee12fdc7dccb30eca2cc075ef0096','en','','','The settings of the diary offer the possibility to adjust these to own needs .','dispatch.php/settings/calendar','3.1',0,0,1,'','',1412942388),('43df8e33145c25eb6d941e4e845ada24','en','','','In the notification settings it is possible to select with which changes within a course notification is to be given.','dispatch.php/settings/notification','3.1',0,0,1,'','',1412942388),('85c000e33732c5596d198776cb884860','en','','','In the standard substitution settings lecturers can stipulate a standard substitution, which can manage and change all courses of the lecturer.','dispatch.php/settings/deputies','3.1',0,0,1,'','',1412942388),('b05b27450e363c38c6b4620b902b3496','en','','','The start page will be displayed after the log-in and can be customised to personal needs by using widgets.','dispatch.php/start','3.1',0,0,1,'','',1412942388),('91d6f451c3ef8d8352a076773b0a19ee','en','','','The course page shows all subscribed courses (as a standard only those of the last two semesters), all subscribed study groups as well as all institutions, to which one was allocated. You can customise the display through colour groupings, semester filters, etc. ','dispatch.php/my_courses','3.1',0,0,1,'','',1412942388),('0237ea35a203be81e44c979d82ef5ee6','en','','','Archived courses to which the user is allocated appear here. Contents can no longer be changed, however deposited documents can be downloaded as a zip file.','dispatch.php/my_courses/archive','3.1',0,0,1,'','',1412942388),('d97eff1196f6aed8e94f7c5096ebd2a9','en','','','Course-related brief and detailed information, announcements, dates and surveys can be found in the overview.','dispatch.php/course/overview','3.1',0,0,1,'','',1412942388),('357bbf06015b2738aae15837f581a07d','en','','','More detailed information about the course is displayed, such as e.g. the course number, allocations, lecturers, tutors, etc. In the detailed information it is possible to register for a course under actions.','dispatch.php/course/details','3.1',0,0,1,'','',1412942388),('0c055cc6ae418a96ff3afa9db13098df','en','','','The properties of the course can be subsequently changed with the management functions. The simulation of the students view is possible under actions.','dispatch.php/course/management','3.1',0,0,1,'','',1412942388),('615c1887f0ee080043f133681ebf0def','en','','','Title, description, lecturer, etc. can be changed in the basic data. The processing can partly be blocked if data are taken over from other systems (e.g. LSF/ UniVZ).','dispatch.php/course/basicdata/view','3.1',0,0,1,'','',1412942388),('abfb5d03de288d02df436f9a8bb96d9d','en','','','The photo of the course, which can help students to distinguish between courses on the \"my courses\" page, can be changed with the photo-upload-function.','dispatch.php/course/avatar/update','3.1',0,0,1,'','',1412942388),('eec46c5d8ea5523d959a8c334455c2ef','en','','','The course can be allocated to a field of study by using the field of study function. The processing can be blocked if data are taken over from other systems (e.g. LSF/ UniVZ).','dispatch.php/course/study_areas/show','3.1',0,0,1,'','',1412942388),('85c709de75085bd56a739e4e8ac6fcad','en','','','The semester, date and room details of the course can be changed by using the time/room function. The processing can be blocked if data are taken over from other systems (e.g. LSF/ UniVZ).','raumzeit.php','3.1',0,0,1,'','',1412942388),('4e14c94cda99e2ef6462f7fef06d9c91','en','','','The access to a course can be regulated with access authorisations (enrolment procedure) e.g. by passwords, time control and restriction to participants.','dispatch.php/course/admission','3.1',0,0,1,'','',1412942388),('42060187921376807f90e52fad5f9822','en','','','(Time-controlled) surveys or individual Multiple-/Single-Choice questions can be set up for courses, study groups or the profile by using the survey and test function.','admin_vote.php','3.1',0,0,1,'','',1412942388),('5475d65b07fdaf5f234bf6eed3d5e4a9','en','','','With the evaluation function surveys can be set up with Multiple-Choice, Likert and free text questions for courses, study groups, the own profile or institutions. Public templates of other persons can be used hereby. All future, ongoing and ended evaluations are displayed.','admin_evaluation.php','3.1',0,0,1,'','',1412942388),('80286432bf17df20e5f11f86b421b0a7','en','','','The forum is a text-based possibility, which is irrespective of time and place, to exchange questions, opinions and experience. Contributions can be subscribed to, exported, marked as favourites and edited. Various views (e.g. new contributions since the last login) can be chosen via the navigation links.','plugins.php/coreforum','3.1',0,0,1,'','',1412942388),('3607d6daea679dcd7003e076fdd1660a','en','','','The list of participants shows a list of the participants of this course. Further participants can be added,  removed, downgraded, upgraded or allocated to self-defined groups by lecturers.','dispatch.php/course/members','3.1',0,0,1,'','',1412942388),('f529bca4d1626b43cbb8149feea41a84','en','','','The self-defined groups are displayed here. Messages can be sent to these. A click on the orange arrows before the group name will allocate you to the group.','statusgruppen.php','3.1',0,0,1,'','',1412942388),('bd5df4fb7b84da79149c96c5f43de46c','en','','','Groups can be set up and managed here. If the self-entry is activated participants can enter and remove themselves.','admin_statusgruppe.php','3.1',0,0,1,'','',1412942388),('8c2fc90bd8175e6d598f895944a8ddc2','en','','','The attendance list shows all meetings (meeting, lecture, exercise, internship) of the schedule and enables the entry of students in Stud.IP by the lecturers as well as an export of the list for the overview or as a basis for handwritten entries.','participantsattendanceplugin/show','3.1',0,0,1,'','',1412942388),('ee91ec0f9085221ada06d171a27d2405','en','','','The document administration offers the possibility to upload, link, administer and download documents. ','folder.php','3.1',0,0,1,'','',1412942388),('8c3067596811d3c6857d253299e01f6f','en','','','The schedule displays dates, topics and rooms of the course. Individual dates can be edited, e.g. topics can be added to dates.','dispatch.php/course/dates','3.1',0,0,1,'','',1412942388),('1f216fe42d879c3fcbb582d67e9ad5a2','en','','','Dates can be allocated topics here or already entered topics can be taken over and edited.','dispatch.php/course/topics','3.1',0,0,1,'','',1412942388),('abaa7b076e6923ac43120f3326322af0','en','','','This page enables the deposit of free information, links, etc.','dispatch.php/course/scm','3.1',0,0,1,'','',1412942388),('7edc08f2f7b0786ca036f8c448441e07','en','','','The Wiki enables a joint, asynchronous creation and editing of texts. Texts can be formatted and linked with each other so that a branched reference work is produced. ','wiki.php','3.1',0,0,1,'','',1412942388),('44edb997707d1458cbf8a3f8f316b908','en','','','The bibliography page offers lecturers the possibility to create bibliographies or to import these from bibliography management programmes. These lists can be copied and placed visibly in courses. Research can be conducted in the actual book stocks of the university depending on the connection. ','dispatch.php/course/literature','3.1',0,0,1,'','',1412942388),('1cb8fd77427ebc092d751eea95454b0a','en','','','Bibliographies can be edited here and placed visibly in the course (with a click on the \"eye\").','dispatch.php/literature/edit_list','3.1',0,0,1,'','',1412942388),('b283b58820db358284f4451dfb691678','en','','','A search can be conducted for literature in catalogues here and these added to the clipboard.','dispatch.php/literature/search','3.1',0,0,1,'','',1412942388),('0d83ce036f2870f873446230c0118bb7','en','','','The learning module interface makes it possible for self-learning units or tests to be made available from external programmes such as ILIAS and LON-CAPA in Stud.IP.','dispatch.php/course/elearning/show','3.1',0,0,1,'','',1412942388),('8b690f942bf0cc0322e5bea0f1b9abed','en','','','Select the requested system and subsequently the learning module/ the test. Writing rights determine who may edit the learning module in future. The option \"update allocations\" is located in the sidebar in order to transfer changed contents e.g. in the ILIAS course to Stud.IP.','dispatch.php/course/elearning/edit','3.1',0,0,1,'','',1412942388),('0838a96b5678e2fc26be0ee38ae67619','en','','','In DoIT! lecturers have the possibility to set various types of tasks, including the uploading of files, Multiple-Choice questions and Peer Reviewing. The processing of tasks can be time limited and alternatively carried out in groups.','plugins.php/reloadedplugin/show','3.1',0,0,1,'','',1412942388),('1804e526c2f6794b877a4b2096eaa67a','en','','','Blubbering is a mixed form of forum and chat, with which contributions of the participants are displayed in real time. Others can be informed about a contribution by the fact that they are mentioned in the contribution by @user name or @\'first name\'.','plugins.php/blubber/streams/forum','3.1',0,0,1,'','',1412942388),('38d1a86517eb6cc195b2e921270c3035','en','','','The group calendar offers an overview of course dates and personalised additional dates for this course. ','plugins.php/gruppenkalenderplugin/show','3.1',0,0,1,'','',1412942388),('852991dc733639dd2df05fb627abf3db','en','','','Further features can be added to the course here.','dispatch.php/course/plus','3.1',0,0,1,'','',1412942388),('1ea099717ceb1b401aedcedc89814d9c','en','','','The learning diary supports the self-controlled learning process of the students and is kept independently by them. Enquiries for work steps to the lecturers are possible, certain data can be released individualised.','plugins.php/lerntagebuchplugin/overview','3.1',0,0,1,'','',1412942388),('2075fe42f56207fbd153a810188f1beb','en','','','Configuration of the learning diary for students and creation of a learning diary for the lecturers.','plugins.php/lerntagebuchplugin/admin_settings','3.1',0,0,1,'','',1412942388),('7465a4aeedb6a320d3455cf9ad0bebd0','en','','','Possibility for providing lecture recordings and pod casts for students of the course (by linking to the documents on the media server). ','plugins.php/mediacastsplugin/show','3.1',0,0,1,'','',1412942388),('02b4e3ce7b8fe6b3e6a3586d410a51a1','en','','','This page displays the study groups to which the user is allocated. Study groups are a simple possibility to cooperate with fellow students, colleagues and others. Each user can create study groups or search for them. The colour grouping can be adjusted individually.','dispatch.php/my_studygroups','3.1',0,0,1,'','',1412942388),('af7573cce1e898054db89a96284866f9','en','','','A new study group can be created here. Each Stud.IP user can create study groups and configure these according to own needs.','dispatch.php/course/studygroup/new','3.1',0,0,1,'','',1412942388),('960d7bafb618853eced1b1b42a7dd412','en','','','This page displays all study groups, which exist in Stud.IP. Study groups are a simple possibility to cooperate with fellow students, colleagues and others. Each user can create study groups or search for them.','dispatch.php/studygroup/browse','3.1',0,0,1,'','',1412942388),('3d040e95a8c29e733a8d5439ee9f5b59','en','','','The name, functions and access restriction of the study group can be edited here.','dispatch.php/course/studygroup/edit','3.1',0,0,1,'','',1412942388),('b3bd33cb0babbb0cc51a4f429d15d438','en','','','Here you can add new memebers to the study group und send them messages.','dispatch.php/course/studygroup/members','3.1',0,0,1,'','',1412942388),('438c4456f85afec29fd9f47c111136c1','en','','','This page displays the institutions to which the user is allocated.','dispatch.php/my_institutes','3.1',0,0,1,'','',1412942388),('f966e348174927565b94e606bbcf064f','en','','','The message page offers an overview on received, system-internal messages, which can be issued with self-chosen key words (so-called tags) in order to subsequently be able to find them easier.','dispatch.php/messages/overview','3.1',0,0,1,'','',1412942388),('ceb21257092b11dcf6897d5bb3085642','en','','','Overview on sent, system-internal messages, which can be issued with self-chosen key words (so-called \"tags\") in order to subsequently be able to find them easier. ','dispatch.php/messages/sent','3.1',0,0,1,'','',1412942388),('b9586c280a0092f86f9392fe5b5ff2a0','en','','','Blubbering is the Stud.IP real time forum, a mixed form of forum and chat. Others can be informed about a contribution by the fact that they are mentioned in the contribution by @user name or @\'first name\'. Texts can be formatted and supplemented by Smileys.','plugins.php/blubber/streams/global','3.1',0,0,1,'','',1412942388),('7cb7026818c4b90935009d0548300674','en','','','A user-defined blubber stream can be created here. It always consists of a collection of contributions from selected courses, contact groups and key words, which can be restricted even further based on a filtering. The new user-defined stream can be found after clicking on the save button in the navigation under global stream.','plugins.php/blubber/streams/edit','3.1',0,0,1,'','',1412942388),('2f1602394a4e31c2e30706f0a0b3112f','en','','','On this page it can be viewed which contacts are online at the moment. A message can be sent to these persons. The clicking on the name of one person forwards to their profile.','dispatch.php/online','3.1',0,0,1,'','',1412942388),('27c4d9837cfb1a9a40c079e16daac902','en','','','This page offers the possibility to enter Stud.IP users in the address book and to list all contacts who can already be found in the address book.','contact.php','3.1',0,0,1,'','',1412942388),('362a67fff2ef7af8cca9f8e20583c9f2','en','','','Contacts from the address book can be displayed sorted according to the groups here.','???','3.1',0,0,1,'','',1412942388),('6b331f5cc2176daba82a0cc71aaa576f','en','','','Contacts from the address book can be sorted in self-defined groups on this page.','contact_statusgruppen.php','3.1',0,0,1,'','',1412942388),('57f1b29d3c1a558f5cc799c1aade7f14','en','','','Contact groups or the whole address book can be exported here in order to be able to import them in an external programme.','contact_export.php','3.1',0,0,1,'','',1412942388),('90ffbd715843b02b3961907f81caf208','en','','','The ranking sorts the Stud.IP users in descending order based on their number of points. The number of points will grow with the activities in Stud.IP and thus represents the experience of the users with the system. By entering a check mark in the little box on the left the own value will be made visible in the ranking for other users. The own value is not visible to the public in the basic settings.','dispatch.php/score','3.1',0,0,1,'','',1412942388),('e5bff29f7adee43202a2aa8f3f0a6ec7','en','','','You can change your own user data incl. profile photo and categories here. Comments can be left similar to Facebook. The profile of lecturers includes consulting hours and room details. In addition the page offers the administration of own documents.','dispatch.php/profile','3.1',0,0,1,'','',1412942388),('2a389c2472656121a76ca4f3b0e137d4','en','','','A profile photo can be uploaded here.','dispatch.php/settings/avatar','3.1',0,0,1,'','',1412942388),('fe23b56f4d691c0f5e2f872e37ce38b5','en','','','You can change and customise your individual user data, such as for example mail address, on this page. ','dispatch.php/settings/account','3.1',0,0,1,'','',1412942388),('b32cb2c4ec56e925b07a5cb0105a6888','en','','','The password for the Stud.IP-Account can be changed here.','dispatch.php/settings/password','3.1',0,0,1,'','',1412942388),('83fd70727605c485a0d8f2c5ef94289b','en','','','Pre-defined information about the own person can be entered here, which is to appear on the profile page. ','dispatch.php/settings/details','3.1',0,0,1,'','',1412942388),('970ebdf39ad5ca89083a52723c5c35f5','en','','','Additional courses of study and institutions can be added manually under \"study details\", if they are not automatically taken over from an external system (e.g. LSF/UniVZ).','dispatch.php/settings/studies','3.1',0,0,1,'','',1412942388),('0e816d9428a3bc8a73fb0042fb2da540','en','','','The affiliation to user domains can be viewed, however not changed, here.','dispatch.php/settings/userdomains','3.1',0,0,1,'','',1412942388),('d04ca1f9e867ee295a3025dac7ce9c7b','en','','','View of the institutions allocated to the Stud.IP user.','dispatch.php/settings/statusgruppen','3.1',0,0,1,'','',1412942388),('8ad364363acd415631226d5574d5592a','en','','','Self-defined information about the own person can be entered on this page, which is to appear on the profile page. ','dispatch.php/settings/categories','3.1',0,0,1,'','',1412942388),('51a0399250de6365619c961ec3669ad3','en','','','Blubbering is a mixed form of forum and chat. Messages are presented in the public stream. Other users can be informed about a contribution by the fact that they are mentioned in the contribution by @user name or @\'first name last name\'.','plugins.php/blubber/streams/profile','3.1',0,0,1,'','',1412942388),('5ae72abc0822570bfe839e3ee24f0c81','en','','','With the allocation of dates, dates can be created for consulting hours, examinations, etc., in which students can enter themselves.','plugins.php/homepageterminvergabeplugin/showadmin','3.1',0,0,1,'','',1412942388),('76195b21d485823fd7ca2fd499131c12','en','','','Dates can be created and edited here.','plugins.php/homepageterminvergabeplugin/show_category','3.1',0,0,1,'','',1412942388),('0ad754cc62d1e86e97c1a28dd68ac40c','en','','','An overview on the dates, which were booked by students can be found here.','plugins.php/homepageterminvergabeplugin/show_bookings','3.1',0,0,1,'','',1412942388),('b5fabb1e5aed7ff8520314e9a86c5c87','en','','','Individual content elements can be subsequently activated or deactivated here. Activated content elements add new functions to your profile or your settings. These will mostly appear as a new tab in the menu. If functionalities are not required these can be deactivated here. The corresponding menu tabs are then faded out.','dispatch.php/profilemodules/index','3.1',0,0,1,'','',1412942388),('51b98d659590e1e37dae5e5e5cc028bb','en','','','The document administration offers the possibility to upload, manage and download personal documents,which cannot be viewed by others. ','dispatch.php/document/files','3.1',0,0,1,'','',1412942388),('440e50f7fcc825368aa9026273d2cd0d','en','','','The schedule of studies consists of subscribed courses, which can be faded in and out as well as customised in the size and form of the presentation.','dispatch.php/calendar/schedule','3.1',0,0,1,'','',1412942388),('dddf5fd4406da0d91c9f121fcae607ad','en','','','The diary consists of subscribed courses and own dates. It can be edited, change in the display and compared with external programmes (e.g. Outlook). ','calendar.php','3.1',0,0,1,'','',1412942388),('a1e3da35edc9b605f670e9c7f5019888','en','','','With the course search courses, study groups, etc. can be found in various semesters and according to various search criteria (see \"display extended search\" in the sidebar). The current semester is pre-selected.','dispatch.php/search/courses','3.1',0,0,1,'','',1412942388),('7d40379f54250b550065e062d71e8fd8','en','','','Various courses can be found with the archive search, which have already been archived.','archiv.php','3.1',0,0,1,'','',1412942388),('ebcc460880b8a63af3f6e7eade97db78','en','','','Users can be found with the search for persons as long as their privacy setting does not prevent this. The search can be limited to certain courses or institutions.','browse.php','3.1',0,0,1,'','',1412942388),('8a32ca4e602a68307d4ae6ae51fa667e','en','','','With the institution search institutions can be found via a free search field or the institution tree','institut_browse.php','3.1',0,0,1,'','',1412942388),('e206a4257e31a0f32ac516cefb8e8331','en','','','University resources such as rooms, buildings, etc. can be found using the resource search.','resources.php','3.1',0,0,1,'','',1412942388),('3318ee99a062079b463e902348ad520e','en','','','Lecturers can create and display announcements for their courses, institutions and their profile page here, whereby the display can be filtered.','dispatch.php/news/admin_news','3.1',0,0,1,'','',1412942388),('bcdedaf1b4bd3b96ef574e8230095b28','en','','','RSS-Feeds, i.e. message streams of external websites, can be integrated onto the start page here. The more feeds are integrated, the longer the loading of the start page will take.','dispatch.php/admin/rss_feeds','3.1',0,0,1,'','',1412942388),('bfb70d5f036769d740fb2342b0b58183','en','','','The learning module interface makes it possible for self-learning units to be made available from external programmes such as ILIAS and LON-CAPA in Stud.IP. An own user account will be created or allocated for each external system. Own learning modules can be created with the corresponding rights.','dispatch.php/elearning/my_accounts','3.1',0,0,1,'','',1412942388),('7bf322a6c5f13db67e047b7afae83e58','en','','','With the export data about courses and employees can be exported in the following formats: RTF, TXT, CSV, PDF, HTML and XML.','export.php','3.1',0,0,1,'','',1412942388),('fa4bf491690645a5f12556f77e51233c','en','','','Bibliographies can be edited here and placed visibly in the course (with a click on the \"eye\").','dispatch.php/literature/edit_list.php','3.1',0,0,1,'','',1412942388);
/*!40000 ALTER TABLE `help_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_tour_audiences`
--

DROP TABLE IF EXISTS `help_tour_audiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_tour_audiences` (
  `tour_id` char(32) NOT NULL,
  `range_id` char(32) NOT NULL,
  `type` enum('inst','sem','studiengang','abschluss','userdomain','tour') NOT NULL,
  PRIMARY KEY (`tour_id`,`range_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_tour_audiences`
--

LOCK TABLES `help_tour_audiences` WRITE;
/*!40000 ALTER TABLE `help_tour_audiences` DISABLE KEYS */;
/*!40000 ALTER TABLE `help_tour_audiences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_tour_settings`
--

DROP TABLE IF EXISTS `help_tour_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_tour_settings` (
  `tour_id` varchar(32) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `access` enum('standard','link','autostart','autostart_once') DEFAULT NULL,
  PRIMARY KEY (`tour_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_tour_settings`
--

LOCK TABLES `help_tour_settings` WRITE;
/*!40000 ALTER TABLE `help_tour_settings` DISABLE KEYS */;
INSERT INTO `help_tour_settings` VALUES ('96ea422f286fb5bbf9e41beadb484a9a',1,'standard'),('25e7421f286fc5bdf9e41beadb484ffa',1,'standard'),('3a717a468afb0822cb1455e0ae6b6fce',1,'standard'),('21f487fa74e3bfc7789886f40fe4131a',1,'standard'),('44f859c50648d3410c39207048ddd833',1,'standard'),('ef5092ba722c81c37a5a6bd703890bd9',1,'standard'),('6849293baa05be5bef8ff438dc7c438b',1,'standard'),('b74f8459dce2437463096d56db7c73b9',1,'standard'),('154e711257d4d32d865fb8f5fb70ad72',1,'standard'),('19ac063e8319310d059d28379139b1cf',1,'standard'),('edfcf78c614869724f93488c4ed09582',1,'standard'),('977f41c5c5239c4e86f04c3df27fae38',0,'autostart_once'),('49604a77654617a745e29ad6b253e491',1,'standard'),('3629493a16bf2680de64361f07cab096',1,'standard'),('7cccbe3b22dfa745c17cb776fb04537c',1,'standard');
/*!40000 ALTER TABLE `help_tour_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_tour_steps`
--

DROP TABLE IF EXISTS `help_tour_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_tour_steps` (
  `tour_id` char(32) NOT NULL DEFAULT '',
  `step` tinyint(4) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL DEFAULT '',
  `tip` text NOT NULL,
  `orientation` enum('T','TL','TR','L','LT','LB','B','BL','BR','R','RT','RB') NOT NULL DEFAULT 'B',
  `interactive` tinyint(4) NOT NULL,
  `css_selector` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL DEFAULT '',
  `author_id` char(32) NOT NULL DEFAULT '',
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tour_id`,`step`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_tour_steps`
--

LOCK TABLES `help_tour_steps` WRITE;
/*!40000 ALTER TABLE `help_tour_steps` DISABLE KEYS */;
INSERT INTO `help_tour_steps` VALUES ('96ea422f286fb5bbf9e41beadb484a9a',3,'Stud.IP-Score','Der Stud.IP-Score w?chst mit den Aktivit?ten in Stud.IP und repr?sentiert so die Erfahrung mit Stud.IP.','BL',0,'#layout_content TABLE:eq(0) TBODY:eq(0) TR:eq(0) TD:eq(0) A:eq(0)','dispatch.php/profile','',1406722657),('96ea422f286fb5bbf9e41beadb484a9a',5,'Neue Ank?ndigung','Klicken Sie auf das Plus-Zeichen, wenn Sie eine Ank?ndigung erstellen m?chten.','BR',0,'.contentbox:eq(0) header img:eq(1)','dispatch.php/profile','',1406722657),('96ea422f286fb5bbf9e41beadb484a9a',1,'Profil-Tour','Diese Tour gibt Ihnen einen ?berblick ?ber die wichtigsten Funktionen des \"Profils\".\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','T',0,'','dispatch.php/profile','',1406722657),('96ea422f286fb5bbf9e41beadb484a9a',6,'Pers?nliche Daten','Das Bild sowie weitere persönliche Angaben können ?ber diese Seiten ge?ndert werden.','BL',0,'#tabs li:eq(2)','dispatch.php/profile','',1406722657),('96ea422f286fb5bbf9e41beadb484a9a',2,'Pers?nliches Bild','Wenn ein Bild hochgeladen wurde, wird es hier angezeigt. Dieses kann jederzeit ge?ndert werden.','RT',0,'.avatar-normal','dispatch.php/profile','',1406722657),('25e7421f286fc5bdf9e41beadb484ffa',1,'Profil','Diese Tour gibt Ihnen einen ?berblick ?ber die wichtigsten Funktionen des \"Profils\".\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','B',0,'','dispatch.php/profile','',1406722657),('25e7421f286fc5bdf9e41beadb484ffa',2,'Bild hochladen','Auf dieser Seite l?sst sich ein Profilbild hochladen.','BL',0,'#nav_profile_avatar A SPAN','dispatch.php/settings/avatar','',1406722657),('25e7421f286fc5bdf9e41beadb484ffa',3,'Bild ausw?hlen','Daf?r kann eine beliebige Bilddatei hochgeladen werden.','L',0,'input[name=imgfile]','dispatch.php/settings/avatar','',1406722657),('96ea422f286fb5bbf9e41beadb484a9a',4,'Ank?ndigungen','Sie k?nnen auf dieser Seite pers?nliche Ank?ndigungen ver?ffentlichen.','B',0,'#layout_content SECTION HEADER H1 :eq(0)','dispatch.php/profile','',1406722657),('3629493a16bf2680de64361f07cab096',3,'Text gestalten','Der Text kann formatiert und mit Smileys versehen werden.\r\nEs k?nnen die ?blichen Formatierungen verwendet werden, wie z. B. **fett** oder %%kursiv%%.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/forum','',1405508371),('6849293baa05be5bef8ff438dc7c438b',4,'Navigation','Falls nur in einem bestimmten Bereich (wie z.B. Lehre) gesucht werden soll, kann dieser hier ausgew?hlt werden.','R',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(0)','dispatch.php/search/courses','',1406121826),('3a717a468afb0822cb1455e0ae6b6fce',3,'Text gestalten','Der Text kann formatiert und mit Smileys versehen werden.\r\nEs k?nnen die ?blichen Formatierungen verwendet werden, wie z. B. **fett** oder %%kursiv%%.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/profile','',1405508371),('3629493a16bf2680de64361f07cab096',9,'Beitrag verlinken','Wird der Mauszeiger auf dem ersten Diskussionsbeitrag positioniert, erscheint links neben dem Datum ein Link-Icon. Wenn dieses mit der rechten Maustaste angeklickt wird, kann der Link auf diesen Beitrag kopiert werden, um ihn an anderer Stelle einf?gen zu k?nnen.','BR',0,'DIV DIV A.permalink','plugins.php/blubber/streams/forum','',1405508281),('3629493a16bf2680de64361f07cab096',8,'Beitrag ?ndern','Wird der Mauszeiger auf einem beliebigen Beitrag positioniert, erscheint dessen Datum. Bei eigenen Beitr?gen erscheint au?erdem rechts neben dem Datum ein Icon, mit dem der Beitrag nachtr?glich ge?ndert werden kann.','BR',0,'DIV DIV A SPAN.time','plugins.php/blubber/streams/forum','',1405507901),('21f487fa74e3bfc7789886f40fe4131a',1,'Forum','Diese Tour gibt einen ?berblick ?ber die Elemente und Interaktionsm?glichkeiten des Forums.\r\n\r\nUm zum n?chsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".','BL',0,'','plugins.php/coreforum','',1405415772),('21f487fa74e3bfc7789886f40fe4131a',2,'Sie befinden sich hier:...','An dieser Stelle wird angezeigt, welcher Bereich des Forums gerade betrachtet wird.','BL',0,'DIV#tutorBreadcrumb','plugins.php/coreforum','',1405415875),('44f859c50648d3410c39207048ddd833',1,'Forum verwalten','Sie haben die M?glichkeit sich eine Tour zur Verwaltung des Forums anzuschauen.\r\n\r\nUm die Tour zu beginnen, klicken Sie bitte unten rechts auf \"Weiter\".','TL',0,'','plugins.php/coreforum','',1405418008),('21f487fa74e3bfc7789886f40fe4131a',3,'Kategorie','Das Forum ist unterteilt in Kategorien, Themen und Beitr?ge. Eine Kategorie fasst Forumsbereiche in gr??ere Sinneinheiten zusammen.','BL',0,'#layout_content #forum #sortable_areas TABLE CAPTION .category_name :eq(0)','plugins.php/coreforum','',1405416611),('21f487fa74e3bfc7789886f40fe4131a',4,'Bereich','Das ist ein Bereich innerhalb einer Kategorie. Bereiche beinhalten die Diskussionstr?nge. Bereiche k?nnen mit per drag & drop in ihrer Reihenfolge verschoben werden.','BL',0,'#layout_content #forum TABLE THEAD TR TH :eq(0)','plugins.php/coreforum','',1405416664),('21f487fa74e3bfc7789886f40fe4131a',5,'Info-Icon','Dieses Icon f?rbt sich rot, sobald es etwas neues in diesem Bereich gibt.','B',0,'IMG#tutorNotificationIcon','plugins.php/coreforum','',1405416705),('21f487fa74e3bfc7789886f40fe4131a',7,'Forum abonnieren','Das gesamte Forum, oder einzelne Themen k?nnen abonniert werden. Dann wird bei jedem neuen Beitrag in diesem Forum eine Benachrichtigung angezeigt und eine Nachricht versendet.','B',0,'#layout-sidebar SECTION DIV DIV UL LI A :eq(5)','plugins.php/coreforum','',1405416795),('21f487fa74e3bfc7789886f40fe4131a',6,'Suchen','Hier k?nnen s?mtliche Inhalte dieses Forums durchsucht werden.\r\nUnterst?tzt werden auch Mehrwortsuchen. Au?erdem kann die Suche auf eine beliebige Kombination aus Titel, Inhalt und Autor eingeschr?nkt werden.','BL',0,'#layout-sidebar SECTION #tutorSearchInfobox DIV #tutorSearchInfobox UL LI INPUT :eq(1)','plugins.php/coreforum','',1405417134),('44f859c50648d3410c39207048ddd833',2,'Kategorie bearbeiten','Mit diesen Icons kann der Name der Kategorie ge?ndert oder aber die gesamte Kategorie gel?scht werden. Die Bereiche werden in diesem Fall in die Kategorie \"Allgemein\" verschoben und bleiben somit erhalten.\r\n\r\nDie Kategorie \"Allgemein\" kann nicht gel?scht werden und ist daher in jedem Forum enthalten.','BR',0,'#forum #sortable_areas TABLE CAPTION #tutorCategoryIcons','plugins.php/coreforum','',1405424216),('44f859c50648d3410c39207048ddd833',3,'Bereich bearbeiten','Wird der Mauszeiger auf einem Bereich positioniert, erscheinen Aktions-Icons.\r\nMit diesen Icons kann der Name und die Beschreibung eines Bereiches ge?ndert oder auch der gesamte Bereich gel?scht werden.\r\nDas L?schen eines Bereichs, f?hrt dazu, dass alle enthaltenen Themen gel?scht werden.','B',0,'IMG.edit-area','plugins.php/coreforum','',1405424346),('44f859c50648d3410c39207048ddd833',4,'Bereiche sortieren','Mit dieser schraffierten Fl?che k?nnen Bereiche an einer beliebigen Stelle durch Klicken-und-Ziehen einsortiert werden. Dies kann einerseits dazu verwendet werden, um Bereiche innerhalb einer Kategorie zu sortieren, andererseits k?nnen Bereiche in andere Kategorien verschoben werden.','BR',0,'HTML #plugins #layout_wrapper #layout_page #layout_container #layout_content #forum #sortable_areas TABLE TBODY #tutorArea TD IMG#tutorMoveArea.handle.js :eq(1)','plugins.php/coreforum','',1405424379),('44f859c50648d3410c39207048ddd833',5,'Neuen Bereich hinzuf?gen','Hier k?nnen neue Bereiche zu einer Kategorie hinzugef?gt werden.','BR',0,'TFOOT TR TD A SPAN','plugins.php/coreforum','',1405424421),('44f859c50648d3410c39207048ddd833',6,'Neue Kategorie erstellen','Hier kann eine neue Kategorie im Forum erstellt werden. Geben Sie hierf?r den Titel der neuen Kategorie ein.','TL',0,'#tutorAddCategory H2','plugins.php/coreforum','',1405424458),('ef5092ba722c81c37a5a6bd703890bd9',1,'Was ist Blubbern?','Diese Tour gibt Ihnen einen ?berblick ?ber die wichtigsten Funktionen von \"Blubber\".\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','TL',0,'','plugins.php/blubber/streams/global','',1405507364),('ef5092ba722c81c37a5a6bd703890bd9',2,'Beitrag erstellen','Hier kann eine Diskussion durch Schreiben von Text begonnen werden. Abs?tze lassen sich durch Dr?cken von Umschalt+Eingabe erzeugen. Der Text wird durch Dr?cken von Eingabe abgeschickt.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/global','',1405507478),('ef5092ba722c81c37a5a6bd703890bd9',8,'Beitrag ?ndern','Wird der Mauszeiger auf einem beliebigen Beitrag positioniert, erscheint dessen Datum. Bei eigenen Beitr?gen erscheint au?erdem rechts neben dem Datum ein Icon, mit dem der Beitrag nachtr?glich ge?ndert werden kann.','BR',0,'DIV DIV A SPAN.time','plugins.php/blubber/streams/global','',1405507901),('ef5092ba722c81c37a5a6bd703890bd9',9,'Beitrag verlinken','Wird der Mauszeiger auf dem ersten Diskussionsbeitrag positioniert, erscheint links neben dem Datum ein Link-Icon. Wenn dieses mit der rechten Maustaste angeklickt wird, kann der Link auf diesen Beitrag kopiert werden, um ihn an anderer Stelle einf?gen zu k?nnen.','BR',0,'DIV DIV A.permalink','plugins.php/blubber/streams/global','',1405508281),('ef5092ba722c81c37a5a6bd703890bd9',3,'Text gestalten','Der Text kann formatiert und mit Smileys versehen werden.\r\nEs k?nnen die ?blichen Formatierungen verwendet werden, wie z. B. **fett** oder %%kursiv%%.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/global','',1405508371),('ef5092ba722c81c37a5a6bd703890bd9',5,'Datei hinzuf?gen','Dateien k?nnen in einen Beitrag eingef?gt werden, indem sie per Drag&Drop in ein Eingabefeld gezogen werden.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/global','',1405508401),('ef5092ba722c81c37a5a6bd703890bd9',6,'Schlagworte','Beitr?ge k?nnen mit Schlagworten (engl. \"Hashtags\") versehen werden, indem einem beliebigen Wort des Beitrags ein # vorangestellt wird.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/global','',1405508442),('ef5092ba722c81c37a5a6bd703890bd9',7,'Schlagwortwolke','Durch Anklicken eines Schlagwortes werden alle Beitr?ge aufgelistet, die dieses Schlagwort enthalten.','RT',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(1)','plugins.php/blubber/streams/global','',1405508505),('3a717a468afb0822cb1455e0ae6b6fce',9,'Beitrag verlinken','Wird der Mauszeiger auf dem ersten Diskussionsbeitrag positioniert, erscheint links neben dem Datum ein Link-Icon. Wenn dieses mit der rechten Maustaste angeklickt wird, kann der Link auf diesen Beitrag kopiert werden, um ihn an anderer Stelle einf?gen zu k?nnen.','BR',0,'DIV DIV A.permalink','plugins.php/blubber/streams/profile','',1405508281),('6849293baa05be5bef8ff438dc7c438b',1,'Suche','Diese Tour gibt Ihnen einen ?berblick ?ber die wichtigsten Funktionen der \"Suche\".\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','TL',0,'','dispatch.php/search/courses','',1405519865),('6849293baa05be5bef8ff438dc7c438b',2,'Suchbegriff eingeben','In dieses Eingabefeld kann ein Suchbegriff (wie z.B. der Veranstaltungsname, Dozent) eingegeben werden.','B',0,'INPUT#search_sem_quick_search_1.ui-autocomplete-input','dispatch.php/search/courses','',1405520106),('6849293baa05be5bef8ff438dc7c438b',3,'Semesterauswahl','Durch einen Klick auf das Drop-Down Men? kann bestimmt werden, auf welches Semester sich der Suchbegriff beziehen soll. \r\n\r\nStandardgem?? ist das aktuelle Semester eingestellt.','TL',0,'SELECT#search_sem_sem','dispatch.php/search/courses','',1405520208),('6849293baa05be5bef8ff438dc7c438b',5,'Erweiterte Suche','Mit der Erweiterten Suche kann die Suche um weitere Optionen erweitert werden.','R',0,'A.options-checkbox.options-unchecked','dispatch.php/search/courses','',1405520436),('6849293baa05be5bef8ff438dc7c438b',6,'Schnellsuche','Die Schnellsuche ist auch auf anderen Seiten von Stud.IP jederzeit verf?gbar. Nach der Eingabe eines Stichwortes, wird mit \"Enter\" best?tigt, oder auf die Lupe rechts neben dem Feld geklickt.','B',0,'INPUT#search_sem_quick_search_2.quicksearchbox.ui-autocomplete-input','dispatch.php/search/courses','',1405520634),('6849293baa05be5bef8ff438dc7c438b',7,'Weitere Suchm?glichkeiten','Neben Veranstaltungen besteht auch die M?glichkeit, im Archiv, nach Personen, nach Einrichtungen oder nach Ressourcen zu suchen.','R',0,'#nav_search_resources A SPAN','dispatch.php/search/courses','',1405520751),('b74f8459dce2437463096d56db7c73b9',1,'Hilfe-Tour \"Meine Veranstaltungen\"','Diese Tour gibt einen ?berblick ?ber die wichtigsten Funktionen der Seite \"Meine Veranstaltungen\".\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','TL',0,'','dispatch.php/my_courses','',1405521184),('b74f8459dce2437463096d56db7c73b9',2,'Veranstaltungs?berblick','Hier werden die  Veranstaltungen des aktuellen und vergangenen Semesters angezeigt. Neue Veranstaltungen erscheinen zun?chst in rot.','T',0,'#my_seminars TABLE THEAD TR TH :eq(2)','dispatch.php/my_courses','',1405521244),('154e711257d4d32d865fb8f5fb70ad72',1,'Meine Dateien','Meine Dateien ist der pers?nliche Dateibereich. Hier k?nnen Dateien auf Stud.IP gespeichert werden, um sie von dort auf andere Rechner herunterladen zu k?nnen.\r\n\r\nAndere Studierende oder Lehrende erhalten keinen Zugriff auf Dateien, die in den pers?nlichen Dateibereich hochgeladen werden.\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','TL',0,'','dispatch.php/document/files','',1405592884),('154e711257d4d32d865fb8f5fb70ad72',4,'Datei?bersicht','Alle Dateien und Verzeichnisse werden tabellarisch aufgelistet. Neben dem Namen werden noch weitere Informationen wie der Dateityp oder die Dateigr??e angezeigt.','TL',0,'#layout_content FORM TABLE THEAD TR TH :eq(3)','dispatch.php/document/files','',1405593089),('154e711257d4d32d865fb8f5fb70ad72',3,'Neue Dateien und Verzeichnisse','Hier k?nnen neue Dateien von dem Computer in den pers?nlichen Dateibereich hochgeladen und neue Verzeichnisse erstellt werden.','TL',0,'#layout-sidebar SECTION DIV DIV UL LI A :eq(0)','dispatch.php/document/files','',1405593409),('ef5092ba722c81c37a5a6bd703890bd9',4,'Personen erw?hnen','Andere k?nnen ?ber einen Beitrag informiert werden, indem sie per @benutzername oder @\"Vorname Nachname\" im Beitrag erw?hnt werden.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/global','',1405672301),('154e711257d4d32d865fb8f5fb70ad72',6,'Export','Hier besteht die M?glichkeit einzelne Ordner oder den vollst?ndigen Dateibereich als ZIP-Datei herunterzuladen. Darin sind alle Dateien und Verzeichnisse enthalten.','TL',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(1)','dispatch.php/document/files','',1405593708),('154e711257d4d32d865fb8f5fb70ad72',5,'Aktionen','Bereits hochgeladene Dateien und Ordner k?nnen hier bearbeitet, heruntergeladen, verschoben, kopiert und gel?scht werden.','TR',0,'#layout_content FORM TABLE THEAD TR TH :eq(7)','dispatch.php/document/files','',1405594079),('154e711257d4d32d865fb8f5fb70ad72',2,'Verf?gbarer Speicherplatz','Der Speicherplatz des pers?nlichen Dateibereichs ist begrenzt. Es wird angezeigt, wie viel Speicherplatz noch verf?gbar ist.','BR',0,'DIV.caption-actions','dispatch.php/document/files','',1405594184),('edfcf78c614869724f93488c4ed09582',5,'Rundmail an Nutzergruppe versenden','Weiterhin besteht die M?glichkeit eine Rundmail an einzelne Nutzergruppen zu versenden.','BR',0,'#layout_container #layout_content TABLE CAPTION SPAN A IMG :eq(0)','dispatch.php/course/members','',1406637123),('25e7421f286fc5bdf9e41beadb484ffa',4,'Voraussetzungen','Eine Bilddatei muss im **.jpg**, **.png** oder **.gif** Format vorliegen.\r\nDie Dateigr??e darf 700 KB nicht ?berschreiten.','L',0,'#layout_content #edit_avatar TBODY TR TD FORM B :eq(2)','dispatch.php/settings/avatar','',1406722657),('19ac063e8319310d059d28379139b1cf',1,'Studiengruppe anlegen','Studiengruppen erm?glichen die Zusammenarbeit mit KommilitonInnen oder KolegInnen. Diese Tour gibt Ihnen einen ?berblick dar?ber wie Sie Studiengruppen anlegen k?nnen.\r\n\r\nUm zum n?chsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".','R',0,'','dispatch.php/my_studygroups','',1405684423),('19ac063e8319310d059d28379139b1cf',2,'Studiengruppe anlegen','Mit Klick auf \"Neue Studiengruppe anlegen\" kann eine neue Studiengruppe angelegt werden.','R',0,'A#nav_browse_new','dispatch.php/my_studygroups','',1406017730),('19ac063e8319310d059d28379139b1cf',3,'Studiengruppe benennen','Der Name einer Studiengruppe sollte aussagekr?ftig sein und einmalig im gesamten Stud.IP.','R',0,'INPUT#groupname','dispatch.php/course/studygroup/new','',1405684720),('19ac063e8319310d059d28379139b1cf',4,'Beschreibung hinzuf?gen','Die Beschreibung erm?glicht es weitere Informationen anzuzeigen und somit das Auffinden der Gruppe zu erleichtern.','R',0,'TEXTAREA#groupdescription','dispatch.php/course/studygroup/new','',1405684806),('19ac063e8319310d059d28379139b1cf',8,'Studiengruppe speichern','Nach dem Speichern einer Studiengruppe erscheint diese unter \"Veranstaltungen > Meine Studiengruppen\".','L',0,'#layout_content FORM TABLE TBODY TR TD :eq(14)','dispatch.php/course/studygroup/new','',1405686068),('19ac063e8319310d059d28379139b1cf',5,'Inhaltselemente zuordnen','Hier k?nnen Inhaltselemente aktiviert werden, welche  innerhalb der Studiengruppe zur Verf?gung stehen sollen. Das Fragezeichen gibt n?here Informationen zur Bedeutung der einzelnen Inhaltselemente.','L',0,'#layout_content FORM TABLE TBODY TR TD :eq(5)','dispatch.php/course/studygroup/new','',1405685093),('19ac063e8319310d059d28379139b1cf',6,'Zugang festlegen','Mit diesem Drop-down-Men? kann der Zugang zur Studiengruppe eingeschr?nkt werden.\r\n\r\nBeim Zugang \"offen f?r alle\" k?nnen sich alle Studierenden frei eintragen und an der Gruppe beteiligen.\r\n\r\nBeim Zugang \"Auf Anfrage\" m?ssen Teilnehmer durch den Gruppengr?nder hinzugef?gt werden.','R',0,'SELECT#groupaccess','dispatch.php/course/studygroup/new','',1405685334),('19ac063e8319310d059d28379139b1cf',7,'Nutzungsbedingungen akzeptieren','Bei der Erstellung einer Studiengruppe m?ssen die Nutzungsbedingungen akzeptiert werden.','R',0,'P LABEL','dispatch.php/course/studygroup/new','',1405685652),('edfcf78c614869724f93488c4ed09582',6,'Gruppen erstellen','Hier k?nnen die TeilnehmerInnen der Veranstaltung in Gruppen eingeteilt werden.','R',0,'A#nav_course_edit_groups','dispatch.php/course/members','',1405689311),('b74f8459dce2437463096d56db7c73b9',3,'Veranstaltungsdetails','Mit Klick auf das \"i\" erscheint ein Fenster mit den wichtigsten Eckdaten der Veranstaltung.','T',0,'#my_seminars TABLE THEAD TR TH :eq(3)','dispatch.php/my_courses','',1405931069),('edfcf78c614869724f93488c4ed09582',7,'Gruppe benennen','Sie k?nnen in den Vorlagen nach einem passenden Gruppennamen suchen und ihn mit dem gelben Doppelpfeil ausw?hlen. Alternativ haben Sie auch die M?glichkeit, einen neuen Gruppennamen zu bestimmen, indem Sie im rechten Feld den Namen direkt eintragen.','B',0,'SELECT','admin_statusgruppe.php','',1405689541),('edfcf78c614869724f93488c4ed09582',1,'Teilnehmerverwaltung','Diese Tour gibt einen ?berblick ?ber die Teilnehmerverwaltung einer Veranstaltung.\r\n\r\nUm zum n?chsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".','B',0,'','dispatch.php/course/members','',1405688399),('edfcf78c614869724f93488c4ed09582',2,'Personen eintragen','Mit diesen Funktionen k?nnen entweder einzelne Personen in Stud.IP gesucht und direkt als Lehrende, Tutor/-in oder Autor/-in eintragen werden. Es ist auch möglich eine Teilnehmendenliste einzugeben, um viele Personen auf einmal als Tutor/-in der Veranstaltung zuzuordnen.','R',0,'#layout-sidebar SECTION DIV.sidebar-widget :eq(1)','dispatch.php/course/members','',1405688707),('edfcf78c614869724f93488c4ed09582',4,'Rundmail verschicken','Hier kann eine Rundmail an alle Teilnehmende der Veranstaltung verschickt werden.','R',0,'#layout-sidebar SECTION DIV DIV UL LI A :eq(3)','dispatch.php/course/members','',1406636964),('edfcf78c614869724f93488c4ed09582',8,'Gruppengr??e','Mit dem Feld \"Gruppengr??e\" k?nnen Sie die maximale Anzahl der Teilnehmer einer Gruppe festlegen. Wenn Sie dies nicht ben?tigen, lassen Sie das Feld einfach leer.','B',0,'INPUT#role_size','admin_statusgruppe.php','',1405689763),('edfcf78c614869724f93488c4ed09582',9,'Selbsteintrag','Wenn Sie die Funktion \"Selbsteintrag\" aktivieren, k?nnen sich die Teilnehmenden der Veranstaltung selbst in die Gruppen eintragen.','B',0,'INPUT#self_assign','admin_statusgruppe.php','',1405689852),('edfcf78c614869724f93488c4ed09582',10,'Dateiordner','Wenn Sie die Funktion \"Dateiordner\" aktivieren, wird zus?tzlich ein Dateiordner pro Gruppe neu angelegt. In diesen Ordner k?nnen gruppenspezifische Dateien hochgeladen werden.','B',0,'INPUT#group_folder','admin_statusgruppe.php','',1405689936),('edfcf78c614869724f93488c4ed09582',3,'Hochstufen / Herabstufen','Um eine bereits eingetragene Person zum/zur Tutor/-in hochzustufen oder zum/zur Leser/-in herabzustufen, w?hlen Sie diese Person in der Liste aus und f?hren Sie mit Hilfe des Dropdown-Men? die gew?nschte Aktion aus.','T',0,'#autor CAPTION','dispatch.php/course/members','',1405690324),('3a717a468afb0822cb1455e0ae6b6fce',7,'Schlagwortwolke','Durch Anklicken eines Schlagwortes werden alle Beitr?ge aufgelistet, die dieses Schlagwort enthalten.','RT',0,'DIV.sidebar-widget-header','plugins.php/blubber/streams/profile','',1405508505),('b74f8459dce2437463096d56db7c73b9',4,'Veranstaltungsinhalte','Hier werden alle Inhalte (wie z.B. ein Forum) durch entsprechende Symbole angezeigt.\r\nFalls es seit dem letzten Login Neuigkeiten gab, erscheinen diese in rot.','LT',0,'#my_seminars TABLE THEAD TR TH :eq(4)','dispatch.php/my_courses','',1405931225),('b74f8459dce2437463096d56db7c73b9',5,'Verlassen der Veranstaltung','Ein Klick auf das T?r-Icon erm?glicht eine direkte Austragung aus der Veranstaltung.','TR',0,'#my_seminars TABLE THEAD TR TH :eq(5)','dispatch.php/my_courses','',1405931272),('b74f8459dce2437463096d56db7c73b9',6,'Zugriff auf archivierte Veranstaltungen','Falls Veranstaltungen archiviert sind, kann hier auf diese zugegriffen werden.','RT',0,'A#nav_browse_archive','dispatch.php/my_courses','',1405931431),('3a717a468afb0822cb1455e0ae6b6fce',2,'Beitrag erstellen','Hier kann eine Diskussion durch Schreiben von Text begonnen werden. Abs?tze lassen sich durch Dr?cken von Umschalt+Eingabe erzeugen. Der Text wird durch Dr?cken von Eingabe abgeschickt.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/profile','',1405507478),('b74f8459dce2437463096d56db7c73b9',7,'Anpassung der Veranstaltungsansicht','Zur Anpassung der Veranstaltungs?bersicht, kann man die Veranstaltungen nach bestimmten Kriterien (wie z.B. Studienbereiche, Dozenten oder Farben) gliedern.','R',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(2)','dispatch.php/my_courses','',1405932131),('b74f8459dce2437463096d56db7c73b9',8,'Zugriff auf Veranstaltung vergangener und zuk?nftiger Semester','Durch Klick auf das Drop-Down Men? k?nnen beispielsweise Veranstaltung aus vergangenen Semestern angezeigt werden.','R',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(3)','dispatch.php/my_courses','',1405932230),('b74f8459dce2437463096d56db7c73b9',9,'Weitere m?gliche Aktionen','Hier k?nnen Sie alle Neuigkeiten als gelesen markieren, Farbgruppierungen nach Belieben ?ndern oder\r\nauch die Benachrichtigungen ?ber Aktivit?ten in den einzelnen Veranstaltungen anpassen.','R',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(1)','dispatch.php/my_courses','',1405932320),('977f41c5c5239c4e86f04c3df27fae38',1,'Willkommen im neuen Stud.IP 3.1','Dies ist eine Hilfe-Tour, die die wichtigsten Neuerungen in Stud.IP vorstellt. \r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\". Eine Hilfe-Tour kann jederzeit durch einen Klick auf \"Beenden\" beendet werden.','TL',0,'','dispatch.php/start','',1405932373),('977f41c5c5239c4e86f04c3df27fae38',2,'Hilfelasche','Auf jeder Seite findet sich die Hilfe von nun an in der sogenannten Hilfelasche. Diese ?ffnet sich mit einem Klick auf das Fragezeichen. \r\n\r\nHier findet sich kurz und knapp zu jeder Seite die wichtigsten Informationen, Links zur ausf?hrlichen Hilfeseite mit Anleitungsvideos und gegebenenfalls Hilfe-Touren.','BR',0,'DIV.helpbar-container .helpbar-title','dispatch.php/start','',1405932475),('977f41c5c5239c4e86f04c3df27fae38',3,'Gliederung der Startseite','Die Startseite im neuen Stud.IP ist standardm??ig so gegliedert wie die alte Version. Neu ist, dass jedes Element (\"Widget\") individuell entfernt und verschoben werden kann. Dar?ber hinaus k?nnen noch weitere Widgets hinzugef?gt werden. Hierzu gibt es eine separate Hilfe-Tour und die Hinweise in der Hilfe.','TL',0,'','dispatch.php/start','',1405932516),('b74f8459dce2437463096d56db7c73b9',10,'Studiengruppen und Einrichtungen','Es besteht zudem die M?glichkeit auf pers?nliche Studiengruppen oder Einrichtungen zuzugreifen.','R',0,'#nav_browse_my_institutes A','dispatch.php/my_courses','',1405932519),('977f41c5c5239c4e86f04c3df27fae38',4,'Sidebar','Auf allen Seiten ist nun links die Sidebar positioniert. Sie enth?lt Funktionen f?r die aktuelle Seite.\r\nIm alten Stud.IP gab es mit der Infobox auf der rechten Seite etwas ?hnliches.','R',0,'SECTION.sidebar','dispatch.php/start','',1405932633),('977f41c5c5239c4e86f04c3df27fae38',5,'?nderung der Navigation','H?ufig ist die Reiternavigation vom alten Stud.IP nun in der Sidebar zu finden. Auf dieser Seite sind das zum Beispiel die Punkte \"Neue Nachricht schreiben\" und der Gesendet-Ordner.','R',0,'SECTION.sidebar ','dispatch.php/messages/overview','',1405932671),('977f41c5c5239c4e86f04c3df27fae38',6,'Studiengruppen und Einrichtungen','Die Einrichtungen, die fr?her unter den Veranstaltungen standen und die Studiengruppen, die fr?her zwischen den Veranstaltungen standen, finden sich nun in eigenen Reitern wieder.','R',0,'#nav_browse_my_institutes A SPAN','dispatch.php/my_courses','',1405932830),('977f41c5c5239c4e86f04c3df27fae38',7,'Hinweise zu den Anmeldeverfahren','Im neuen Stud.IP haben sich die Zugangsberechtigungen und Anmeldeverfahren ge?ndert. Das betrifft sowohl Studierende als auch Lehrende. Hierzu gibt es separate Feature-Touren und die Hinweise in der Hilfe.','TL',0,'','dispatch.php/my_courses','',1405932890),('3a717a468afb0822cb1455e0ae6b6fce',1,'Was ist Blubbern?','Diese Tour gibt Ihnen einen ?berblick ?ber die wichtigsten Funktionen von \"Blubber\".\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','TL',0,'','plugins.php/blubber/streams/profile','',1405507364),('3a717a468afb0822cb1455e0ae6b6fce',6,'Schlagworte','Beitr?ge k?nnen mit Schlagworten (engl. \"Hashtags\") versehen werden, indem einem beliebigen Wort des Beitrags ein # vorangestellt wird.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/profile','',1405508442),('49604a77654617a745e29ad6b253e491',1,'Funktionen und Gestaltungs-m?glichkeiten der Startseite','\r\nDiese Tour gibt Ihnen einen ?berblick ?ber die wichtigsten Funktionen der \"Startseite\".\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','TL',0,'','dispatch.php/start','',1405934926),('49604a77654617a745e29ad6b253e491',2,'Individuelle Gestaltung der Startseite','Die Startseite ist standardm??ig so konfiguriert, dass die Elemente \"Schnellzugriff\", \"Ank?ndigungen\", \"Meine aktuellen Termine\" und  \"Umfragen\" angezeigt werden. Die Elemente werden Widgets genannt und  k?nnen entfernt, hinzugef?gt und verschoben werde.n Jedes Widget kann individuell hinzugef?gt, entfernt und verschoben werden.','TL',0,'','dispatch.php/start','',1405934970),('49604a77654617a745e29ad6b253e491',3,'Widget hinzuf?gen','Hier k?nnen Widgets hinzugef?gt werden. Zus?tzlich zu den Standard-Widgets kann beispielsweise der pers?nliche Stundenplan auf der Startseite anzeigt werden. Neu hinzugef?gte Widgets erscheinen ganz unten auf der Startseite. Dar?ber hinaus kann in der Sidebar direkt zu jedem Widget gesprungen werden.','R',0,'#layout-sidebar SECTION DIV DIV UL LI :eq(4)','dispatch.php/start','',1405935192),('49604a77654617a745e29ad6b253e491',5,'Widget positionieren','Ein Widget kann per Drag&Drop an die gew?nschte Position verschoben werden: Dazu wird in die Titelzeile eines Widgets geklickt, die Maustaste gedr?ckt gehalten und das Widget an die gew?nschte Position gezogen.','B',0,'.widget-header','dispatch.php/start','',1405935687),('49604a77654617a745e29ad6b253e491',7,'Widget entfernen','Jedes Widget kann durch Klicken auf das X in der rechten oberen Ecke entfernt werden. Bei Bedarf kann es jederzeit wieder hinzugef?gt werden.','R',0,'.widget-header','dispatch.php/start','',1405935376),('49604a77654617a745e29ad6b253e491',6,'Widget bearbeiten','Bei einigen Widgets wird neben dem X zum Schlie?en noch ein weiteres Symbol angezeigt. Der Schnellzugriff bspw. kann durch Klick auf diesen Button individuell angepasst, die Ank?ndigungen k?nnen abonniert und bei den aktuellen Terminen bzw. Stundenplan k?nnen Termine hinzugef?gt werden.','L',0,'#layout_content DIV UL DIV SPAN A IMG :eq(0)','dispatch.php/start','',1405935792),('3629493a16bf2680de64361f07cab096',1,'Was ist Blubbern?','Diese Tour gibt Ihnen einen ?berblick ?ber die wichtigsten Funktionen von \"Blubber\".\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','TL',0,'','plugins.php/blubber/streams/forum','',1405507364),('3a717a468afb0822cb1455e0ae6b6fce',4,'Personen erw?hnen','Andere k?nnen ?ber einen Beitrag informiert werden, indem sie per @benutzername oder @\"Vorname Nachname\" im Beitrag erw?hnt werden.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/profile','',1405672301),('3629493a16bf2680de64361f07cab096',2,'Beitrag erstellen','Hier kann eine Diskussion durch Schreiben von Text begonnen werden. Abs?tze lassen sich durch Dr?cken von Umschalt+Eingabe erzeugen. Der Text wird durch Dr?cken von Eingabe abgeschickt.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/forum','',1405507478),('7cccbe3b22dfa745c17cb776fb04537c',1,'Hilfe-Tour \"Meine Veranstaltung\"','Diese Tour gibt einen ?berblick ?ber die wichtigsten Funktionen der Seite \"Meine Veranstaltungen\".\r\n\r\nUm auf den n?chsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".','TL',0,'','dispatch.php/my_courses','',1406125847),('7cccbe3b22dfa745c17cb776fb04537c',2,'Veranstaltungs?berblick','Hier werden die  Veranstaltungen des aktuellen und vergangenen Semesters angezeigt. Neue Veranstaltungen erscheinen zun?chst in rot.','TL',0,'#my_seminars TABLE THEAD TR TH :eq(2)','dispatch.php/my_courses','',1406125908),('7cccbe3b22dfa745c17cb776fb04537c',3,'Veranstaltungsdetails','Mit Klick auf das \"i\" erscheint ein Fenster mit den wichtigsten Eckdaten der Veranstaltung.','T',0,'#my_seminars TABLE THEAD TR TH :eq(3)','dispatch.php/my_courses','',1406125992),('7cccbe3b22dfa745c17cb776fb04537c',4,'Veranstaltungsinhalte','Hier werden alle Inhalte (wie z.B. ein Forum) durch entsprechende Symbole angezeigt.\r\nFalls es seit dem letzten Login Neuigkeiten gab, erscheinen diese in rot.','LT',0,'#my_seminars TABLE THEAD TR TH :eq(4)','dispatch.php/my_courses','',1406126049),('7cccbe3b22dfa745c17cb776fb04537c',5,'Bearbeitung oder L?schung einer Veranstaltung','Der Klick auf das Zahnrad erm?glicht die Bearbeitung einer Veranstaltung.\r\nFalls bei einer Veranstaltung Teilnehmerstatus besteht, kann hier eine Austragung, durch Klick auf dasT?r-Icon, vorgenommen werden.','TR',0,'#my_seminars TABLE THEAD TR TH :eq(5)','dispatch.php/my_courses','',1406126134),('3a717a468afb0822cb1455e0ae6b6fce',8,'Beitrag ?ndern','Wird der Mauszeiger auf einem beliebigen Beitrag positioniert, erscheint dessen Datum. Bei eigenen Beitr?gen erscheint au?erdem rechts neben dem Datum ein Icon, mit dem der Beitrag nachtr?glich ge?ndert werden kann.','BR',0,'DIV DIV A SPAN.time','plugins.php/blubber/streams/profile','',1405507901),('7cccbe3b22dfa745c17cb776fb04537c',6,'Anpassung der Veranstaltungsansicht','Zur Anpassung der Veranstaltungs?bersicht, kann man die Veranstaltungen nach bestimmten Kriterien (wie z.B. Studienbereiche, Dozenten oder Farben) gliedern.','R',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(2)','dispatch.php/my_courses','',1406126281),('7cccbe3b22dfa745c17cb776fb04537c',7,'Zugriff auf Veranstaltung vergangener und zuk?nftiger Semester','Durch Klick auf das Drop-Down Men? k?nnen beispielsweise Veranstaltung aus vergangenen Semestern angezeigt werden.','R',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(3)','dispatch.php/my_courses','',1406126316),('7cccbe3b22dfa745c17cb776fb04537c',8,'Weitere m?gliche Aktionen','Hier k?nnen Sie alle Neuigkeiten als gelesen markieren, Farbgruppierungen nach Belieben ?ndern oder\r\nauch die Benachrichtigungen ?ber Aktivit?ten in den einzelnen Veranstaltungen anpassen.','R',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(1)','dispatch.php/my_courses','',1406126374),('7cccbe3b22dfa745c17cb776fb04537c',9,'Studiengruppen und Einrichtungen','Es besteht zudem die M?glichkeit auf pers?nliche Studiengruppen oder Einrichtungen zuzugreifen.','R',0,'#nav_browse_my_institutes A','dispatch.php/my_courses','',1406126415),('3a717a468afb0822cb1455e0ae6b6fce',5,'Datei hinzuf?gen','Dateien k?nnen in einen Beitrag eingef?gt werden, indem sie per Drag&Drop in ein Eingabefeld gezogen werden.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/profile','',1405508401),('977f41c5c5239c4e86f04c3df27fae38',8,'Ende der Feature-Tour','Durch Klick auf \"Beenden\" in der Box rechts unten wird diese Tour beendet. ?ber die Hilfelasche l?sst sich diese Tour jederzeit wieder starten.','TL',0,'','dispatch.php/start','',1406539532),('49604a77654617a745e29ad6b253e491',4,'Sprungmarken','Dar?ber hinaus kann mit Sprungmarken direkt zu jedem Widget gesprungen werden.','R',0,'#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(0)','dispatch.php/start','',1406623464),('3629493a16bf2680de64361f07cab096',5,'Datei hinzuf?gen','Dateien k?nnen in einen Beitrag eingef?gt werden, indem sie per Drag&Drop in ein Eingabefeld gezogen werden.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/forum','',1405508401),('3629493a16bf2680de64361f07cab096',6,'Schlagworte','Beitr?ge k?nnen mit Schlagworten (engl. \"Hashtags\") versehen werden, indem einem beliebigen Wort des Beitrags ein # vorangestellt wird.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/forum','',1405508442),('3629493a16bf2680de64361f07cab096',7,'Schlagwortwolke','Durch Anklicken eines Schlagwortes werden alle Beitr?ge aufgelistet, die dieses Schlagwort enthalten.','RT',0,'DIV.sidebar-widget-header','plugins.php/blubber/streams/forum','',1405508505),('3629493a16bf2680de64361f07cab096',4,'Personen erw?hnen','Andere k?nnen ?ber einen Beitrag informiert werden, indem sie per @benutzername oder @\"Vorname Nachname\" im Beitrag erw?hnt werden.','BL',0,'TEXTAREA#new_posting.autoresize','plugins.php/blubber/streams/forum','',1405672301);
/*!40000 ALTER TABLE `help_tour_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_tour_user`
--

DROP TABLE IF EXISTS `help_tour_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_tour_user` (
  `tour_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `step_nr` int(11) NOT NULL,
  `completed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tour_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_tour_user`
--

LOCK TABLES `help_tour_user` WRITE;
/*!40000 ALTER TABLE `help_tour_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `help_tour_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help_tours`
--

DROP TABLE IF EXISTS `help_tours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_tours` (
  `tour_id` char(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` enum('tour','wizard') NOT NULL,
  `roles` varchar(255) NOT NULL,
  `version` int(11) unsigned NOT NULL DEFAULT '1',
  `language` char(2) NOT NULL DEFAULT 'de',
  `studip_version` varchar(32) NOT NULL DEFAULT '',
  `installation_id` varchar(255) NOT NULL DEFAULT 'demo-installation',
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tour_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help_tours`
--

LOCK TABLES `help_tours` WRITE;
/*!40000 ALTER TABLE `help_tours` DISABLE KEYS */;
INSERT INTO `help_tours` VALUES ('96ea422f286fb5bbf9e41beadb484a9a','Profilseite','In dieser Tour werden die Grundfunktionen und Bereiche der Profilseite vorgestellt.','tour','autor,dozent,root',1,'de','3.1','',1406722657),('25e7421f286fc5bdf9e41beadb484ffa','Eigenes Bild hochladen','In der Tour wird erkl?rt, wie Nutzende ein eigenes Profilbild hochladen k?nnen.','tour','autor,tutor,dozent,admin,root',1,'de','3.1','',1406722657),('3629493a16bf2680de64361f07cab096','Blubber','In der Tour wird die Nutzung von Blubber erkl?rt.','tour','autor,tutor,dozent,admin,root',1,'de','3.1','',1406709759),('21f487fa74e3bfc7789886f40fe4131a','Forum nutzen','Die Inhalte dieser Tour stammen aus der alten Tour des Forums (Sidebar > Aktionen > Tour starten).','tour','autor,tutor,dozent,admin,root',1,'de','3.1','',1405415746),('44f859c50648d3410c39207048ddd833','Forum verwalten','Die Inhalte dieser Tour stammen aus der alten Tour des Forums (Sidebar > Aktionen > Tour starten).','tour','tutor,dozent,admin,root',1,'de','3.1','',1405417901),('3a717a468afb0822cb1455e0ae6b6fce','Blubber','In der Tour wird die Nutzung von Blubber erkl?rt.','tour','autor,tutor,dozent,admin,root',1,'de','3.1','',1406709041),('ef5092ba722c81c37a5a6bd703890bd9','Blubber','In der Tour wird die Nutzung von Blubber erkl?rt.','tour','autor,tutor,dozent,admin,root',1,'de','3.1','',1405507317),('6849293baa05be5bef8ff438dc7c438b','Suche','In dieser Feature-Tour werden die wichtigsten Funktionen der Suche vorgestellt.','tour','autor,tutor,dozent,admin,root',1,'de','3.1','',1405519609),('b74f8459dce2437463096d56db7c73b9','Meine Veranstaltungen (Studierende)','In dieser Tour werden die wichtigsten Funktionen der Seite \"Meine Veranstaltungen\" vorgestellt.','tour','autor,admin,root',1,'de','3.1','',1405521073),('154e711257d4d32d865fb8f5fb70ad72','Meine Dateien','In dieser Tour wird der pers?nliche Dateibereich vorgestellt.','tour','autor,tutor,dozent,admin,root',1,'de','3.1','',1405592618),('19ac063e8319310d059d28379139b1cf','Studiengruppe anlegen','In dieser Tour wird das Anlegen von Studiengruppen erkl?rt.','tour','autor,tutor,dozent,admin,root',1,'de','3.1','',1405684299),('edfcf78c614869724f93488c4ed09582','Teilnehmerverwaltung','In dieser Tour werden die Verwaltungsoptionen der Teilnehmerverwaltung erkl?rt.','tour','tutor,dozent,admin,root',1,'de','3.1','',1405688156),('977f41c5c5239c4e86f04c3df27fae38','Was ist neu in Stud.IP 3.1?','In dieser Tour werden die Neuerungen in Stud.IP 3.1 ?berblicksartig vorgestellt.','tour','autor,tutor,dozent,admin',1,'de','3.1','',1405932260),('49604a77654617a745e29ad6b253e491','Gestaltung der Startseite','In dieser Tour werden die Funktionen und Gestaltungsm?glichkeiten der Startseite vorgestellt.','tour','autor,tutor,dozent,admin,root',1,'de','3.1','',1405934780),('7cccbe3b22dfa745c17cb776fb04537c','Meine Veranstaltungen (Lehrende)','In dieser Tour werden die wichtigsten Funktionen der Seite \"Meine Veranstaltungen\" vorgestellt.','tour','tutor,dozent,admin,root',1,'de','3.1','',1406125685);
/*!40000 ALTER TABLE `help_tours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategorien`
--

DROP TABLE IF EXISTS `kategorien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategorien` (
  `kategorie_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`kategorie_id`),
  KEY `priority` (`priority`),
  KEY `range_id` (`range_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategorien`
--

LOCK TABLES `kategorien` WRITE;
/*!40000 ALTER TABLE `kategorien` DISABLE KEYS */;
/*!40000 ALTER TABLE `kategorien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `limitedadmissions`
--

DROP TABLE IF EXISTS `limitedadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `limitedadmissions` (
  `rule_id` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `maxnumber` int(11) NOT NULL DEFAULT '0',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `limitedadmissions`
--

LOCK TABLES `limitedadmissions` WRITE;
/*!40000 ALTER TABLE `limitedadmissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `limitedadmissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lit_catalog`
--

DROP TABLE IF EXISTS `lit_catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lit_catalog` (
  `catalog_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  `lit_plugin` varchar(100) NOT NULL DEFAULT 'Studip',
  `accession_number` varchar(100) DEFAULT NULL,
  `dc_title` varchar(255) NOT NULL DEFAULT '',
  `dc_creator` varchar(255) NOT NULL DEFAULT '',
  `dc_subject` varchar(255) DEFAULT NULL,
  `dc_description` text,
  `dc_publisher` varchar(255) DEFAULT NULL,
  `dc_contributor` varchar(255) DEFAULT NULL,
  `dc_date` date DEFAULT NULL,
  `dc_type` varchar(100) DEFAULT NULL,
  `dc_format` varchar(100) DEFAULT NULL,
  `dc_identifier` varchar(255) DEFAULT NULL,
  `dc_source` varchar(255) DEFAULT NULL,
  `dc_language` varchar(10) DEFAULT NULL,
  `dc_relation` varchar(255) DEFAULT NULL,
  `dc_coverage` varchar(255) DEFAULT NULL,
  `dc_rights` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`catalog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lit_catalog`
--

LOCK TABLES `lit_catalog` WRITE;
/*!40000 ALTER TABLE `lit_catalog` DISABLE KEYS */;
INSERT INTO `lit_catalog` VALUES ('54181f281faa777941acc252aebaf26d','studip',1156516698,1156516698,'Gvk','387042768','Quickguide Strahlenschutz : [Aufgaben, Organisation, Schutzma?nahmen].','Wolf, Heike','','','Kissing : WEKA Media','','2004-01-01','','74 S : Ill.','','','ger','[Der Strahlenschutzbeauftragte in Medizin und Technik / Heike Wolf] Praxisl?sungen','',''),('d6623a3c2b8285fb472aa759150148ad','studip',1156516698,1156516698,'Gvk','387042253','R?ntgenverordnung : (R?V) ; Verordnung ?ber den Schutz vor Sch?den durch R?ntgenstrahlen.','Wolf, Heike','','','Kissing : WEKA Media','','2004-01-01','','50 S.','','','ger','[Der Strahlenschutzbeauftragte in Medizin und Technik / Heike Wolf] Praxisl?sungen','',''),('15074ad4f2bd2c57cbc9dfb343c1355b','studip',1156516698,1156516698,'Gvk','384065813','Der Kater mit Hut','Geisel, Theodor Seuss','','','M?nchen [u.a.] : Piper','','2004-01-01','','75 S : zahlr. Ill ; 19 cm.','ISBN: 349224078X (kart.)','','ger','Serie Piper ;, 4078','',''),('ce704bbc9453994daa05d76d2d04aba0','studip',1156516698,1156516698,'Gvk','379252104','Die volkswirtschaftliche Perspektive','Heise, Michael','','','In: Zeitschrift f?r das gesamte Kreditwesen, Vol. 57, No. 4 (2004), p. 211-217, Frankfurt, M. : Knapp','Kater, Ulrich;','2004-01-01','','graph. Darst.','','','ger','','',''),('b5d115a7f7cad02b4535fb3090bf18da','studip',1156516698,1156516698,'Gvk','386883831','E-Learning: Qualit?t und Nutzerakzeptanz sichern : Beitr?ge zur Planung, Umsetzung und Evaluation multimedialer und netzgest?tzter Anwendungen','Zinke, Gert','','','Bielefeld : Bertelsmann','H?rtel, Michael; Bundesinstitut f?r Berufsbildung, ;','2004-01-01','','159 S : graph. Darst ; 225 mm x 155 mm.','ISBN: 3763910204','','ger','Berichte zur beruflichen Bildung ;, 265','','');
/*!40000 ALTER TABLE `lit_catalog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lit_list`
--

DROP TABLE IF EXISTS `lit_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lit_list` (
  `list_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `format` varchar(255) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  `priority` smallint(6) NOT NULL DEFAULT '0',
  `visibility` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`list_id`),
  KEY `range_id` (`range_id`),
  KEY `priority` (`priority`),
  KEY `visibility` (`visibility`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lit_list`
--

LOCK TABLES `lit_list` WRITE;
/*!40000 ALTER TABLE `lit_list` DISABLE KEYS */;
INSERT INTO `lit_list` VALUES ('0b4d8c94244a1a571e3cc2afeeb15c5f','a07535cf2f8a72df33c12ddfa4b53dde','Basisliteratur der Veranstaltung','**{dc_creator}** |({dc_contributor})||\r\n{dc_title}||\r\n{dc_identifier}||\r\n%%{published}%%||\r\n{note}||\r\n[{lit_plugin_display_name}]{external_link}|','76ed43ef286fb55cf9e41beadb484a9f',1343924971,1343925058,1,1);
/*!40000 ALTER TABLE `lit_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lit_list_content`
--

DROP TABLE IF EXISTS `lit_list_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lit_list_content` (
  `list_element_id` varchar(32) NOT NULL DEFAULT '',
  `list_id` varchar(32) NOT NULL DEFAULT '',
  `catalog_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  `note` text,
  `priority` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`list_element_id`),
  KEY `list_id` (`list_id`),
  KEY `catalog_id` (`catalog_id`),
  KEY `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lit_list_content`
--

LOCK TABLES `lit_list_content` WRITE;
/*!40000 ALTER TABLE `lit_list_content` DISABLE KEYS */;
INSERT INTO `lit_list_content` VALUES ('48acf3d39374f46876d46df0f56203cd','0b4d8c94244a1a571e3cc2afeeb15c5f','b5d115a7f7cad02b4535fb3090bf18da','76ed43ef286fb55cf9e41beadb484a9f',1343925058,1343925058,'',5),('0cf7e4622ddbcc145b5792519979116f','0b4d8c94244a1a571e3cc2afeeb15c5f','d6623a3c2b8285fb472aa759150148ad','76ed43ef286fb55cf9e41beadb484a9f',1343925058,1343925058,'',4),('28de3cab6e36758b96ba757b65512cd2','0b4d8c94244a1a571e3cc2afeeb15c5f','54181f281faa777941acc252aebaf26d','76ed43ef286fb55cf9e41beadb484a9f',1343925058,1343925058,'',3),('03e0d3910e15fd7ae2826ed6baf2b59d','0b4d8c94244a1a571e3cc2afeeb15c5f','ce704bbc9453994daa05d76d2d04aba0','76ed43ef286fb55cf9e41beadb484a9f',1343925058,1343925058,'',2),('7e129b140176dfc1a4c53e065fa5e8b1','0b4d8c94244a1a571e3cc2afeeb15c5f','15074ad4f2bd2c57cbc9dfb343c1355b','76ed43ef286fb55cf9e41beadb484a9f',1343925058,1343925058,'',1);
/*!40000 ALTER TABLE `lit_list_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lock_rules`
--

DROP TABLE IF EXISTS `lock_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lock_rules` (
  `lock_id` varchar(32) NOT NULL DEFAULT '',
  `permission` enum('autor','tutor','dozent','admin','root') NOT NULL DEFAULT 'dozent',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `attributes` text NOT NULL,
  `object_type` enum('sem','inst','user') NOT NULL DEFAULT 'sem',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`lock_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lock_rules`
--

LOCK TABLES `lock_rules` WRITE;
/*!40000 ALTER TABLE `lock_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `lock_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lockedadmissions`
--

DROP TABLE IF EXISTS `lockedadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lockedadmissions` (
  `rule_id` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lockedadmissions`
--

LOCK TABLES `lockedadmissions` WRITE;
/*!40000 ALTER TABLE `lockedadmissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `lockedadmissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_actions`
--

DROP TABLE IF EXISTS `log_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_actions` (
  `action_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(128) NOT NULL DEFAULT '',
  `description` varchar(64) DEFAULT NULL,
  `info_template` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `expires` int(20) NOT NULL DEFAULT '0',
  `filename` varchar(255) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  `type` enum('core','plugin','file') DEFAULT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_actions`
--

LOCK TABLES `log_actions` WRITE;
/*!40000 ALTER TABLE `log_actions` DISABLE KEYS */;
INSERT INTO `log_actions` VALUES ('0ee290df95f0547caafa163c4d533991','SEM_VISIBLE','Veranstaltung sichtbar schalten','%user schaltet %sem(%affected) sichtbar.',1,0,NULL,NULL,NULL),('a94706b41493e32f8336194262418c01','SEM_INVISIBLE','Veranstaltung unsichtbar schalten','%user versteckt %sem(%affected).',1,0,NULL,NULL,NULL),('bd2103035a8021942390a78a431ba0c4','DUMMY','Dummy-Aktion','%user tut etwas.',1,0,NULL,NULL,NULL),('4490aa3d29644e716440fada68f54032','LOG_ERROR','Allgemeiner Log-Fehler','Allgemeiner Logging-Fehler, Details siehe Debug-Info.',1,0,NULL,NULL,NULL),('f858b05c11f5faa2198a109a783087a8','SEM_CREATE','Veranstaltung anlegen','%user legt %sem(%affected) an.',1,0,NULL,NULL,NULL),('5b96f2fe994637253ba0fe4a94ad1b98','SEM_ARCHIVE','Veranstaltung archivieren','%user archiviert %info (ID: %affected).',1,0,NULL,NULL,NULL),('bf192518a9c3587129ed2fdb9ea56f73','SEM_DELETE_FROM_ARCHIVE','Veranstaltung aus Archiv l?schen','%user l?scht %info aus dem Archiv (ID: %affected).',1,0,NULL,NULL,NULL),('4869cd69f20d4d7ed4207e027d763a73','INST_USER_STATUS','Einrichtungsnutzerstatus ?ndern','%user ?ndert Status f?r %user(%coaffected) in Einrichtung %inst(%affected): %info.',1,0,NULL,NULL,NULL),('6be59dcd70197c59d7bf3bcd3fec616f','INST_USER_DEL','Benutzer aus Einrichtung l?schen','%user l?scht %user(%coaffected) aus Einrichtung %inst(%affected).',1,0,NULL,NULL,NULL),('cf8986a67e67ca273e15fd9230f6e872','USER_CHANGE_TITLE','Akademische Titel ?ndern','%user ?ndert/setzt akademischen Titel f?r %user(%affected) - %info.',1,0,NULL,NULL,NULL),('ca216ccdf753f59ba7fd621f7b22f7bd','USER_CHANGE_NAME','Personennamen ?ndern','%user ?ndert/setzt Name f?r %user(%affected) - %info.',1,0,NULL,NULL,NULL),('8aad296e52423452fc75cabaf2bee384','USER_CHANGE_USERNAME','Benutzernamen ?ndern','%user ?ndert/setzt Benutzernamen f?r %user(%affected): %info.',1,0,NULL,NULL,NULL),('0d87c25b624b16fb9b8cdaf9f4e96e53','INST_CREATE','Einrichtung anlegen','%user legt Einrichtung %inst(%affected) an.',1,0,NULL,NULL,NULL),('1a1e8c9c3125ea8d2c58c875a41226d6','INST_DEL','Einrichtung l?schen','%user l?scht Einrichtung %info (%affected).',1,0,NULL,NULL,NULL),('d18d750fb2c166e1c425976e8bca96e7','USER_CHANGE_EMAIL','E-Mail-Adresse ?ndern','%user ?ndert/setzt E-Mail-Adresse f?r %user(%affected): %info.',1,0,NULL,NULL,NULL),('a92afa63584cc2a62d2dd2996727b2c5','USER_CREATE','Nutzer anlegen','%user legt Nutzer %user(%affected) an.',1,0,NULL,NULL,NULL),('e406e407501c8418f752e977182cd782','USER_CHANGE_PERMS','Globalen Nutzerstatus ?ndern','%user ?ndert/setzt globalen Status von %user(%affected): %info',1,0,NULL,NULL,NULL),('63042706e5cd50924987b9515e1e6cae','INST_USER_ADD','Benutzer zu Einrichtung hinzuf?gen','%user f?gt %user(%coaffected) zu Einrichtung %inst(%affected) mit Status %info hinzu.',1,0,NULL,NULL,NULL),('4dd6b4101f7bf3bd7fe8374042da95e9','USER_NEWPWD','Neues Passwort','%user generiert neues Passwort f?r %user(%affected)',1,0,NULL,NULL,NULL),('e8646729e5e04970954c8b9679af389b','USER_DEL','Benutzer l?schen','%user l?scht %user(%affected) (%info)',1,0,NULL,NULL,NULL),('2e816bfd792e4a99913f11c04ad49198','SEM_UNDELETE_SINGLEDATE','Einzeltermin wiederherstellen','%user stellt Einzeltermin %singledate(%affected) in %sem(%coaffected) wieder her.',1,0,NULL,NULL,NULL),('997cf01328d4d9f36b9f50ac9b6ace47','SEM_DELETE_SINGLEDATE','Einzeltermin l?schen','%user l?scht Einzeltermin %singledate(%affected) in %sem(%coaffected).',1,0,NULL,NULL,NULL),('b205bde204b5607e036c10557a6ce149','SEM_SET_STARTSEMESTER','Startsemester ?ndern','%user hat in %sem(%affected) das Startsemester auf %semester(%coaffected) ge?ndert.',1,0,NULL,NULL,NULL),('9d13643a1833c061dc3d10b4fb227f12','SEM_SET_ENDSEMESTER','Semesterlaufzeit ?ndern','%user hat in %sem(%affected) die Laufzeit auf %semester(%coaffected) ge?ndert',1,0,NULL,NULL,NULL),('5f8fda12a4c0bd6eadbb94861de83696','SEM_ADD_CYCLE','Regelm??ige Zeit hinzugef?gt','%user hat in %sem(%affected) die regelm??ige Zeit %info hinzugef?gt.',1,0,NULL,NULL,NULL),('6f4bb66c1caf89879d89f3b1921a93dd','SEM_DELETE_CYCLE','Regelm??ige Zeit gel?scht','%user hat in %sem(%affected) die regelm??ige Zeit %info gel?scht.',1,0,NULL,NULL,NULL),('3f7dcf6cc85d6fba1281d18c4d9aba6f','SEM_ADD_SINGLEDATE','Einzeltermin hinzuf?gen','%user hat in %sem(%affected) den Einzeltermin <em>%coaffected</em> hinzugef?gt',1,0,NULL,NULL,NULL),('c36fa0f804cde78a6dcb1c30c2ee47ba','SEM_DELETE_REQUEST','Raumanfrage gel?scht','%user hat in %sem(%affected) die Raumanfrage f?r die gesamte Veranstaltung gel?scht.',1,0,NULL,NULL,NULL),('370db4eb0e38051dd3c5d7c52717215a','SEM_DELETE_SINGLEDATE_REQUEST','Einzeltermin, Raumanfrage gel?scht','%user hat in %sem(%affected) die Raumanfrage f?r den Termin <em>%coaffected</em> gel?scht.',1,0,NULL,NULL,NULL),('9d642dc93540580d42ba2ea502c3fbf6','SINGLEDATE_CHANGE_TIME','Einzeltermin bearbeiten','%user hat in %sem(%affected) den Einzeltermin %coaffected ge?ndert.',1,0,NULL,NULL,NULL),('10c31be1aec819c03b0dc299d0111576','CHANGE_BASIC_DATA','Basisdaten ge?ndert','%user hat in Veranstaltung %sem(%affected) die Daten %info ge?ndert.',0,0,NULL,NULL,NULL),('fd74339a9ea038d084569e33e2655b6a','CHANGE_INSTITUTE_DATA','Beteiligte Einrichtungen ge?ndert','%user hat in Veranstaltung %sem(%affected) die Daten ge?ndert. %info',0,0,NULL,NULL,NULL),('89114dcd6f02dd7f94488a616c21a7c3','PLUGIN_ENABLE','Plugin einschalten','%user hat in Veranstaltung %sem(%affected) das Plugin %plugin(%coaffected) aktiviert.',1,0,NULL,NULL,NULL),('a66c9e04e9c41bf5cc4d23fa509a8667','PLUGIN_DISABLE','Plugin ausschalten','%user hat in Veranstaltung %sem(%affected) das Plugin %plugin(%coaffected) deaktiviert.',1,0,NULL,NULL,NULL),('005df8d5eb23c66214b28b3c9792680b','SEM_CHANGED_ACCESS','Zugangsberechtigungen ge?ndert','%user ?ndert die Zugangsberechtigungen der Veranstaltung %sem(%affected).',0,0,NULL,NULL,NULL),('535010528d6c012ec0e3535e2d754f66','SEM_USER_ADD','In Veranstaltung eingetragen','%user hat %user(%coaffected) f?r %sem(%affected) mit dem status %info eingetragen. (%dbg_info)',0,0,NULL,NULL,NULL),('6e2b789a57b9125af59c0273f5b47cb1','SEM_USER_DEL','Aus Veranstaltung ausgetragen','%user hat %user(%coaffected) aus %sem(%affected) ausgetragen. (%info)',0,0,NULL,NULL,NULL),('d07c8b37c6d3e206cd012d07ba8028b1','SEM_CHANGED_RIGHTS','Veranstaltungsrechte ge?ndert','%user hat %user(%coaffected) in %sem(%affected) als %info eingetragen. (%dbg_info)',0,0,NULL,NULL,NULL),('2420da2946df66a5ad96c6d45e97d5b9','SEM_ADD_STUDYAREA','Studienbereich zu Veranst. hinzuf?gen','%user f?gt Studienbereich \"%studyarea(%coaffected)\" zu %sem(%affected) hinzu.',0,0,NULL,NULL,NULL),('754708c8c0c61a916855c5031014acbb','SEM_DELETE_STUDYAREA','Studienbereich aus Veranst. l?schen','%user entfernt Studienbereich \"%studyarea(%coaffected)\" aus %sem(%affected).',0,0,NULL,NULL,NULL),('30dfb509cb1a8e228af3bd17dd6c8d1d','RES_ASSIGN_SEM','Buchen einer Ressource (VA)','%user bucht %res(%affected) f?r %sem(%coaffected) (%info).',0,0,NULL,NULL,NULL),('e8b1105ca4f2305ef0db6c961d2fbe4c','RES_ASSIGN_SINGLE','Buchen einer Ressource (Einzel)','%user bucht %res(%affected) direkt (%info).',0,0,NULL,NULL,NULL),('7d26ffbf73103601966f7517e40d7e66','RES_REQUEST_NEW','Neue Raumanfrage','%user stellt neue Raumanfrage f?r %sem(%affected), gew?nschter Raum: %res(%coaffected), %info',0,0,NULL,NULL,NULL),('46bc7faabfc73864998b561b1011e3fe','RES_REQUEST_UPDATE','Ge?nderte Raumanfrage','%user ?ndert Raumanfrage f?r %sem(%affected), gew?nschter Raum: %res(%coaffected), %info',0,0,NULL,NULL,NULL),('a0928e74639fd2a55f5d4d2a3c5a8e71','RES_REQUEST_DEL','Raumanfrage l?schen','%user l?scht Raumanfrage f?r %sem(%affected).',0,0,NULL,NULL,NULL),('a3856b6531e2f79d158b5ebfb998e5db','RES_ASSIGN_DEL_SEM','VA-Buchung l?schen','%user l?scht Ressourcenbelegung f?r %res(%affected) in Veranstaltung %sem(%coaffected), %info.',0,0,NULL,NULL,NULL),('17f0a527e9db7dec09687a70681559cf','RES_ASSIGN_DEL_SINGLE','Direktbuchung l?schen','%user l?scht Direktbuchung f?r %res(%affected) (%info).',0,0,NULL,NULL,NULL),('9179d3cf4e0353f9874bcde072d12b30','RES_REQUEST_DENY','Abgelehnte Raumanfrage','%user lehnt Raumanfrage f?r %sem(%coaffected), Raum %sem(%affected) ab.',0,0,NULL,NULL,NULL),('ff806b4b26f8bc8c3e65e29d14176cd9','RES_REQUEST_RESOLVE','Aufgel?ste Raumanfrage','%user l?st Raumanfrage f?r %sem(%affected), Raum %res(%coaffected) auf.',0,0,NULL,NULL,NULL),('248f54105b7102e5cbcc36e9439504fb','STUDYAREA_ADD','Studienbereich hinzuf?gen','%user legt Studienbereich %studyarea(%affected) an.',0,0,NULL,NULL,NULL),('9123d360316ba28ddb32c0ed1a0320f2','STUDYAREA_DELETE','Studienbereich l?schen','%user entfernt Studienbereich %studyarea(%affected).',0,0,NULL,NULL,NULL),('897207a36c411d736947052219624b72','USER_CHANGE_PASSWORD','Nutzerpasswort ge?ndert','%user ?ndert/setzt das Passwort f?r %user(%affected)',0,0,NULL,NULL,NULL),('9ed46a3ca3d4f43e17f91e314224dcae','SEM_CHANGE_CYCLE','Regelm??ige Zeit ge?ndert','%user hat in %sem(%affected) die regelm??ige Zeit %info ge?ndert',1,0,NULL,NULL,NULL);
/*!40000 ALTER TABLE `log_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_events`
--

DROP TABLE IF EXISTS `log_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_events` (
  `event_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `action_id` varchar(32) NOT NULL DEFAULT '',
  `affected_range_id` varchar(32) DEFAULT NULL,
  `coaffected_range_id` varchar(32) DEFAULT NULL,
  `info` text,
  `dbg_info` text,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`),
  KEY `action_id` (`action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_events`
--

LOCK TABLES `log_events` WRITE;
/*!40000 ALTER TABLE `log_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_queue_entries`
--

DROP TABLE IF EXISTS `mail_queue_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_queue_entries` (
  `mail_queue_id` varchar(32) NOT NULL,
  `mail` text NOT NULL,
  `message_id` varchar(32) DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `tries` int(11) NOT NULL,
  `last_try` int(11) NOT NULL DEFAULT '0',
  `mkdate` bigint(20) NOT NULL,
  `chdate` bigint(20) NOT NULL,
  PRIMARY KEY (`mail_queue_id`),
  KEY `message_id` (`message_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_queue_entries`
--

LOCK TABLES `mail_queue_entries` WRITE;
/*!40000 ALTER TABLE `mail_queue_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `mail_queue_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media_cache`
--

DROP TABLE IF EXISTS `media_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_cache` (
  `id` varchar(32) NOT NULL,
  `type` varchar(64) NOT NULL,
  `chdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media_cache`
--

LOCK TABLES `media_cache` WRITE;
/*!40000 ALTER TABLE `media_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `media_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `message_id` varchar(32) NOT NULL DEFAULT '',
  `autor_id` varchar(32) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `priority` enum('normal','high') NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`message_id`),
  KEY `autor_id` (`autor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_tags`
--

DROP TABLE IF EXISTS `message_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_tags` (
  `message_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `tag` varchar(64) NOT NULL,
  `chdate` bigint(20) NOT NULL,
  `mkdate` bigint(20) NOT NULL,
  PRIMARY KEY (`message_id`,`user_id`,`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_tags`
--

LOCK TABLES `message_tags` WRITE;
/*!40000 ALTER TABLE `message_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_user`
--

DROP TABLE IF EXISTS `message_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_user` (
  `user_id` char(32) NOT NULL DEFAULT '',
  `message_id` char(32) NOT NULL DEFAULT '',
  `readed` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `snd_rec` enum('rec','snd') NOT NULL DEFAULT 'rec',
  `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`,`snd_rec`,`user_id`),
  KEY `user_id` (`user_id`,`snd_rec`,`deleted`,`readed`,`mkdate`),
  KEY `user_id_2` (`user_id`,`snd_rec`,`deleted`,`mkdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_user`
--

LOCK TABLES `message_user` WRITE;
/*!40000 ALTER TABLE `message_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `news_id` varchar(32) NOT NULL DEFAULT '',
  `topic` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `author` varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `expire` int(11) NOT NULL DEFAULT '0',
  `allow_comments` tinyint(1) NOT NULL DEFAULT '0',
  `chdate` int(10) unsigned NOT NULL DEFAULT '0',
  `chdate_uid` varchar(32) NOT NULL DEFAULT '',
  `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`news_id`),
  KEY `date` (`date`),
  KEY `chdate` (`chdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES ('29f2932ce32be989022c6f43b866e744','Herzlich Willkommen!','Das Stud.IP-Team heisst sie herzlich willkommen. \r\nBitte schauen Sie sich ruhig um!\r\n\r\nWenn Sie das System selbst installiert haben und diese News sehen, haben Sie die Demonstrationsdaten in die Datenbank eingef?gt. Wenn Sie produktiv mit dem System arbeiten wollen, sollten Sie diese Daten sp?ter wieder l?schen, da die Passw?rter der Accounts (vor allem des root-Accounts) ?ffentlich bekannt sind.','Root Studip',1417519700,'76ed43ef286fb55cf9e41beadb484a9f',14562502,1,1417519700,'',1417519700);
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_range`
--

DROP TABLE IF EXISTS `news_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_range` (
  `news_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`news_id`,`range_id`),
  KEY `range_id` (`range_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_range`
--

LOCK TABLES `news_range` WRITE;
/*!40000 ALTER TABLE `news_range` DISABLE KEYS */;
INSERT INTO `news_range` VALUES ('29f2932ce32be989022c6f43b866e744','76ed43ef286fb55cf9e41beadb484a9f'),('29f2932ce32be989022c6f43b866e744','studip');
/*!40000 ALTER TABLE `news_range` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_rss_range`
--

DROP TABLE IF EXISTS `news_rss_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_rss_range` (
  `range_id` char(32) NOT NULL DEFAULT '',
  `rss_id` char(32) NOT NULL DEFAULT '',
  `range_type` enum('user','sem','inst','global') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`range_id`),
  KEY `rss_id` (`rss_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_rss_range`
--

LOCK TABLES `news_rss_range` WRITE;
/*!40000 ALTER TABLE `news_rss_range` DISABLE KEYS */;
INSERT INTO `news_rss_range` VALUES ('studip','70cefd1e80398bb20ff599636546cdff','global');
/*!40000 ALTER TABLE `news_rss_range` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_consumer_registry`
--

DROP TABLE IF EXISTS `oauth_consumer_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_consumer_registry` (
  `ocr_id` int(11) NOT NULL AUTO_INCREMENT,
  `ocr_usa_id_ref` int(11) DEFAULT NULL,
  `ocr_consumer_key` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `ocr_consumer_secret` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `ocr_signature_methods` varchar(128) NOT NULL DEFAULT 'HMAC-SHA1,PLAINTEXT',
  `ocr_server_uri` varchar(128) NOT NULL,
  `ocr_server_uri_host` varchar(128) NOT NULL,
  `ocr_server_uri_path` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `ocr_request_token_uri` varchar(255) NOT NULL,
  `ocr_authorize_uri` varchar(255) NOT NULL,
  `ocr_access_token_uri` varchar(255) NOT NULL,
  `ocr_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ocr_id`),
  UNIQUE KEY `ocr_consumer_key` (`ocr_consumer_key`,`ocr_usa_id_ref`,`ocr_server_uri`),
  KEY `ocr_server_uri` (`ocr_server_uri`),
  KEY `ocr_server_uri_host` (`ocr_server_uri_host`,`ocr_server_uri_path`),
  KEY `ocr_usa_id_ref` (`ocr_usa_id_ref`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_consumer_registry`
--

LOCK TABLES `oauth_consumer_registry` WRITE;
/*!40000 ALTER TABLE `oauth_consumer_registry` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_consumer_registry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_consumer_token`
--

DROP TABLE IF EXISTS `oauth_consumer_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_consumer_token` (
  `oct_id` int(11) NOT NULL AUTO_INCREMENT,
  `oct_ocr_id_ref` int(11) NOT NULL,
  `oct_usa_id_ref` int(11) NOT NULL,
  `oct_name` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `oct_token` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `oct_token_secret` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `oct_token_type` enum('request','authorized','access') DEFAULT NULL,
  `oct_token_ttl` datetime NOT NULL DEFAULT '9999-12-31 00:00:00',
  `oct_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`oct_id`),
  UNIQUE KEY `oct_ocr_id_ref` (`oct_ocr_id_ref`,`oct_token`),
  UNIQUE KEY `oct_usa_id_ref` (`oct_usa_id_ref`,`oct_ocr_id_ref`,`oct_token_type`,`oct_name`),
  KEY `oct_token_ttl` (`oct_token_ttl`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_consumer_token`
--

LOCK TABLES `oauth_consumer_token` WRITE;
/*!40000 ALTER TABLE `oauth_consumer_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_consumer_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_log`
--

DROP TABLE IF EXISTS `oauth_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_log` (
  `olg_id` int(11) NOT NULL AUTO_INCREMENT,
  `olg_osr_consumer_key` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `olg_ost_token` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `olg_ocr_consumer_key` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `olg_oct_token` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `olg_usa_id_ref` int(11) DEFAULT NULL,
  `olg_received` text NOT NULL,
  `olg_sent` text NOT NULL,
  `olg_base_string` text NOT NULL,
  `olg_notes` text NOT NULL,
  `olg_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `olg_remote_ip` bigint(20) NOT NULL,
  PRIMARY KEY (`olg_id`),
  KEY `olg_osr_consumer_key` (`olg_osr_consumer_key`,`olg_id`),
  KEY `olg_ost_token` (`olg_ost_token`,`olg_id`),
  KEY `olg_ocr_consumer_key` (`olg_ocr_consumer_key`,`olg_id`),
  KEY `olg_oct_token` (`olg_oct_token`,`olg_id`),
  KEY `olg_usa_id_ref` (`olg_usa_id_ref`,`olg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_log`
--

LOCK TABLES `oauth_log` WRITE;
/*!40000 ALTER TABLE `oauth_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_server_nonce`
--

DROP TABLE IF EXISTS `oauth_server_nonce`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_server_nonce` (
  `osn_id` int(11) NOT NULL AUTO_INCREMENT,
  `osn_consumer_key` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `osn_token` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `osn_timestamp` bigint(20) NOT NULL,
  `osn_nonce` varchar(80) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`osn_id`),
  UNIQUE KEY `osn_consumer_key` (`osn_consumer_key`,`osn_token`,`osn_timestamp`,`osn_nonce`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_server_nonce`
--

LOCK TABLES `oauth_server_nonce` WRITE;
/*!40000 ALTER TABLE `oauth_server_nonce` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_server_nonce` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_server_registry`
--

DROP TABLE IF EXISTS `oauth_server_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_server_registry` (
  `osr_id` int(11) NOT NULL AUTO_INCREMENT,
  `osr_usa_id_ref` int(11) DEFAULT NULL,
  `osr_consumer_key` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `osr_consumer_secret` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `osr_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `osr_status` varchar(16) NOT NULL,
  `osr_requester_name` varchar(64) NOT NULL,
  `osr_requester_email` varchar(64) NOT NULL,
  `osr_callback_uri` varchar(255) NOT NULL,
  `osr_application_uri` varchar(255) NOT NULL,
  `osr_application_title` varchar(80) NOT NULL,
  `osr_application_descr` text NOT NULL,
  `osr_application_notes` text NOT NULL,
  `osr_application_type` varchar(20) NOT NULL,
  `osr_application_commercial` tinyint(1) NOT NULL DEFAULT '0',
  `osr_issue_date` datetime NOT NULL,
  `osr_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`osr_id`),
  UNIQUE KEY `osr_consumer_key` (`osr_consumer_key`),
  KEY `osr_usa_id_ref` (`osr_usa_id_ref`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_server_registry`
--

LOCK TABLES `oauth_server_registry` WRITE;
/*!40000 ALTER TABLE `oauth_server_registry` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_server_registry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_server_token`
--

DROP TABLE IF EXISTS `oauth_server_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_server_token` (
  `ost_id` int(11) NOT NULL AUTO_INCREMENT,
  `ost_osr_id_ref` int(11) NOT NULL,
  `ost_usa_id_ref` int(11) NOT NULL,
  `ost_token` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `ost_token_secret` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `ost_token_type` enum('request','access') DEFAULT NULL,
  `ost_authorized` tinyint(1) NOT NULL DEFAULT '0',
  `ost_referrer_host` varchar(128) NOT NULL DEFAULT '',
  `ost_token_ttl` datetime NOT NULL DEFAULT '9999-12-31 00:00:00',
  `ost_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ost_verifier` char(10) DEFAULT NULL,
  `ost_callback_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`ost_id`),
  UNIQUE KEY `ost_token` (`ost_token`),
  KEY `ost_osr_id_ref` (`ost_osr_id_ref`),
  KEY `ost_token_ttl` (`ost_token_ttl`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_server_token`
--

LOCK TABLES `oauth_server_token` WRITE;
/*!40000 ALTER TABLE `oauth_server_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_server_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `object_contentmodules`
--

DROP TABLE IF EXISTS `object_contentmodules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `object_contentmodules` (
  `object_id` varchar(32) NOT NULL DEFAULT '',
  `module_id` varchar(255) NOT NULL DEFAULT '',
  `system_type` varchar(32) NOT NULL DEFAULT '',
  `module_type` varchar(32) NOT NULL DEFAULT '',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`module_id`,`system_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `object_contentmodules`
--

LOCK TABLES `object_contentmodules` WRITE;
/*!40000 ALTER TABLE `object_contentmodules` DISABLE KEYS */;
/*!40000 ALTER TABLE `object_contentmodules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `object_user_visits`
--

DROP TABLE IF EXISTS `object_user_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `object_user_visits` (
  `object_id` char(32) NOT NULL DEFAULT '',
  `user_id` char(32) NOT NULL DEFAULT '',
  `type` enum('vote','documents','forum','literature','schedule','scm','sem','wiki','news','eval','inst','ilias_connect','elearning_interface','participants') NOT NULL DEFAULT 'vote',
  `visitdate` int(20) NOT NULL DEFAULT '0',
  `last_visitdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`user_id`,`type`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `object_user_visits`
--

LOCK TABLES `object_user_visits` WRITE;
/*!40000 ALTER TABLE `object_user_visits` DISABLE KEYS */;
/*!40000 ALTER TABLE `object_user_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `object_views`
--

DROP TABLE IF EXISTS `object_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `object_views` (
  `object_id` varchar(32) NOT NULL DEFAULT '',
  `views` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`),
  KEY `views` (`views`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `object_views`
--

LOCK TABLES `object_views` WRITE;
/*!40000 ALTER TABLE `object_views` DISABLE KEYS */;
/*!40000 ALTER TABLE `object_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opengraphdata`
--

DROP TABLE IF EXISTS `opengraphdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opengraphdata` (
  `url` varchar(1000) NOT NULL,
  `is_opengraph` tinyint(2) DEFAULT NULL,
  `title` text,
  `image` varchar(1024) DEFAULT NULL,
  `description` text,
  `type` varchar(64) DEFAULT NULL,
  `data` text NOT NULL,
  `last_update` bigint(20) NOT NULL,
  `chdate` bigint(20) NOT NULL,
  `mkdate` bigint(20) NOT NULL,
  PRIMARY KEY (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opengraphdata`
--

LOCK TABLES `opengraphdata` WRITE;
/*!40000 ALTER TABLE `opengraphdata` DISABLE KEYS */;
/*!40000 ALTER TABLE `opengraphdata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participantrestrictedadmissions`
--

DROP TABLE IF EXISTS `participantrestrictedadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `participantrestrictedadmissions` (
  `rule_id` varchar(32) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `distribution_time` int(11) NOT NULL DEFAULT '0',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantrestrictedadmissions`
--

LOCK TABLES `participantrestrictedadmissions` WRITE;
/*!40000 ALTER TABLE `participantrestrictedadmissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `participantrestrictedadmissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passwordadmissions`
--

DROP TABLE IF EXISTS `passwordadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `passwordadmissions` (
  `rule_id` varchar(32) NOT NULL,
  `message` text,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `password` varchar(255) DEFAULT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passwordadmissions`
--

LOCK TABLES `passwordadmissions` WRITE;
/*!40000 ALTER TABLE `passwordadmissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `passwordadmissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_notifications`
--

DROP TABLE IF EXISTS `personal_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_notifications` (
  `personal_notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(512) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `avatar` varchar(256) NOT NULL DEFAULT '',
  `html_id` varchar(64) NOT NULL DEFAULT '',
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`personal_notification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_notifications`
--

LOCK TABLES `personal_notifications` WRITE;
/*!40000 ALTER TABLE `personal_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_notifications_user`
--

DROP TABLE IF EXISTS `personal_notifications_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_notifications_user` (
  `personal_notification_id` int(10) unsigned NOT NULL,
  `user_id` binary(32) NOT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`personal_notification_id`,`user_id`),
  KEY `user_id` (`user_id`,`seen`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_notifications_user`
--

LOCK TABLES `personal_notifications_user` WRITE;
/*!40000 ALTER TABLE `personal_notifications_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_notifications_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins` (
  `pluginid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pluginclassname` varchar(255) NOT NULL DEFAULT '',
  `pluginpath` varchar(255) NOT NULL DEFAULT '',
  `pluginname` varchar(45) NOT NULL DEFAULT '',
  `plugintype` text NOT NULL,
  `enabled` enum('yes','no') NOT NULL DEFAULT 'no',
  `navigationpos` int(10) unsigned NOT NULL DEFAULT '0',
  `dependentonid` int(10) unsigned DEFAULT NULL,
  `automatic_update_url` varchar(256) DEFAULT NULL,
  `automatic_update_secret` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`pluginid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins`
--

LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;
INSERT INTO `plugins` VALUES (1,'Blubber','core/Blubber','Blubber','StandardPlugin,SystemPlugin','yes',1,NULL,NULL,NULL),(2,'CoreForum','core/Forum','Forum','ForumModule,StandardPlugin,StudipModule','yes',2,NULL,NULL,NULL),(3,'EvaluationsWidget','core/EvaluationsWidget','EvaluationsWidget','PortalPlugin','yes',3,NULL,NULL,NULL),(4,'NewsWidget','core/NewsWidget','NewsWidget','PortalPlugin','yes',4,NULL,NULL,NULL),(5,'QuickSelection','core/QuickSelection','QuickSelection','PortalPlugin','yes',5,NULL,NULL,NULL),(6,'ScheduleWidget','core/ScheduleWidget','ScheduleWidget','PortalPlugin','yes',6,NULL,NULL,NULL),(7,'TerminWidget','core/TerminWidget','TerminWidget','PortalPlugin','yes',7,NULL,NULL,NULL);
/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins_activated`
--

DROP TABLE IF EXISTS `plugins_activated`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins_activated` (
  `pluginid` int(10) unsigned NOT NULL DEFAULT '0',
  `poiid` varchar(36) NOT NULL DEFAULT '',
  `state` enum('on','off') NOT NULL DEFAULT 'on',
  PRIMARY KEY (`pluginid`,`poiid`),
  UNIQUE KEY `poiid` (`poiid`,`pluginid`,`state`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins_activated`
--

LOCK TABLES `plugins_activated` WRITE;
/*!40000 ALTER TABLE `plugins_activated` DISABLE KEYS */;
INSERT INTO `plugins_activated` VALUES (1,'sema07535cf2f8a72df33c12ddfa4b53dde','on'),(2,'sema07535cf2f8a72df33c12ddfa4b53dde','on');
/*!40000 ALTER TABLE `plugins_activated` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins_default_activations`
--

DROP TABLE IF EXISTS `plugins_default_activations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins_default_activations` (
  `pluginid` int(10) unsigned NOT NULL DEFAULT '0',
  `institutid` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`pluginid`,`institutid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='default activations of standard plugins';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins_default_activations`
--

LOCK TABLES `plugins_default_activations` WRITE;
/*!40000 ALTER TABLE `plugins_default_activations` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugins_default_activations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `priorities`
--

DROP TABLE IF EXISTS `priorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `priorities` (
  `user_id` varchar(32) NOT NULL,
  `set_id` varchar(32) NOT NULL,
  `seminar_id` varchar(32) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`set_id`,`seminar_id`),
  KEY `user_rule_priority` (`user_id`,`priority`,`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `priorities`
--

LOCK TABLES `priorities` WRITE;
/*!40000 ALTER TABLE `priorities` DISABLE KEYS */;
/*!40000 ALTER TABLE `priorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `range_tree`
--

DROP TABLE IF EXISTS `range_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `range_tree` (
  `item_id` varchar(32) NOT NULL DEFAULT '',
  `parent_id` varchar(32) NOT NULL DEFAULT '',
  `level` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `studip_object` varchar(10) DEFAULT NULL,
  `studip_object_id` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `parent_id` (`parent_id`),
  KEY `priority` (`priority`),
  KEY `studip_object_id` (`studip_object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `range_tree`
--

LOCK TABLES `range_tree` WRITE;
/*!40000 ALTER TABLE `range_tree` DISABLE KEYS */;
INSERT INTO `range_tree` VALUES ('3f93863e3d37ba0df286a6e7e26974ef','root',0,0,'Einrichtungen der Universit','',''),('1323254564871354786157481484621','3f93863e3d37ba0df286a6e7e26974ef',1,0,'','inst','1535795b0d6ddecac6813f5f6ac47ef2'),('ce6c87bbf759b4cfd6f92d0c5560da5c','1323254564871354786157481484621',0,0,'Test Einrichtung','inst','2560f7c7674942a7dce8eeb238e15d93'),('2f4f90ac9d8d832cc8c8a95910fde4eb','1323254564871354786157481484621',0,1,'Test Lehrstuhl','inst','536249daa596905f433e1f73578019db'),('5d032f70c255f3e57cf8aa85a429ad4e','1323254564871354786157481484621',0,2,'Test Abteilung','inst','f02e2b17bc0e99fc885da6ac4c2532dc'),('a3d977a66f0010fa8e15c27dd71aff63','root',0,1,'externe Bildungseinrichtungen','fak','ec2e364b28357106c0f8c282733dbe56'),('e0ff0ead6a8c5191078ed787cd7c0c1f','a3d977a66f0010fa8e15c27dd71aff63',0,0,'externe Einrichtung A','inst','7a4f19a0a2c321ab2b8f7b798881af7c'),('105b70b72dc1908ce2925e057c4a8daa','a3d977a66f0010fa8e15c27dd71aff63',0,1,'externe Einrichtung B','inst','110ce78ffefaf1e5f167cd7019b728bf');
/*!40000 ALTER TABLE `range_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_assign`
--

DROP TABLE IF EXISTS `resources_assign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_assign` (
  `assign_id` varchar(32) NOT NULL DEFAULT '',
  `resource_id` varchar(32) NOT NULL DEFAULT '',
  `assign_user_id` varchar(32) DEFAULT NULL,
  `user_free_name` varchar(255) DEFAULT NULL,
  `begin` int(20) NOT NULL DEFAULT '0',
  `end` int(20) NOT NULL DEFAULT '0',
  `repeat_end` int(20) DEFAULT NULL,
  `repeat_quantity` int(2) DEFAULT NULL,
  `repeat_interval` int(2) DEFAULT NULL,
  `repeat_month_of_year` int(2) DEFAULT NULL,
  `repeat_day_of_month` int(2) DEFAULT NULL,
  `repeat_week_of_month` int(2) DEFAULT NULL,
  `repeat_day_of_week` int(2) DEFAULT NULL,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `comment_internal` text,
  PRIMARY KEY (`assign_id`),
  KEY `resource_id` (`resource_id`),
  KEY `assign_user_id` (`assign_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_assign`
--

LOCK TABLES `resources_assign` WRITE;
/*!40000 ALTER TABLE `resources_assign` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_assign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_categories`
--

DROP TABLE IF EXISTS `resources_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_categories` (
  `category_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `system` tinyint(4) NOT NULL DEFAULT '0',
  `is_room` tinyint(4) NOT NULL DEFAULT '0',
  `iconnr` int(3) DEFAULT '1',
  PRIMARY KEY (`category_id`),
  KEY `is_room` (`is_room`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_categories`
--

LOCK TABLES `resources_categories` WRITE;
/*!40000 ALTER TABLE `resources_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_categories_properties`
--

DROP TABLE IF EXISTS `resources_categories_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_categories_properties` (
  `category_id` varchar(32) NOT NULL DEFAULT '',
  `property_id` varchar(32) NOT NULL DEFAULT '',
  `requestable` tinyint(4) NOT NULL DEFAULT '0',
  `system` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`property_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_categories_properties`
--

LOCK TABLES `resources_categories_properties` WRITE;
/*!40000 ALTER TABLE `resources_categories_properties` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_categories_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_locks`
--

DROP TABLE IF EXISTS `resources_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_locks` (
  `lock_id` varchar(32) NOT NULL DEFAULT '',
  `lock_begin` int(20) unsigned DEFAULT NULL,
  `lock_end` int(20) unsigned DEFAULT NULL,
  `type` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`lock_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_locks`
--

LOCK TABLES `resources_locks` WRITE;
/*!40000 ALTER TABLE `resources_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_objects`
--

DROP TABLE IF EXISTS `resources_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_objects` (
  `resource_id` varchar(32) NOT NULL DEFAULT '',
  `root_id` varchar(32) NOT NULL DEFAULT '',
  `parent_id` varchar(32) NOT NULL DEFAULT '',
  `category_id` varchar(32) NOT NULL DEFAULT '',
  `owner_id` varchar(32) NOT NULL DEFAULT '',
  `institut_id` varchar(32) NOT NULL DEFAULT '',
  `level` int(4) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `lockable` tinyint(4) DEFAULT NULL,
  `multiple_assign` tinyint(4) DEFAULT NULL,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`resource_id`),
  KEY `institut_id` (`institut_id`),
  KEY `root_id` (`root_id`),
  KEY `parent_id` (`parent_id`),
  KEY `category_id` (`category_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_objects`
--

LOCK TABLES `resources_objects` WRITE;
/*!40000 ALTER TABLE `resources_objects` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_objects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_objects_properties`
--

DROP TABLE IF EXISTS `resources_objects_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_objects_properties` (
  `resource_id` varchar(32) NOT NULL DEFAULT '',
  `property_id` varchar(32) NOT NULL DEFAULT '',
  `state` text NOT NULL,
  PRIMARY KEY (`resource_id`,`property_id`),
  KEY `property_id` (`property_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_objects_properties`
--

LOCK TABLES `resources_objects_properties` WRITE;
/*!40000 ALTER TABLE `resources_objects_properties` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_objects_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_properties`
--

DROP TABLE IF EXISTS `resources_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_properties` (
  `property_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `type` set('bool','text','num','select') NOT NULL DEFAULT 'bool',
  `options` text NOT NULL,
  `system` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`property_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_properties`
--

LOCK TABLES `resources_properties` WRITE;
/*!40000 ALTER TABLE `resources_properties` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_requests`
--

DROP TABLE IF EXISTS `resources_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_requests` (
  `request_id` varchar(32) NOT NULL DEFAULT '',
  `seminar_id` varchar(32) NOT NULL DEFAULT '',
  `termin_id` varchar(32) NOT NULL DEFAULT '',
  `metadate_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `last_modified_by` varchar(32) NOT NULL DEFAULT '',
  `resource_id` varchar(32) NOT NULL DEFAULT '',
  `category_id` varchar(32) NOT NULL DEFAULT '',
  `comment` text,
  `reply_comment` text,
  `reply_recipients` enum('requester','lecturer') NOT NULL DEFAULT 'requester',
  `closed` tinyint(3) unsigned DEFAULT NULL,
  `mkdate` int(20) unsigned DEFAULT NULL,
  `chdate` int(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `termin_id` (`termin_id`),
  KEY `seminar_id` (`seminar_id`),
  KEY `user_id` (`user_id`),
  KEY `resource_id` (`resource_id`),
  KEY `category_id` (`category_id`),
  KEY `closed` (`closed`,`request_id`,`resource_id`),
  KEY `metadate_id` (`metadate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_requests`
--

LOCK TABLES `resources_requests` WRITE;
/*!40000 ALTER TABLE `resources_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_requests_properties`
--

DROP TABLE IF EXISTS `resources_requests_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_requests_properties` (
  `request_id` varchar(32) NOT NULL DEFAULT '',
  `property_id` varchar(32) NOT NULL DEFAULT '',
  `state` text,
  `mkdate` int(20) unsigned DEFAULT NULL,
  `chdate` int(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`request_id`,`property_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_requests_properties`
--

LOCK TABLES `resources_requests_properties` WRITE;
/*!40000 ALTER TABLE `resources_requests_properties` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_requests_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_requests_user_status`
--

DROP TABLE IF EXISTS `resources_requests_user_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_requests_user_status` (
  `request_id` char(32) NOT NULL DEFAULT '',
  `user_id` char(32) NOT NULL DEFAULT '',
  `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`request_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_requests_user_status`
--

LOCK TABLES `resources_requests_user_status` WRITE;
/*!40000 ALTER TABLE `resources_requests_user_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_requests_user_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_temporary_events`
--

DROP TABLE IF EXISTS `resources_temporary_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_temporary_events` (
  `event_id` char(32) NOT NULL DEFAULT '',
  `resource_id` char(32) NOT NULL DEFAULT '',
  `assign_id` char(32) NOT NULL DEFAULT '',
  `begin` int(20) NOT NULL DEFAULT '0',
  `end` int(20) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`),
  KEY `resource_id` (`resource_id`,`begin`),
  KEY `assign_object_id` (`assign_id`,`resource_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_temporary_events`
--

LOCK TABLES `resources_temporary_events` WRITE;
/*!40000 ALTER TABLE `resources_temporary_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_temporary_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources_user_resources`
--

DROP TABLE IF EXISTS `resources_user_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources_user_resources` (
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `resource_id` varchar(32) NOT NULL DEFAULT '',
  `perms` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`,`resource_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources_user_resources`
--

LOCK TABLES `resources_user_resources` WRITE;
/*!40000 ALTER TABLE `resources_user_resources` DISABLE KEYS */;
/*!40000 ALTER TABLE `resources_user_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `roleid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rolename` varchar(80) NOT NULL DEFAULT '',
  `system` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`roleid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Root-Administrator(in)','y'),(2,'Administrator(in)','y'),(3,'Mitarbeiter(in)','y'),(4,'Lehrende(r)','y'),(5,'Studierende(r)','y'),(6,'Tutor(in)','y'),(7,'Nobody','y');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_plugins`
--

DROP TABLE IF EXISTS `roles_plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_plugins` (
  `roleid` int(10) unsigned NOT NULL DEFAULT '0',
  `pluginid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`roleid`,`pluginid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_plugins`
--

LOCK TABLES `roles_plugins` WRITE;
/*!40000 ALTER TABLE `roles_plugins` DISABLE KEYS */;
INSERT INTO `roles_plugins` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(5,1),(5,2),(5,3),(5,4),(5,5),(5,6),(5,7),(6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),(7,1),(7,2);
/*!40000 ALTER TABLE `roles_plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_studipperms`
--

DROP TABLE IF EXISTS `roles_studipperms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_studipperms` (
  `roleid` int(10) unsigned NOT NULL DEFAULT '0',
  `permname` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`roleid`,`permname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_studipperms`
--

LOCK TABLES `roles_studipperms` WRITE;
/*!40000 ALTER TABLE `roles_studipperms` DISABLE KEYS */;
INSERT INTO `roles_studipperms` VALUES (1,'root'),(2,'admin'),(3,'admin'),(3,'root'),(4,'dozent'),(5,'autor'),(5,'tutor'),(6,'tutor');
/*!40000 ALTER TABLE `roles_studipperms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_user`
--

DROP TABLE IF EXISTS `roles_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_user` (
  `roleid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` char(32) NOT NULL DEFAULT '',
  `institut_id` char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`roleid`,`userid`,`institut_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_user`
--

LOCK TABLES `roles_user` WRITE;
/*!40000 ALTER TABLE `roles_user` DISABLE KEYS */;
INSERT INTO `roles_user` VALUES (7,'nobody','');
/*!40000 ALTER TABLE `roles_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start` smallint(6) NOT NULL COMMENT 'start hour and minutes',
  `end` smallint(6) NOT NULL COMMENT 'end hour and minutes',
  `day` tinyint(4) NOT NULL COMMENT 'day of week, 0-6',
  `title` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL COMMENT 'color, rgb in hex',
  `user_id` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule`
--

LOCK TABLES `schedule` WRITE;
/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_seminare`
--

DROP TABLE IF EXISTS `schedule_seminare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_seminare` (
  `user_id` varchar(32) NOT NULL,
  `seminar_id` varchar(32) NOT NULL,
  `metadate_id` varchar(32) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `color` varchar(7) DEFAULT NULL COMMENT 'color, rgb in hex',
  PRIMARY KEY (`user_id`,`seminar_id`,`metadate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_seminare`
--

LOCK TABLES `schedule_seminare` WRITE;
/*!40000 ALTER TABLE `schedule_seminare` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_seminare` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schema_version`
--

DROP TABLE IF EXISTS `schema_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schema_version` (
  `domain` varchar(255) NOT NULL DEFAULT '',
  `version` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schema_version`
--

LOCK TABLES `schema_version` WRITE;
/*!40000 ALTER TABLE `schema_version` DISABLE KEYS */;
INSERT INTO `schema_version` VALUES ('studip',155);
/*!40000 ALTER TABLE `schema_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scm`
--

DROP TABLE IF EXISTS `scm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scm` (
  `scm_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `tab_name` varchar(20) NOT NULL DEFAULT 'Info',
  `content` text,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `position` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`scm_id`),
  KEY `chdate` (`chdate`),
  KEY `range_id` (`range_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scm`
--

LOCK TABLES `scm` WRITE;
/*!40000 ALTER TABLE `scm` DISABLE KEYS */;
INSERT INTO `scm` VALUES ('a07df31918cc8e5ca0597e959a4a5297','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','Informationen','',1343924407,1343924407,0);
/*!40000 ALTER TABLE `scm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sem_classes`
--

DROP TABLE IF EXISTS `sem_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sem_classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `compact_mode` tinyint(4) NOT NULL,
  `workgroup_mode` tinyint(4) NOT NULL,
  `only_inst_user` tinyint(4) NOT NULL,
  `turnus_default` int(11) NOT NULL,
  `default_read_level` int(11) NOT NULL,
  `default_write_level` int(11) NOT NULL,
  `bereiche` tinyint(4) NOT NULL,
  `show_browse` tinyint(4) NOT NULL,
  `write_access_nobody` tinyint(4) NOT NULL,
  `topic_create_autor` tinyint(4) NOT NULL,
  `visible` tinyint(4) NOT NULL,
  `course_creation_forbidden` tinyint(4) NOT NULL,
  `overview` varchar(64) DEFAULT NULL,
  `forum` varchar(64) DEFAULT NULL,
  `admin` varchar(64) DEFAULT NULL,
  `documents` varchar(64) DEFAULT NULL,
  `schedule` varchar(64) DEFAULT NULL,
  `participants` varchar(64) DEFAULT NULL,
  `literature` varchar(64) DEFAULT NULL,
  `scm` varchar(64) DEFAULT NULL,
  `wiki` varchar(64) DEFAULT NULL,
  `resources` varchar(64) DEFAULT NULL,
  `calendar` varchar(64) DEFAULT NULL,
  `elearning_interface` varchar(64) DEFAULT NULL,
  `modules` text NOT NULL,
  `description` text NOT NULL,
  `create_description` text NOT NULL,
  `studygroup_mode` tinyint(4) NOT NULL,
  `admission_prelim_default` tinyint(4) NOT NULL DEFAULT '0',
  `admission_type_default` tinyint(4) NOT NULL DEFAULT '0',
  `title_dozent` varchar(64) DEFAULT NULL,
  `title_dozent_plural` varchar(64) DEFAULT NULL,
  `title_tutor` varchar(64) DEFAULT NULL,
  `title_tutor_plural` varchar(64) DEFAULT NULL,
  `title_autor` varchar(64) DEFAULT NULL,
  `title_autor_plural` varchar(64) DEFAULT NULL,
  `mkdate` bigint(20) NOT NULL,
  `chdate` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sem_classes`
--

LOCK TABLES `sem_classes` WRITE;
/*!40000 ALTER TABLE `sem_classes` DISABLE KEYS */;
INSERT INTO `sem_classes` VALUES (1,'Lehre',0,0,1,0,1,1,1,1,0,0,1,0,'CoreOverview','CoreForum','CoreAdmin','CoreDocuments','CoreSchedule','CoreParticipants','CoreLiterature','CoreScm','CoreWiki','CoreResources','CoreCalendar','CoreElearningInterface','{\"CoreOverview\":{\"activated\":\"1\",\"sticky\":\"1\"},\"CoreAdmin\":{\"activated\":\"1\",\"sticky\":\"1\"},\"CoreForum\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreParticipants\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreDocuments\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreSchedule\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreLiterature\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreScm\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreWiki\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreResources\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreCalendar\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreElearningInterface\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreStudygroupAdmin\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreStudygroupParticipants\":{\"activated\":\"0\",\"sticky\":\"1\"}}','Hier finden Sie alle in Stud.IP registrierten Lehrveranstaltungen','',0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1366882120,1366882169),(2,'Organisation',1,1,0,-1,2,2,0,1,0,0,1,0,'CoreOverview','CoreForum','CoreAdmin','CoreDocuments','CoreSchedule','CoreParticipants',NULL,NULL,'CoreWiki','CoreResources',NULL,NULL,'{\"CoreOverview\":{\"activated\":\"1\",\"sticky\":\"1\"},\"CoreAdmin\":{\"activated\":\"1\",\"sticky\":\"1\"},\"CoreForum\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreParticipants\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreDocuments\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreSchedule\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreWiki\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreResources\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreStudygroupAdmin\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreStudygroupParticipants\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreScm\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreLiterature\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreCalendar\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreElearningInterface\":{\"activated\":\"0\",\"sticky\":\"1\"}}','Hier finden Sie virtuelle Veranstaltungen zu verschiedenen Gremien an der Universit&auml;t','',0,0,0,'LeiterIn','LeiterInnen','Mitglied','Mitglieder',NULL,NULL,1366882120,1366882198),(3,'Community',1,0,0,-1,1,1,0,1,1,0,1,0,'CoreOverview','CoreForum','CoreAdmin','CoreDocuments','CoreSchedule','CoreParticipants','CoreLiterature',NULL,'CoreWiki','CoreResources',NULL,NULL,'{\"CoreOverview\":{\"activated\":1,\"sticky\":1},\"CoreAdmin\":{\"activated\":1,\"sticky\":1}}','Hier finden Sie virtuelle Veranstaltungen zu unterschiedlichen Themen','',0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,1366882120,1366882120),(99,'Studiengruppen',0,0,0,0,0,0,0,0,0,1,0,1,'CoreOverview','CoreForum','CoreStudygroupAdmin','CoreDocuments',NULL,'CoreStudygroupParticipants',NULL,'CoreScm','CoreWiki',NULL,NULL,NULL,'{\"CoreOverview\":{\"activated\":\"1\",\"sticky\":\"1\"},\"CoreStudygroupAdmin\":{\"activated\":\"1\",\"sticky\":\"1\"},\"CoreForum\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreStudygroupParticipants\":{\"activated\":\"1\",\"sticky\":\"1\"},\"CoreDocuments\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreScm\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreWiki\":{\"activated\":\"1\",\"sticky\":\"0\"},\"CoreAdmin\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreSchedule\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreParticipants\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreLiterature\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreCalendar\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreElearningInterface\":{\"activated\":\"0\",\"sticky\":\"1\"},\"CoreResources\":{\"activated\":\"0\",\"sticky\":\"1\"}}','','',1,0,0,'Gruppengr?nderIn','Gruppengr?nderInnen','ModeratorIn','ModeratorInnen','Mitglied','Mitglieder',1366882120,1366882252);
/*!40000 ALTER TABLE `sem_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sem_tree`
--

DROP TABLE IF EXISTS `sem_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sem_tree` (
  `sem_tree_id` varchar(32) NOT NULL DEFAULT '',
  `parent_id` varchar(32) NOT NULL DEFAULT '',
  `priority` tinyint(4) NOT NULL DEFAULT '0',
  `info` text NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `studip_object_id` varchar(32) DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`sem_tree_id`),
  KEY `parent_id` (`parent_id`),
  KEY `priority` (`priority`),
  KEY `studip_object_id` (`studip_object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sem_tree`
--

LOCK TABLES `sem_tree` WRITE;
/*!40000 ALTER TABLE `sem_tree` DISABLE KEYS */;
INSERT INTO `sem_tree` VALUES ('5b73e28644a3e259a6e0bc1e1499773c','root',1,'','','1535795b0d6ddecac6813f5f6ac47ef2',0),('439618ae57d8c10dcaabcf7e21bcc1d9','5b73e28644a3e259a6e0bc1e1499773c',0,'','Test Studienbereich A',NULL,0),('5c41d2b4a5a8338e069dda987a624b74','5b73e28644a3e259a6e0bc1e1499773c',1,'','Test Studienbereich B',NULL,0),('3d39528c1d560441fd4a8cb0b7717285','439618ae57d8c10dcaabcf7e21bcc1d9',0,'','Test Studienbereich A-1',NULL,0),('dd7fff9151e85e7130cdb684edf0c370','439618ae57d8c10dcaabcf7e21bcc1d9',1,'','Test Studienbereich A-2',NULL,0),('01c8b1d188be40c5ac64b54a01aae294','5b73e28644a3e259a6e0bc1e1499773c',2,'','Test Studienbereich C',NULL,0);
/*!40000 ALTER TABLE `sem_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sem_types`
--

DROP TABLE IF EXISTS `sem_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sem_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `class` int(11) NOT NULL,
  `mkdate` bigint(20) NOT NULL,
  `chdate` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sem_types`
--

LOCK TABLES `sem_types` WRITE;
/*!40000 ALTER TABLE `sem_types` DISABLE KEYS */;
INSERT INTO `sem_types` VALUES (1,'Vorlesung',1,1366882120,1366882120),(2,'Seminar',1,1366882120,1366882120),(3,'?bung',1,1366882120,1366882120),(4,'Praktikum',1,1366882120,1366882120),(5,'Colloquium',1,1366882120,1366882120),(6,'Forschungsgruppe',1,1366882120,1366882120),(7,'sonstige',1,1366882120,1366882120),(8,'Gremium',2,1366882120,1366882120),(9,'Projektgruppe',2,1366882120,1366882120),(10,'sonstige',2,1366882120,1366882120),(11,'Kulturforum',3,1366882120,1366882120),(12,'Veranstaltungsboard',3,1366882120,1366882120),(13,'sonstige',3,1366882120,1366882120),(99,'Studiengruppe',99,1366882120,1366882120);
/*!40000 ALTER TABLE `sem_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semester_data`
--

DROP TABLE IF EXISTS `semester_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `semester_data` (
  `semester_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `semester_token` varchar(10) NOT NULL DEFAULT '',
  `beginn` int(20) unsigned DEFAULT NULL,
  `ende` int(20) unsigned DEFAULT NULL,
  `vorles_beginn` int(20) unsigned DEFAULT NULL,
  `vorles_ende` int(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`semester_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semester_data`
--

LOCK TABLES `semester_data` WRITE;
/*!40000 ALTER TABLE `semester_data` DISABLE KEYS */;
INSERT INTO `semester_data` VALUES ('f2b4fdf5ac59a9cb57dd73c4d3bbb651','SS 2015','','',1427839200,1443650399,1428962400,1437256799),('eb828ebb81bb946fac4108521a3b4697','WS 2013/14','','',1380578400,1396303199,1382306400,1391900399);
/*!40000 ALTER TABLE `semester_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semester_holiday`
--

DROP TABLE IF EXISTS `semester_holiday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `semester_holiday` (
  `holiday_id` varchar(32) NOT NULL DEFAULT '',
  `semester_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `beginn` int(20) unsigned DEFAULT NULL,
  `ende` int(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`holiday_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semester_holiday`
--

LOCK TABLES `semester_holiday` WRITE;
/*!40000 ALTER TABLE `semester_holiday` DISABLE KEYS */;
INSERT INTO `semester_holiday` VALUES ('75a24d5f6f0c4f633d5293221629b9a6','1','Weihnachtsferien 2014/2015','',1419375600,1420153199);
/*!40000 ALTER TABLE `semester_holiday` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminar_courseset`
--

DROP TABLE IF EXISTS `seminar_courseset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminar_courseset` (
  `set_id` varchar(32) NOT NULL,
  `seminar_id` varchar(32) NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`seminar_id`),
  KEY `seminar_id` (`seminar_id`,`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminar_courseset`
--

LOCK TABLES `seminar_courseset` WRITE;
/*!40000 ALTER TABLE `seminar_courseset` DISABLE KEYS */;
/*!40000 ALTER TABLE `seminar_courseset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminar_cycle_dates`
--

DROP TABLE IF EXISTS `seminar_cycle_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminar_cycle_dates` (
  `metadate_id` varchar(32) NOT NULL,
  `seminar_id` varchar(32) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `weekday` tinyint(3) unsigned NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `sws` decimal(2,1) NOT NULL DEFAULT '0.0',
  `cycle` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `week_offset` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sorter` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mkdate` int(10) unsigned NOT NULL,
  `chdate` int(10) unsigned NOT NULL,
  PRIMARY KEY (`metadate_id`),
  KEY `seminar_id` (`seminar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminar_cycle_dates`
--

LOCK TABLES `seminar_cycle_dates` WRITE;
/*!40000 ALTER TABLE `seminar_cycle_dates` DISABLE KEYS */;
INSERT INTO `seminar_cycle_dates` VALUES ('0309c794406b96bb01662e9e02593517','a07535cf2f8a72df33c12ddfa4b53dde','09:00:00','13:00:00',4,'',0.0,1,1,0,1343924407,1343924407),('d124b42deb48ac58adbd620b7ae6cc21','a07535cf2f8a72df33c12ddfa4b53dde','09:00:00','12:00:00',1,'',0.0,1,0,0,1343924407,1343924407);
/*!40000 ALTER TABLE `seminar_cycle_dates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminar_inst`
--

DROP TABLE IF EXISTS `seminar_inst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminar_inst` (
  `seminar_id` varchar(32) NOT NULL DEFAULT '',
  `institut_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`,`institut_id`),
  KEY `institut_id` (`institut_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminar_inst`
--

LOCK TABLES `seminar_inst` WRITE;
/*!40000 ALTER TABLE `seminar_inst` DISABLE KEYS */;
INSERT INTO `seminar_inst` VALUES ('a07535cf2f8a72df33c12ddfa4b53dde','2560f7c7674942a7dce8eeb238e15d93');
/*!40000 ALTER TABLE `seminar_inst` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminar_sem_tree`
--

DROP TABLE IF EXISTS `seminar_sem_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminar_sem_tree` (
  `seminar_id` varchar(32) NOT NULL DEFAULT '',
  `sem_tree_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`,`sem_tree_id`),
  KEY `sem_tree_id` (`sem_tree_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminar_sem_tree`
--

LOCK TABLES `seminar_sem_tree` WRITE;
/*!40000 ALTER TABLE `seminar_sem_tree` DISABLE KEYS */;
INSERT INTO `seminar_sem_tree` VALUES ('a07535cf2f8a72df33c12ddfa4b53dde','3d39528c1d560441fd4a8cb0b7717285'),('a07535cf2f8a72df33c12ddfa4b53dde','5c41d2b4a5a8338e069dda987a624b74'),('a07535cf2f8a72df33c12ddfa4b53dde','dd7fff9151e85e7130cdb684edf0c370');
/*!40000 ALTER TABLE `seminar_sem_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminar_user`
--

DROP TABLE IF EXISTS `seminar_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminar_user` (
  `Seminar_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `status` enum('user','autor','tutor','dozent') NOT NULL DEFAULT 'user',
  `position` int(11) NOT NULL DEFAULT '0',
  `gruppe` tinyint(4) NOT NULL DEFAULT '0',
  `notification` int(10) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `visible` enum('yes','no','unknown') NOT NULL DEFAULT 'unknown',
  `label` varchar(128) NOT NULL DEFAULT '',
  `bind_calendar` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Seminar_id`,`user_id`),
  KEY `status` (`status`,`Seminar_id`),
  KEY `user_id` (`user_id`,`Seminar_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminar_user`
--

LOCK TABLES `seminar_user` WRITE;
/*!40000 ALTER TABLE `seminar_user` DISABLE KEYS */;
INSERT INTO `seminar_user` VALUES ('a07535cf2f8a72df33c12ddfa4b53dde','e7a0a84b161f3e8c09b4a0a2e8a58147','autor',0,5,0,1343924589,'','unknown','',1),('7cb72dab1bf896a0b55c6aa7a70a3a86','e7a0a84b161f3e8c09b4a0a2e8a58147','dozent',0,8,0,0,'','unknown','',1),('a07535cf2f8a72df33c12ddfa4b53dde','205f3efb7997a0fc9755da2b535038da','dozent',0,5,0,1343924407,'','yes','',1),('a07535cf2f8a72df33c12ddfa4b53dde','7e81ec247c151c02ffd479511e24cc03','tutor',0,5,0,1343924407,'','yes','',1);
/*!40000 ALTER TABLE `seminar_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminar_userdomains`
--

DROP TABLE IF EXISTS `seminar_userdomains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminar_userdomains` (
  `seminar_id` varchar(32) NOT NULL DEFAULT '',
  `userdomain_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`,`userdomain_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminar_userdomains`
--

LOCK TABLES `seminar_userdomains` WRITE;
/*!40000 ALTER TABLE `seminar_userdomains` DISABLE KEYS */;
/*!40000 ALTER TABLE `seminar_userdomains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminare`
--

DROP TABLE IF EXISTS `seminare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminare` (
  `Seminar_id` varchar(32) NOT NULL DEFAULT '0',
  `VeranstaltungsNummer` varchar(100) DEFAULT NULL,
  `Institut_id` varchar(32) NOT NULL DEFAULT '0',
  `Name` varchar(255) NOT NULL DEFAULT '',
  `Untertitel` varchar(255) DEFAULT NULL,
  `status` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `Beschreibung` text NOT NULL,
  `Ort` varchar(255) DEFAULT NULL,
  `Sonstiges` text,
  `Lesezugriff` tinyint(4) NOT NULL DEFAULT '0',
  `Schreibzugriff` tinyint(4) NOT NULL DEFAULT '0',
  `start_time` int(20) DEFAULT '0',
  `duration_time` int(20) DEFAULT NULL,
  `art` varchar(255) DEFAULT NULL,
  `teilnehmer` text,
  `vorrausetzungen` text,
  `lernorga` text,
  `leistungsnachweis` text,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `ects` varchar(32) DEFAULT NULL,
  `admission_turnout` int(5) DEFAULT NULL,
  `admission_binding` tinyint(4) DEFAULT NULL,
  `admission_prelim` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `admission_prelim_txt` text,
  `admission_disable_waitlist` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `visible` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `showscore` tinyint(3) DEFAULT '0',
  `modules` int(10) unsigned DEFAULT NULL,
  `aux_lock_rule` varchar(32) DEFAULT NULL,
  `aux_lock_rule_forced` tinyint(4) NOT NULL DEFAULT '0',
  `lock_rule` varchar(32) DEFAULT NULL,
  `admission_waitlist_max` int(10) unsigned NOT NULL DEFAULT '0',
  `admission_disable_waitlist_move` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`Seminar_id`),
  KEY `Institut_id` (`Institut_id`),
  KEY `visible` (`visible`),
  KEY `status` (`status`,`Seminar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminare`
--

LOCK TABLES `seminare` WRITE;
/*!40000 ALTER TABLE `seminare` DISABLE KEYS */;
INSERT INTO `seminare` VALUES ('7cb72dab1bf896a0b55c6aa7a70a3a86','','ec2e364b28357106c0f8c282733dbe56','Test Studiengruppe','',99,'Studiengruppen sind eine einfache M?glichkeit, mit KommilitonInnen, KollegInnen und anderen zusammenzuarbeiten.','','',1,1,1254348000,-1,'','','','','',1268739824,1343924088,'',0,0,0,'',0,1,0,395,NULL,0,NULL,0,0),('a07535cf2f8a72df33c12ddfa4b53dde','12345','2560f7c7674942a7dce8eeb238e15d93','Test Lehrveranstaltung','eine normale Lehrveranstaltung',1,'','','',1,1,1380578400,0,'','f?r alle Studierenden','abgeschlossenes Grundstudium','Referate in Gruppenarbeit','Klausur',1343924407,1383667270,'4',0,0,0,'',0,1,0,20911,NULL,0,NULL,0,0);
/*!40000 ALTER TABLE `seminare` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_data`
--

DROP TABLE IF EXISTS `session_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_data` (
  `sid` varchar(32) NOT NULL DEFAULT '',
  `val` mediumtext NOT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sid`),
  KEY `changed` (`changed`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_data`
--

LOCK TABLES `session_data` WRITE;
/*!40000 ALTER TABLE `session_data` DISABLE KEYS */;
INSERT INTO `session_data` VALUES ('0357bcb93ff27df17219ba76dad16d6d','auth|O:20:\"Seminar_Default_Auth\":2:{s:4:\"auth\";a:2:{s:3:\"uid\";s:6:\"nobody\";s:4:\"perm\";s:0:\"\";}s:9:\"classname\";s:20:\"Seminar_Default_Auth\";}','2014-12-02 11:28:59');
/*!40000 ALTER TABLE `session_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siteinfo_details`
--

DROP TABLE IF EXISTS `siteinfo_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siteinfo_details` (
  `detail_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `rubric_id` smallint(5) unsigned NOT NULL,
  `position` tinyint(3) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`detail_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siteinfo_details`
--

LOCK TABLES `siteinfo_details` WRITE;
/*!40000 ALTER TABLE `siteinfo_details` DISABLE KEYS */;
INSERT INTO `siteinfo_details` VALUES (1,1,NULL,'[lang=de]Ansprechpartner[/lang][lang=en]Contact[/lang]','[style=float: right]\n[img]http://www.studip.de/images/studipanim.gif\n**Version:** (:version:)\n[/style]\n[lang=de]F?r diese Stud.IP-Installation ((:uniname:)) sind folgende Administratoren zust?ndig:[/lang]\n[lang=en]The following administrators are responsible for this Stud.IP installation ((:uniname:)):[/lang]\n(:rootlist:)\n[lang=de]allgemeine Anfragen wie Passwort-Anforderungen u.a. richten Sie bitte an:[/lang]\n[lang=en]General queries e.g., password queries, please contact:[/lang]\n(:unicontact:)\n[lang=de]Folgende Einrichtungen sind beteiligt:\n(Genannt werden die jeweiligen Administratoren der Einrichtungen f?r entsprechende Anfragen)[/lang]\n[lang=en]The following institutes participate:\n(Named are the institutes administrators responsible for the corresponding query areas)[/lang]\n(:adminlist:)'),(2,1,NULL,'[lang=de]Entwickler[/lang][lang=en]Developer[/lang]','[style=float: right]\r\n[img]http://www.studip.de/images/studipanim.gif\r\n**Version:** (:version:)\r\n[/style]\r\n[lang=de]Stud.IP ist ein Open Source Projekt zur Unterst?tzung von Pr?senzlehre an Universit?ten, Hochschulen und anderen Bildungseinrichtungen. Das System entstand am Zentrum f?r interdisziplin?re Medienwissenschaft (ZiM) der Georg-August-Universit?t G?ttingen unter Mitwirkung der Suchi & Berg GmbH (data-quest) , G?ttingen. Heute erfolgt die Weiterentwicklung von Stud.IP verteilt an vielen Standorten (G?ttingen, Osnabr?ck, Oldenburg, Bremen, Hannover, Jena und weiteren). Die Koordination der Entwicklung erfolgt durch die Stud.IP-CoreGroup.\r\nStud.IP steht unter der GNU General Public License, Version 2.\r\n\r\nWeitere Informationen finden sie auf ** [www.studip.de]http://www.studip.de **,**  [develop.studip.de]http://develop.studip.de ** und ** [blog.studip.de]http://blog.studip.de **.[/lang]\r\n\r\n[lang=en]Stud.IP is an opensource project for supporting attendance courses offered by universities, institutions of higher education and other educational institutions. The system was established at the Zentrum f?r interdisziplin?re Medienwissenschaft (ZiM) in the Georg-August-Universit?t G?ttingen in cooperation with Suchi & Berg GmbH (data-quest) , G?ttingen. At the present further developing takes place at various locations (among others G?ttingen, Osnabr?ck, Oldenburg, Bremen, Hannover, Jena) under coordination through the Stud.IP-CoreGroup.\r\n\r\nStud.IP is covered by the GNU General Public Licence, version 2.\r\n\r\nFurther information can be found under ** [www.studip.de]http://www.studip.de **,**  [develop.studip.de]http://develop.studip.de ** and ** [blog.studip.de]http://blog.studip.de **.[/lang]\r\n\r\n(:coregroup:)\r\n[lang=de]Sie erreichen uns auch ?ber folgende **Mailinglisten**:\r\n\r\n**Nutzer-Anfragen**, E-Mail: studip-users@lists.sourceforge.net : Fragen, Anregungen und Vorschl?ge an die Entwickler - bitte __keine__ Passwort Anfragen!\r\n**News-Mailingsliste**, E-Mail: studip-news@lists.sourceforge.net : News rund um Stud.IP (Eintragung notwendig)\r\n\r\nWir laden alle Entwickler, Betreiber und Nutzer von Stud.IP ein, sich auf dem Developer-Server http://develop.studip.de an den Diskussionen rund um die Weiterentwicklung und Nutzung der Plattform zu beteiligen.[/lang]\r\n[lang=en]You can contact us via the following **mailing lists**:\r\n\r\n**User enquiries**, E-Mail: studip-users@lists.sourceforge.net : Questions, suggestions and recommendations to the developers - __please no password queries__!\r\n\r\n**News mailing list**, E-Mail: studip-news@lists.sourceforge.net : News about Stud.IP (registration necessary)\r\n\r\nWe invite all developers, administrators and users of Stud.IP to join the discussions on further developing and using the platform available at the developer server http://develop.studip.de[/lang]'),(3,2,NULL,'[lang=de]Technik[/lang][lang=en]Technology[/lang]','[style=float: right]\n[img]http://www.studip.de/images/studipanim.gif\n**Version:** (:version:)\n[/style]\n[lang=de]Stud IP ist ein Open-Source Projekt und steht unter der GNU General Public License. S?mtliche zum Betrieb notwendigen Dateien k?nnen unter http://sourceforge.net/projects/studip/ heruntergeladen werden.\nDie technische Grundlage bietet ein LINUX-System mit Apache Webserver sowie eine MySQL Datenbank, die ?ber PHP gesteuert wird.\nIm System findet ein 6-stufiges Rechtesystem Verwendung, das individuell auf verschiedenen Ebenen wirkt - etwa in Veranstaltungen, Einrichtungen, Fakult?ten oder systemweit.\nSeminare oder Arbeitsgruppen k?nnen mit Passw?rtern gesch?tzt werden - die Verschl?sselung erfolgt mit einem MD5 one-way-hash.\nDas System ist zu 100% ?ber das Internet administrierbar, es sind keine zus?tzlichen Werkzeuge n?tig. Ein Webbrowser der 5. Generation wird empfohlen.\nDas System wird st?ndig weiterentwickelt und an die W?nsche unserer Nutzer angepasst - [sagen Sie uns Ihre Meinung!]studip-users@lists.sourceforge.net[/lang]\n[lang=en]Stud.IP is an Open Source Project and is covered by the Gnu General Public License (GPL). All files necessary for operation can be downloaded from http://sourceforge.net/projects/studip/ .\nThe technical basis can be provided by a LINUX system with Apache Webserver and a MySQL database, which is then controlled by PHP.\nThe system features a authorisation system with six ranks, that affects individually different levels - in courses, institutes,faculties or system wide.\nSeminars or work groups can be secured with passwords - the encryption of which uses a MD5 one-way-hash.\nThe system is capable of being administrated 100% over the internet - no additional tools are necessary. A 5th generation web browser is recommended.\nThe system is continually being developed and customised to the wishes of our users - [Tell us your opinion!]studip-users@lists.sourceforge.net[/lang]'),(4,2,NULL,'[lang=de]Statistik[/lang][lang=en]Statistics[/lang]','[lang=de]!!Top-Listen aller Veranstaltungen[/lang][lang=en]!!Top list of all courses[/lang]\n[style=float: right]\n[lang=de]!!Statistik[/lang][lang=en]!!statistics[/lang]\n(:indicator seminar_all:)\n(:indicator seminar_archived:)\n(:indicator institute_firstlevel_all:)\n(:indicator institute_secondlevel_all:)\n(:indicator user_admin:)\n(:indicator user_dozent:)\n(:indicator user_tutor:)\n(:indicator user_autor:)\n(:indicator posting:)\n(:indicator document:)\n(:indicator link:)\n(:indicator litlist:)\n(:indicator termin:)\n(:indicator news:)\n(:indicator guestbook:)\n(:indicator vote:)\n(:indicator test:)\n(:indicator evaluation:)\n(:indicator wiki_pages:)\n(:indicator lernmodul:)\n(:indicator resource:)\n[/style]\n(:toplist mostparticipants:)\n(:toplist recentlycreated:)\n(:toplist mostdocuments:)\n(:toplist mostpostings:)\n(:toplist mostvisitedhomepages:)'),(5,2,NULL,'History','(:history:)'),(6,2,NULL,'Stud.IP-Blog','[lang=de]Das Blog der Stud.IP-Entwickler finden Sie auf:[/lang]\n[lang=en]The Stud.IP-Developer-Blog can be found under:[/lang]\nhttp://blog.studip.de');
/*!40000 ALTER TABLE `siteinfo_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siteinfo_rubrics`
--

DROP TABLE IF EXISTS `siteinfo_rubrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siteinfo_rubrics` (
  `rubric_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `position` tinyint(3) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`rubric_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siteinfo_rubrics`
--

LOCK TABLES `siteinfo_rubrics` WRITE;
/*!40000 ALTER TABLE `siteinfo_rubrics` DISABLE KEYS */;
INSERT INTO `siteinfo_rubrics` VALUES (1,NULL,'[lang=de]Kontakt[/lang][lang=en]Contact[/lang]'),(2,NULL,'[lang=de]?ber Stud.IP[/lang][lang=en]About Stud.IP[/lang]');
/*!40000 ALTER TABLE `siteinfo_rubrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smiley`
--

DROP TABLE IF EXISTS `smiley`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smiley` (
  `smiley_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `smiley_name` varchar(50) NOT NULL DEFAULT '',
  `smiley_width` int(11) NOT NULL DEFAULT '0',
  `smiley_height` int(11) NOT NULL DEFAULT '0',
  `short_name` varchar(50) NOT NULL DEFAULT '',
  `smiley_counter` int(11) unsigned NOT NULL DEFAULT '0',
  `short_counter` int(11) unsigned NOT NULL DEFAULT '0',
  `fav_counter` int(11) unsigned NOT NULL DEFAULT '0',
  `mkdate` int(10) unsigned DEFAULT NULL,
  `chdate` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`smiley_id`),
  UNIQUE KEY `name` (`smiley_name`),
  KEY `short` (`short_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smiley`
--

LOCK TABLES `smiley` WRITE;
/*!40000 ALTER TABLE `smiley` DISABLE KEYS */;
/*!40000 ALTER TABLE `smiley` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statusgruppe_user`
--

DROP TABLE IF EXISTS `statusgruppe_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statusgruppe_user` (
  `statusgruppe_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `inherit` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`statusgruppe_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statusgruppe_user`
--

LOCK TABLES `statusgruppe_user` WRITE;
/*!40000 ALTER TABLE `statusgruppe_user` DISABLE KEYS */;
INSERT INTO `statusgruppe_user` VALUES ('efb56e092f33cb78a8766676042dc1c5','7e81ec247c151c02ffd479511e24cc03',1,1,1),('5d40b1fc0434e6589d7341a3ee742baf','205f3efb7997a0fc9755da2b535038da',1,1,1),('f4319d9909e9f7cb4692c16771887f22','205f3efb7997a0fc9755da2b535038da',1,1,1),('f4319d9909e9f7cb4692c16771887f22','7e81ec247c151c02ffd479511e24cc03',2,1,1),('2f597139a049a768dbf8345a0a0af3de','e7a0a84b161f3e8c09b4a0a2e8a58147',1,1,1);
/*!40000 ALTER TABLE `statusgruppe_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statusgruppen`
--

DROP TABLE IF EXISTS `statusgruppen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statusgruppen` (
  `statusgruppe_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `position` int(20) NOT NULL DEFAULT '0',
  `size` int(20) NOT NULL DEFAULT '0',
  `selfassign` tinyint(4) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `calendar_group` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `name_w` varchar(255) DEFAULT NULL,
  `name_m` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`statusgruppe_id`),
  KEY `range_id` (`range_id`),
  KEY `position` (`position`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statusgruppen`
--

LOCK TABLES `statusgruppen` WRITE;
/*!40000 ALTER TABLE `statusgruppen` DISABLE KEYS */;
INSERT INTO `statusgruppen` VALUES ('86498c641ccf4f4d4e02f4961ccc3829','Lehrbeauftragte','2560f7c7674942a7dce8eeb238e15d93',3,0,0,1156516698,1156516698,0,NULL,NULL),('600403561c21a50ae8b4d41655bd2191','HochschullehrerIn','2560f7c7674942a7dce8eeb238e15d93',4,0,0,1156516698,1156516698,0,NULL,NULL),('efb56e092f33cb78a8766676042dc1c5','wiss. MitarbeiterIn','2560f7c7674942a7dce8eeb238e15d93',2,0,0,1156516698,1156516698,0,NULL,NULL),('5d40b1fc0434e6589d7341a3ee742baf','DirektorIn','2560f7c7674942a7dce8eeb238e15d93',1,0,0,1156516698,1156516698,0,NULL,NULL),('2f597139a049a768dbf8345a0a0af3de','Studierende','a07535cf2f8a72df33c12ddfa4b53dde',1,0,0,1343924562,1343924562,0,NULL,NULL),('f4319d9909e9f7cb4692c16771887f22','Lehrende','a07535cf2f8a72df33c12ddfa4b53dde',0,0,0,1343924551,1343924551,0,NULL,NULL);
/*!40000 ALTER TABLE `statusgruppen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studiengaenge`
--

DROP TABLE IF EXISTS `studiengaenge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studiengaenge` (
  `studiengang_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT NULL,
  `beschreibung` text,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`studiengang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studiengaenge`
--

LOCK TABLES `studiengaenge` WRITE;
/*!40000 ALTER TABLE `studiengaenge` DISABLE KEYS */;
INSERT INTO `studiengaenge` VALUES ('f981c9b42ca72788a09da4a45794a737','Informatik','',1311416397,1311416397),('6b9ac09535885ca55e29dd011e377c0a','Geschichte','',1311416418,1311416418);
/*!40000 ALTER TABLE `studiengaenge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studygroup_invitations`
--

DROP TABLE IF EXISTS `studygroup_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studygroup_invitations` (
  `sem_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `mkdate` int(20) NOT NULL,
  PRIMARY KEY (`sem_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studygroup_invitations`
--

LOCK TABLES `studygroup_invitations` WRITE;
/*!40000 ALTER TABLE `studygroup_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `studygroup_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `termin_related_groups`
--

DROP TABLE IF EXISTS `termin_related_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `termin_related_groups` (
  `termin_id` varchar(32) NOT NULL,
  `statusgruppe_id` varchar(45) NOT NULL,
  UNIQUE KEY `unique` (`termin_id`,`statusgruppe_id`),
  KEY `termin_id` (`termin_id`),
  KEY `statusgruppe_id` (`statusgruppe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `termin_related_groups`
--

LOCK TABLES `termin_related_groups` WRITE;
/*!40000 ALTER TABLE `termin_related_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `termin_related_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `termin_related_persons`
--

DROP TABLE IF EXISTS `termin_related_persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `termin_related_persons` (
  `range_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  PRIMARY KEY (`range_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `termin_related_persons`
--

LOCK TABLES `termin_related_persons` WRITE;
/*!40000 ALTER TABLE `termin_related_persons` DISABLE KEYS */;
/*!40000 ALTER TABLE `termin_related_persons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `termine`
--

DROP TABLE IF EXISTS `termine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `termine` (
  `termin_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `autor_id` varchar(32) NOT NULL DEFAULT '',
  `content` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `date` int(20) NOT NULL DEFAULT '0',
  `end_time` int(20) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `date_typ` tinyint(4) NOT NULL DEFAULT '0',
  `topic_id` varchar(32) DEFAULT NULL,
  `raum` varchar(255) DEFAULT NULL,
  `metadate_id` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`termin_id`),
  KEY `metadate_id` (`metadate_id`,`date`),
  KEY `range_id` (`range_id`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `termine`
--

LOCK TABLES `termine` WRITE;
/*!40000 ALTER TABLE `termine` DISABLE KEYS */;
INSERT INTO `termine` VALUES ('856f55695ecc263d78a7386cdc63e398','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1391673600,1391688000,1383667270,1383667270,1,NULL,'','0309c794406b96bb01662e9e02593517'),('bc8dfea3b0cab70d316513f17de0d543','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1390464000,1390478400,1383667270,1383667270,1,NULL,'','0309c794406b96bb01662e9e02593517'),('6e3d7610078f945d4f1854bf8e91f62c','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1389254400,1389268800,1383667270,1383667270,1,NULL,'','0309c794406b96bb01662e9e02593517'),('1aeda7c8188b1cab7c138bffa8950a3b','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1386835200,1386849600,1383667270,1383667270,1,NULL,'','0309c794406b96bb01662e9e02593517'),('48ee2f2c69495dafeb62b061e1cd3ff5','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1383552000,1383562800,1383667270,1383667270,1,NULL,'','d124b42deb48ac58adbd620b7ae6cc21'),('336336908740c15556f468a214a613eb','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1384761600,1384772400,1383667270,1383667270,1,NULL,'','d124b42deb48ac58adbd620b7ae6cc21'),('16c8c7b1cecbd79dd596f9f4354ad7af','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1385971200,1385982000,1383667270,1383667270,1,NULL,'','d124b42deb48ac58adbd620b7ae6cc21'),('3bbccfb5d151e09d6c55c87e60f44bff','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1387180800,1387191600,1383667270,1383667270,1,NULL,'','d124b42deb48ac58adbd620b7ae6cc21'),('8c9bbc5d9978123a1dbfaf3dc4911970','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1389600000,1389610800,1383667270,1383667270,1,NULL,'','d124b42deb48ac58adbd620b7ae6cc21'),('7bd71630e82b4577e6b7042931aa4177','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1390809600,1390820400,1383667270,1383667270,1,NULL,'','d124b42deb48ac58adbd620b7ae6cc21'),('b5656d6a7d9945808bb671aa57e59d8e','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1383206400,1383220800,1383667270,1383667270,1,NULL,'','0309c794406b96bb01662e9e02593517'),('e1f1ba3ab74189c899a3db312066b619','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1384416000,1384430400,1383667270,1383667270,1,NULL,'','0309c794406b96bb01662e9e02593517'),('0d2ebc65277500a28e9bec8eb2424ac1','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1385625600,1385640000,1383667270,1383667270,1,NULL,'','0309c794406b96bb01662e9e02593517'),('be6aff5747fae3274c31b63a7eb80913','a07535cf2f8a72df33c12ddfa4b53dde','76ed43ef286fb55cf9e41beadb484a9f','0',NULL,1382338800,1382349600,1383667270,1383667270,1,NULL,'','d124b42deb48ac58adbd620b7ae6cc21');
/*!40000 ALTER TABLE `termine` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `themen`
--

DROP TABLE IF EXISTS `themen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `themen` (
  `issue_id` varchar(32) NOT NULL DEFAULT '',
  `seminar_id` varchar(32) NOT NULL DEFAULT '',
  `author_id` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `priority` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mkdate` int(10) unsigned NOT NULL DEFAULT '0',
  `chdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`issue_id`),
  KEY `seminar_id` (`seminar_id`,`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `themen`
--

LOCK TABLES `themen` WRITE;
/*!40000 ALTER TABLE `themen` DISABLE KEYS */;
/*!40000 ALTER TABLE `themen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `themen_termine`
--

DROP TABLE IF EXISTS `themen_termine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `themen_termine` (
  `issue_id` varchar(32) NOT NULL DEFAULT '',
  `termin_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`issue_id`,`termin_id`),
  KEY `termin_id` (`termin_id`,`issue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `themen_termine`
--

LOCK TABLES `themen_termine` WRITE;
/*!40000 ALTER TABLE `themen_termine` DISABLE KEYS */;
/*!40000 ALTER TABLE `themen_termine` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timedadmissions`
--

DROP TABLE IF EXISTS `timedadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timedadmissions` (
  `rule_id` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`),
  KEY `start_end` (`start_time`,`end_time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timedadmissions`
--

LOCK TABLES `timedadmissions` WRITE;
/*!40000 ALTER TABLE `timedadmissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `timedadmissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_config`
--

DROP TABLE IF EXISTS `user_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_config` (
  `userconfig_id` varchar(32) NOT NULL DEFAULT '',
  `parent_id` varchar(32) DEFAULT NULL,
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `field` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  PRIMARY KEY (`userconfig_id`),
  KEY `user_id` (`user_id`,`field`,`value`(5))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_config`
--

LOCK TABLES `user_config` WRITE;
/*!40000 ALTER TABLE `user_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_factorlist`
--

DROP TABLE IF EXISTS `user_factorlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_factorlist` (
  `list_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `mkdate` int(11) DEFAULT NULL,
  PRIMARY KEY (`list_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_factorlist`
--

LOCK TABLES `user_factorlist` WRITE;
/*!40000 ALTER TABLE `user_factorlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_factorlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_info` (
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `hobby` varchar(255) NOT NULL DEFAULT '',
  `lebenslauf` text,
  `publi` text NOT NULL,
  `schwerp` text NOT NULL,
  `Home` varchar(200) NOT NULL DEFAULT '',
  `privatnr` varchar(32) NOT NULL DEFAULT '',
  `privatcell` varchar(32) NOT NULL DEFAULT '',
  `privadr` varchar(64) NOT NULL DEFAULT '',
  `score` int(11) unsigned NOT NULL DEFAULT '0',
  `geschlecht` tinyint(4) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `title_front` varchar(64) NOT NULL DEFAULT '',
  `title_rear` varchar(64) NOT NULL DEFAULT '',
  `preferred_language` varchar(20) DEFAULT NULL,
  `smsforward_copy` tinyint(1) NOT NULL DEFAULT '1',
  `smsforward_rec` varchar(32) NOT NULL DEFAULT '',
  `email_forward` tinyint(4) NOT NULL DEFAULT '0',
  `smiley_favorite` varchar(255) NOT NULL DEFAULT '',
  `motto` varchar(255) NOT NULL DEFAULT '',
  `lock_rule` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`),
  KEY `score` (`score`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_info`
--

LOCK TABLES `user_info` WRITE;
/*!40000 ALTER TABLE `user_info` DISABLE KEYS */;
INSERT INTO `user_info` VALUES ('76ed43ef286fb55cf9e41beadb484a9f','',NULL,'','','','','','',0,0,0,0,'','',NULL,1,'',0,'','',''),('e7a0a84b161f3e8c09b4a0a2e8a58147','',NULL,'','','','','','',0,0,0,0,'','',NULL,1,'',0,'','',''),('205f3efb7997a0fc9755da2b535038da','',NULL,'','','','','','',0,0,0,0,'','',NULL,1,'',0,'','',''),('6235c46eb9e962866ebdceece739ace5','',NULL,'','','','','','',0,0,0,0,'','',NULL,1,'',0,'','',''),('7e81ec247c151c02ffd479511e24cc03','',NULL,'','','','','','',0,0,0,0,'','',NULL,1,'',0,'','','');
/*!40000 ALTER TABLE `user_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_inst`
--

DROP TABLE IF EXISTS `user_inst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_inst` (
  `user_id` varchar(32) NOT NULL DEFAULT '0',
  `Institut_id` varchar(32) NOT NULL DEFAULT '0',
  `inst_perms` enum('user','autor','tutor','dozent','admin') NOT NULL DEFAULT 'user',
  `sprechzeiten` varchar(200) NOT NULL DEFAULT '',
  `raum` varchar(200) NOT NULL DEFAULT '',
  `Telefon` varchar(32) NOT NULL DEFAULT '',
  `Fax` varchar(32) NOT NULL DEFAULT '',
  `externdefault` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `visible` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`Institut_id`,`user_id`),
  KEY `inst_perms` (`inst_perms`,`Institut_id`),
  KEY `user_id` (`user_id`,`inst_perms`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_inst`
--

LOCK TABLES `user_inst` WRITE;
/*!40000 ALTER TABLE `user_inst` DISABLE KEYS */;
INSERT INTO `user_inst` VALUES ('205f3efb7997a0fc9755da2b535038da','2560f7c7674942a7dce8eeb238e15d93','dozent','','','','',0,0,1),('6235c46eb9e962866ebdceece739ace5','2560f7c7674942a7dce8eeb238e15d93','admin','','','','',0,0,1),('7e81ec247c151c02ffd479511e24cc03','2560f7c7674942a7dce8eeb238e15d93','tutor','','','','',0,0,1),('e7a0a84b161f3e8c09b4a0a2e8a58147','2560f7c7674942a7dce8eeb238e15d93','user','','','','',1,0,1);
/*!40000 ALTER TABLE `user_inst` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_online`
--

DROP TABLE IF EXISTS `user_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_online` (
  `user_id` char(32) NOT NULL,
  `last_lifesign` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `last_lifesign` (`last_lifesign`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_online`
--

LOCK TABLES `user_online` WRITE;
/*!40000 ALTER TABLE `user_online` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_online` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_studiengang`
--

DROP TABLE IF EXISTS `user_studiengang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_studiengang` (
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `studiengang_id` varchar(32) NOT NULL DEFAULT '',
  `semester` tinyint(2) DEFAULT '0',
  `abschluss_id` char(32) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`studiengang_id`,`abschluss_id`),
  KEY `studiengang_id` (`studiengang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_studiengang`
--

LOCK TABLES `user_studiengang` WRITE;
/*!40000 ALTER TABLE `user_studiengang` DISABLE KEYS */;
INSERT INTO `user_studiengang` VALUES ('e7a0a84b161f3e8c09b4a0a2e8a58147','6b9ac09535885ca55e29dd011e377c0a',2,'228234544820cdf75db55b42d1ea3ecc'),('7e81ec247c151c02ffd479511e24cc03','f981c9b42ca72788a09da4a45794a737',1,'228234544820cdf75db55b42d1ea3ecc');
/*!40000 ALTER TABLE `user_studiengang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_token`
--

DROP TABLE IF EXISTS `user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_token` (
  `user_id` varchar(32) NOT NULL,
  `token` varchar(32) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`token`,`expiration`),
  KEY `index_expiration` (`expiration`),
  KEY `index_token` (`token`),
  KEY `index_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_token`
--

LOCK TABLES `user_token` WRITE;
/*!40000 ALTER TABLE `user_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_userdomains`
--

DROP TABLE IF EXISTS `user_userdomains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_userdomains` (
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `userdomain_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`,`userdomain_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_userdomains`
--

LOCK TABLES `user_userdomains` WRITE;
/*!40000 ALTER TABLE `user_userdomains` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_userdomains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_visibility`
--

DROP TABLE IF EXISTS `user_visibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_visibility` (
  `user_id` varchar(32) NOT NULL,
  `online` tinyint(1) NOT NULL DEFAULT '1',
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `email` tinyint(1) NOT NULL DEFAULT '1',
  `homepage` text NOT NULL,
  `default_homepage_visibility` int(11) NOT NULL DEFAULT '0',
  `mkdate` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_visibility`
--

LOCK TABLES `user_visibility` WRITE;
/*!40000 ALTER TABLE `user_visibility` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_visibility` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_visibility_settings`
--

DROP TABLE IF EXISTS `user_visibility_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_visibility_settings` (
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `visibilityid` int(32) NOT NULL AUTO_INCREMENT,
  `parent_id` int(32) NOT NULL,
  `category` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `state` int(2) DEFAULT NULL,
  `plugin` int(11) DEFAULT NULL,
  `identifier` varchar(64) NOT NULL,
  PRIMARY KEY (`visibilityid`),
  KEY `parent_id` (`parent_id`),
  KEY `identifier` (`identifier`),
  KEY `userid` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_visibility_settings`
--

LOCK TABLES `user_visibility_settings` WRITE;
/*!40000 ALTER TABLE `user_visibility_settings` DISABLE KEYS */;
INSERT INTO `user_visibility_settings` VALUES ('76ed43ef286fb55cf9e41beadb484a9f',1,0,'0','Allgemeine Daten',4,NULL,'commondata'),('76ed43ef286fb55cf9e41beadb484a9f',2,0,'0','Private Daten',4,NULL,'privatedata'),('76ed43ef286fb55cf9e41beadb484a9f',3,0,'0','Studien-/Einrichtungsdaten',4,NULL,'studdata'),('76ed43ef286fb55cf9e41beadb484a9f',4,0,'0','Zus?tzliche Datenfelder',4,NULL,'additionaldata'),('76ed43ef286fb55cf9e41beadb484a9f',5,0,'0','Eigene Kategorien',4,NULL,'owncategory'),('76ed43ef286fb55cf9e41beadb484a9f',7,1,'1','Ank?ndigungen',4,NULL,'news'),('6235c46eb9e962866ebdceece739ace5',8,0,'0','Allgemeine Daten',4,NULL,'commondata'),('6235c46eb9e962866ebdceece739ace5',9,0,'0','Private Daten',4,NULL,'privatedata'),('6235c46eb9e962866ebdceece739ace5',10,0,'0','Studien-/Einrichtungsdaten',4,NULL,'studdata'),('6235c46eb9e962866ebdceece739ace5',11,0,'0','Zus?tzliche Datenfelder',4,NULL,'additionaldata'),('6235c46eb9e962866ebdceece739ace5',12,0,'0','Eigene Kategorien',4,NULL,'owncategory'),('e7a0a84b161f3e8c09b4a0a2e8a58147',14,0,'0','Allgemeine Daten',4,NULL,'commondata'),('e7a0a84b161f3e8c09b4a0a2e8a58147',15,0,'0','Private Daten',4,NULL,'privatedata'),('e7a0a84b161f3e8c09b4a0a2e8a58147',16,0,'0','Studien-/Einrichtungsdaten',4,NULL,'studdata'),('e7a0a84b161f3e8c09b4a0a2e8a58147',17,0,'0','Zus?tzliche Datenfelder',4,NULL,'additionaldata'),('e7a0a84b161f3e8c09b4a0a2e8a58147',18,0,'0','Eigene Kategorien',4,NULL,'owncategory'),('e7a0a84b161f3e8c09b4a0a2e8a58147',20,16,'1','Wo ich studiere',4,NULL,'studying'),('e7a0a84b161f3e8c09b4a0a2e8a58147',21,17,'1','Matrikelnummer',4,NULL,'ce73a10d07b3bb13c0132d363549efda'),('205f3efb7997a0fc9755da2b535038da',22,0,'0','Allgemeine Daten',4,NULL,'commondata'),('205f3efb7997a0fc9755da2b535038da',23,0,'0','Private Daten',4,NULL,'privatedata'),('205f3efb7997a0fc9755da2b535038da',24,0,'0','Studien-/Einrichtungsdaten',4,NULL,'studdata'),('205f3efb7997a0fc9755da2b535038da',25,0,'0','Zus?tzliche Datenfelder',4,NULL,'additionaldata'),('205f3efb7997a0fc9755da2b535038da',26,0,'0','Eigene Kategorien',4,NULL,'owncategory'),('7e81ec247c151c02ffd479511e24cc03',28,0,'0','Allgemeine Daten',4,NULL,'commondata'),('7e81ec247c151c02ffd479511e24cc03',29,0,'0','Private Daten',4,NULL,'privatedata'),('7e81ec247c151c02ffd479511e24cc03',30,0,'0','Studien-/Einrichtungsdaten',4,NULL,'studdata'),('7e81ec247c151c02ffd479511e24cc03',31,0,'0','Zus?tzliche Datenfelder',4,NULL,'additionaldata'),('7e81ec247c151c02ffd479511e24cc03',32,0,'0','Eigene Kategorien',4,NULL,'owncategory'),('7e81ec247c151c02ffd479511e24cc03',34,31,'1','Matrikelnummer',4,NULL,'ce73a10d07b3bb13c0132d363549efda');
/*!40000 ALTER TABLE `user_visibility_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userdomains`
--

DROP TABLE IF EXISTS `userdomains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userdomains` (
  `userdomain_id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`userdomain_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userdomains`
--

LOCK TABLES `userdomains` WRITE;
/*!40000 ALTER TABLE `userdomains` DISABLE KEYS */;
/*!40000 ALTER TABLE `userdomains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userfilter`
--

DROP TABLE IF EXISTS `userfilter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userfilter` (
  `filter_id` varchar(32) NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userfilter`
--

LOCK TABLES `userfilter` WRITE;
/*!40000 ALTER TABLE `userfilter` DISABLE KEYS */;
/*!40000 ALTER TABLE `userfilter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userfilter_fields`
--

DROP TABLE IF EXISTS `userfilter_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userfilter_fields` (
  `field_id` varchar(32) NOT NULL,
  `filter_id` varchar(32) NOT NULL,
  `type` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `compare_op` varchar(255) NOT NULL,
  `mkdate` int(11) NOT NULL DEFAULT '0',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userfilter_fields`
--

LOCK TABLES `userfilter_fields` WRITE;
/*!40000 ALTER TABLE `userfilter_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `userfilter_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userlimits`
--

DROP TABLE IF EXISTS `userlimits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userlimits` (
  `rule_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `maxnumber` int(11) DEFAULT NULL,
  `mkdate` int(11) DEFAULT NULL,
  `chdate` int(11) DEFAULT NULL,
  PRIMARY KEY (`rule_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userlimits`
--

LOCK TABLES `userlimits` WRITE;
/*!40000 ALTER TABLE `userlimits` DISABLE KEYS */;
/*!40000 ALTER TABLE `userlimits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote`
--

DROP TABLE IF EXISTS `vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote` (
  `vote_id` varchar(32) NOT NULL DEFAULT '',
  `author_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `type` enum('vote','test') NOT NULL DEFAULT 'vote',
  `title` varchar(100) NOT NULL DEFAULT '',
  `question` text NOT NULL,
  `state` enum('new','active','stopvis','stopinvis') NOT NULL DEFAULT 'new',
  `startdate` int(20) DEFAULT NULL,
  `stopdate` int(20) DEFAULT NULL,
  `timespan` int(20) DEFAULT NULL,
  `mkdate` int(20) NOT NULL DEFAULT '0',
  `chdate` int(20) NOT NULL DEFAULT '0',
  `resultvisibility` enum('ever','delivery','end','never') NOT NULL DEFAULT 'ever',
  `multiplechoice` tinyint(1) NOT NULL DEFAULT '0',
  `anonymous` tinyint(1) NOT NULL DEFAULT '1',
  `changeable` tinyint(1) NOT NULL DEFAULT '0',
  `co_visibility` tinyint(1) DEFAULT NULL,
  `namesvisibility` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vote_id`),
  KEY `range_id` (`range_id`),
  KEY `state` (`state`),
  KEY `startdate` (`startdate`),
  KEY `stopdate` (`stopdate`),
  KEY `resultvisibility` (`resultvisibility`),
  KEY `chdate` (`chdate`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote`
--

LOCK TABLES `vote` WRITE;
/*!40000 ALTER TABLE `vote` DISABLE KEYS */;
INSERT INTO `vote` VALUES ('b5329b23b7f865c62028e226715e1914','76ed43ef286fb55cf9e41beadb484a9f','studip','vote','Nutzen Sie bereits Stud.IP?','Haben Sie Stud.IP bereits im Einsatz oder planen Sie, es einzusetzen?','active',1383667417,NULL,NULL,1142525062,1383667418,'delivery',1,0,1,NULL,0);
/*!40000 ALTER TABLE `vote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote_user`
--

DROP TABLE IF EXISTS `vote_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote_user` (
  `vote_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `votedate` int(20) DEFAULT NULL,
  PRIMARY KEY (`vote_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote_user`
--

LOCK TABLES `vote_user` WRITE;
/*!40000 ALTER TABLE `vote_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voteanswers`
--

DROP TABLE IF EXISTS `voteanswers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voteanswers` (
  `answer_id` varchar(32) NOT NULL DEFAULT '',
  `vote_id` varchar(32) NOT NULL DEFAULT '',
  `answer` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `counter` int(11) NOT NULL DEFAULT '0',
  `correct` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `vote_id` (`vote_id`),
  KEY `position` (`position`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voteanswers`
--

LOCK TABLES `voteanswers` WRITE;
/*!40000 ALTER TABLE `voteanswers` DISABLE KEYS */;
INSERT INTO `voteanswers` VALUES ('42a47ba18ad12df72fca2898d5e27132','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 2.5',23,0,0),('2f05e4b581d9941a4262ed4b65914b9a','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 2.4',22,0,0),('5d664c7914aaf2b5fbc66ab871a0e27b','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 2.3',21,0,0),('56991b7ad13aa8f5315e9bc412c6a199','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 2.2',20,0,0),('3c065ec2b3037c39991cc5d99eca185c','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 2.1',19,0,0),('2ea4169a90dbcc56be1610f75d86d460','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 2.0',18,0,0),('ddcf45e577e20133fcc5bf65aef2a075','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.11',17,0,0),('71b97633448009af49c43b5a56de4c7f','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.10',16,0,0),('ef983352938c5714f23bc47257dd2489','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.9',15,0,0),('5fb01b6623c848c3bf33cce70675b91a','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.8',14,0,0),('03bce9c940fc76f5eb90ab7b151cf34d','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.7',13,0,0),('816a463bef33edcdf1ed82e94166f1ad','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.6',12,0,0),('dddf684fbcac58f7ffd0804b7095c71b','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.5',11,0,0),('b1083fbf35c8782ad35c1a0c9364f2c2','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.4',10,0,0),('f31fab58d15388245396dc59de346e90','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.3',9,0,0),('6f51e5d957aa6e7a3e8494e0e56c43aa','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.2',8,0,0),('8502e4b4600a12b2d5d43aefe2930be4','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.1.5',7,0,0),('8112e4b4600a12b2d5d43aecf2930be4','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.1.0',6,0,0),('8342e4b4600a12b2d5d43aecf2930be4','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 1.0',5,0,0),('dc1b49bf35e9cfbfcece807b21cec0ef','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 0.9.5',4,0,0),('ddfd889094a6cea75703728ee7b48806','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 0.9.0',3,0,0),('58281eda805a0fe5741c74a2c612cb05','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 0.8.15',2,0,0),('c8ade4c7f3bbe027f6c19016dd3e001c','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 0.8.0',1,0,0),('112f7c8f52b0a2a6eff9cddf93b419c7','b5329b23b7f865c62028e226715e1914','Ich nutze die Version 0.7.5',0,0,0),('f963fccda920be268aa116ae870a8984','b5329b23b7f865c62028e226715e1914','Ich plane, es demn?chst einzusetzen',24,0,0),('52390fa347c0f58b80a6f1d42a1c186c','b5329b23b7f865c62028e226715e1914','Ich schaue mich nur mal um',25,0,0),('157edc4f5682113c304b19295bfb5b2f','b5329b23b7f865c62028e226715e1914','Ich bin nicht interessiert',26,0,0);
/*!40000 ALTER TABLE `voteanswers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voteanswers_user`
--

DROP TABLE IF EXISTS `voteanswers_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voteanswers_user` (
  `answer_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `votedate` int(20) DEFAULT NULL,
  PRIMARY KEY (`answer_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voteanswers_user`
--

LOCK TABLES `voteanswers_user` WRITE;
/*!40000 ALTER TABLE `voteanswers_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `voteanswers_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webservice_access_rules`
--

DROP TABLE IF EXISTS `webservice_access_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webservice_access_rules` (
  `api_key` varchar(100) NOT NULL DEFAULT '',
  `method` varchar(100) NOT NULL DEFAULT '',
  `ip_range` varchar(200) NOT NULL DEFAULT '',
  `type` enum('allow','deny') NOT NULL DEFAULT 'allow',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webservice_access_rules`
--

LOCK TABLES `webservice_access_rules` WRITE;
/*!40000 ALTER TABLE `webservice_access_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `webservice_access_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `widget_default`
--

DROP TABLE IF EXISTS `widget_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widget_default` (
  `pluginid` int(11) NOT NULL,
  `col` tinyint(1) NOT NULL DEFAULT '0',
  `position` tinyint(1) NOT NULL DEFAULT '0',
  `perm` enum('user','autor','tutor','dozent','admin','root') NOT NULL DEFAULT 'autor',
  PRIMARY KEY (`perm`,`pluginid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `widget_default`
--

LOCK TABLES `widget_default` WRITE;
/*!40000 ALTER TABLE `widget_default` DISABLE KEYS */;
INSERT INTO `widget_default` VALUES (5,0,0,'user'),(5,0,0,'autor'),(5,0,0,'tutor'),(5,0,0,'dozent'),(5,0,0,'admin'),(5,0,0,'root'),(4,0,1,'user'),(4,0,1,'autor'),(4,0,1,'tutor'),(4,0,1,'dozent'),(4,0,1,'admin'),(4,0,1,'root'),(7,0,2,'user'),(7,0,2,'autor'),(7,0,2,'tutor'),(7,0,2,'dozent'),(7,0,2,'admin'),(7,0,2,'root'),(3,0,3,'user'),(3,0,3,'autor'),(3,0,3,'tutor'),(3,0,3,'dozent'),(3,0,3,'admin'),(3,0,3,'root');
/*!40000 ALTER TABLE `widget_default` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `widget_user`
--

DROP TABLE IF EXISTS `widget_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widget_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pluginid` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `range_id` varchar(32) NOT NULL,
  `col` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `range_id` (`range_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `widget_user`
--

LOCK TABLES `widget_user` WRITE;
/*!40000 ALTER TABLE `widget_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `widget_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wiki`
--

DROP TABLE IF EXISTS `wiki`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wiki` (
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) DEFAULT NULL,
  `keyword` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `body` text,
  `chdate` int(11) DEFAULT NULL,
  `version` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`range_id`,`keyword`,`version`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wiki`
--

LOCK TABLES `wiki` WRITE;
/*!40000 ALTER TABLE `wiki` DISABLE KEYS */;
/*!40000 ALTER TABLE `wiki` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wiki_links`
--

DROP TABLE IF EXISTS `wiki_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wiki_links` (
  `range_id` char(32) NOT NULL DEFAULT '',
  `from_keyword` char(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `to_keyword` char(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`range_id`,`to_keyword`,`from_keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wiki_links`
--

LOCK TABLES `wiki_links` WRITE;
/*!40000 ALTER TABLE `wiki_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `wiki_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wiki_locks`
--

DROP TABLE IF EXISTS `wiki_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wiki_locks` (
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `range_id` varchar(32) NOT NULL DEFAULT '',
  `keyword` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `chdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`range_id`,`user_id`,`keyword`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wiki_locks`
--

LOCK TABLES `wiki_locks` WRITE;
/*!40000 ALTER TABLE `wiki_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `wiki_locks` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-12-02 12:29:16
