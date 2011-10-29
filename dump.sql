set names utf8;
-- MySQL dump 10.13  Distrib 5.1.39, for apple-darwin10.0.0 (i386)
--
-- Host: localhost    Database: starter
-- ------------------------------------------------------
-- Server version	5.1.39
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `_access`
--

DROP TABLE IF EXISTS `_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_access` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_models_id` int(11) NOT NULL DEFAULT '0',
  `_users_id` int(11) unsigned NOT NULL DEFAULT '0',
  `add_access` enum('yes','no') NOT NULL DEFAULT 'no',
  `edit_access` enum('yes','no') NOT NULL DEFAULT 'no',
  `delete_access` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_access`
--

LOCK TABLES `_access` WRITE;
/*!40000 ALTER TABLE `_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_adminlog`
--

DROP TABLE IF EXISTS `_adminlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_adminlog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `action` char(3) NOT NULL,
  `adminlogin` varchar(16) NOT NULL,
  `adminid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_adminlog`
--

LOCK TABLES `_adminlog` WRITE;
/*!40000 ALTER TABLE `_adminlog` DISABLE KEYS */;
/*!40000 ALTER TABLE `_adminlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_cache`
--

DROP TABLE IF EXISTS `_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_cache` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `f` varchar(64) NOT NULL,
  `u` varchar(255) NOT NULL,
  `e` int(11) NOT NULL DEFAULT '0',
  `v` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_cache`
--

LOCK TABLES `_cache` WRITE;
/*!40000 ALTER TABLE `_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_cache__models_rel`
--

DROP TABLE IF EXISTS `_cache__models_rel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_cache__models_rel` (
  `_cache_id_key1` int(11) NOT NULL,
  `_models_id_key2` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_cache__models_rel`
--

LOCK TABLES `_cache__models_rel` WRITE;
/*!40000 ALTER TABLE `_cache__models_rel` DISABLE KEYS */;
/*!40000 ALTER TABLE `_cache__models_rel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_models`
--

DROP TABLE IF EXISTS `_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_models` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `txt_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon_name` varchar(64) NOT NULL,
  `icon_uri` varchar(255) NOT NULL,
  `icon_width` varchar(4) NOT NULL,
  `icon_height` varchar(4) NOT NULL,
  `icon_ext` varchar(4) NOT NULL,
  `ordering` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_models`
--

LOCK TABLES `_models` WRITE;
/*!40000 ALTER TABLE `_models` DISABLE KEYS */;
INSERT INTO `_models` VALUES (1,'127.0.0.1','2011-01-09 17:48:03','Структура','structure','','','','','',8),(11,'','2011-10-24 12:26:28','Тексты','texts','','','','','',9),(4,'127.0.0.1','2011-01-09 17:48:00','[Админы]','_users','','','','','',1),(5,'127.0.0.1','2011-01-09 17:48:01','[Доступ]','_access','','','','','',4),(6,'127.0.0.1','2011-01-09 17:48:01','[Модели]','_models','','','','','',5),(7,'127.0.0.1','2011-01-09 17:48:01','[Поля]','_modelsfields','','','','','',6),(8,'127.0.0.1','2011-01-09 17:48:01','[Лог авторизации]','_adminlog','','','','','',7);
/*!40000 ALTER TABLE `_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_modelsfields`
--

DROP TABLE IF EXISTS `_modelsfields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_modelsfields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_models_id` int(11) unsigned NOT NULL DEFAULT '0',
  `txt_name` varchar(255) NOT NULL,
  `help_text` varchar(255) NOT NULL,
  `db_column` varchar(255) NOT NULL,
  `_name` varchar(255) NOT NULL,
  `blank` enum('yes','no') NOT NULL DEFAULT 'no',
  `editable` enum('yes','no') NOT NULL DEFAULT 'yes',
  `viewable` enum('yes','no') NOT NULL DEFAULT 'no',
  `allowtags` enum('yes','no') NOT NULL DEFAULT 'no',
  `maxlength` int(11) DEFAULT '0',
  `choices` varchar(255) NOT NULL,
  `editor` varchar(16) NOT NULL,
  `path` varchar(200) NOT NULL,
  `match` varchar(64) NOT NULL,
  `sizes` varchar(64) NOT NULL,
  `unique` enum('yes','no') NOT NULL DEFAULT 'no',
  `fieldrel` varchar(24) NOT NULL,
  `core` enum('yes','no') NOT NULL DEFAULT 'no',
  `num_in_admin` varchar(5) NOT NULL,
  `modelrel` varchar(24) NOT NULL,
  `null` enum('yes','no') NOT NULL DEFAULT 'no',
  `default` varchar(100) NOT NULL,
  `ordering` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_modelsfields`
--

LOCK TABLES `_modelsfields` WRITE;
/*!40000 ALTER TABLE `_modelsfields` DISABLE KEYS */;
INSERT INTO `_modelsfields` VALUES (1,'127.0.0.1','2011-01-09 19:16:05',1,'Порядок','','ordering','orderfield','yes','no','no','no',NULL,'','','','','','no','parent','no','','','no','1',1),(2,'127.0.0.1','2011-01-09 19:16:05',1,'Родитель','','parent','treefield','no','yes','no','no',NULL,'','','','','','no','','no','','','no','0',2),(3,'127.0.0.1','2011-01-09 19:16:05',1,'URL','допустимы латинские буквы и цифры','url','urlfield','no','yes','no','no',255,'','','','','','yes','parent','no','','','no','',3),(4,'127.0.0.1','2011-01-09 19:16:05',1,'Название раздела','','title','charfield','no','yes','no','no',255,'','','','','','no','','no','','','no','',4),(5,'127.0.0.1','2011-01-09 19:16:05',1,'Альтернативное название раздела','','alternative','charfield','yes','no','no','no',255,'','','','','','no','','no','','','no','',5),(6,'127.0.0.1','2011-01-09 19:16:05',1,'Редирект','','redirect','booleanfield','yes','no','no','no',NULL,'перенаправлять пользователей в первый подраздел текущего раздела','','','','','no','','no','','','no','no',6),(7,'127.0.0.1','2011-01-09 19:16:05',1,'Тег title','если не заполнить (рекомендуется), то будет сформирован автоматически','titletag','charfield','yes','yes','no','no',255,'','','','','','no','','no','','','no','',7),(8,'127.0.0.1','2011-01-09 19:16:05',1,'ключевые слова для тега meta','','keywords','charfield','yes','yes','no','no',255,'','','','','','no','','no','','','no','',8),(9,'127.0.0.1','2011-01-09 19:16:05',1,'описание для тега meta','','description','charfield','yes','yes','no','no',255,'','','','','','no','','no','','','no','',9),(10,'127.0.0.1','2011-01-09 19:16:05',1,'Обработчик','','view','charfield','yes','yes','no','no',32,'viewsChoices()','','','','','no','','no','','','no','',10),(11,'127.0.0.1','2011-01-09 19:16:05',1,'Шаблон','','template','charfield','yes','yes','no','no',32,'templateChoices()','','','','','no','','no','','','no','',11),(12,'127.0.0.1','2011-01-09 19:16:05',1,'Время хранения в кэше','в секундах','cachetime','intfield','yes','yes','no','no',NULL,'','','','','','no','','no','','','no','0',12),(13,'127.0.0.1','2011-01-09 19:16:05',1,'Показывать в меню','','menu_on','booleanfield','no','yes','no','no',NULL,'не включать','','','','','no','','no','','','no','no',13),(14,'127.0.0.1','2011-01-09 19:16:05',1,'Показывать на карте сайта','','sitemap_on','booleanfield','no','yes','no','no',NULL,'не включать','','','','','no','','no','','','no','no',14),(15,'127.0.0.1','2011-01-09 19:16:05',1,'Временно скрыть раздел и подразделы','','is_hidden','booleanfield','no','yes','no','no',NULL,'скрыть,показать','','','','','no','','no','','','no','no',15),(16,'127.0.0.1','2011-01-09 19:16:05',1,'domain','','domain','domainfield','yes','no','no','no',NULL,'','','','','','no','','no','','','no','',16),(17,'127.0.0.1','2011-01-09 19:16:05',11,'Раздел','','structure_id','foreignkeyfield','no','yes','no','no',NULL,'','','','','','no','','no','','structure','no','0',1),(18,'127.0.0.1','2011-01-09 19:16:05',11,'Ярлык','латиница','label','charfield','no','yes','no','no',32,'labelChoices()','','','/^[a-zA-Z_-]+$/','','yes','structure_id','no','','','no','',2),(19,'127.0.0.1','2011-01-09 19:16:05',11,'Текст/код','','body','textfield','yes','yes','no','yes',NULL,'','medium_360','','','','no','','no','','','no','',3),(20,'127.0.0.1','2011-01-09 19:16:05',11,'structure_title','','structure_title','charfield','yes','no','no','no',255,'','','','','','no','','no','','','no','',4),(21,'127.0.0.1','2011-01-09 19:16:05',11,'domain','','domain','domainfield','yes','no','no','no',NULL,'','','','','','no','','no','','','no','',5),(22,'127.0.0.1','2011-01-09 19:16:05',4,'Имя','','name','charfield','yes','yes','no','no',64,'','','','','','no','','no','','','no','',1),(23,'127.0.0.1','2011-01-09 19:16:05',4,'Логин','','login','charfield','no','yes','no','no',16,'','','','','','yes','domain','no','','','no','',2),(24,'127.0.0.1','2011-01-09 19:16:05',4,'Хэш','','hash','charfield','yes','no','no','no',64,'','','','','','no','','no','','','no','',3),(25,'127.0.0.1','2011-01-09 19:16:05',4,'Пароль','','password1','passwordfield','yes','yes','no','no',16,'','','','','','no','','no','','','no','',4),(26,'127.0.0.1','2011-01-09 19:16:05',4,'Повторите пароль','','password2','passwordfield','yes','yes','no','no',16,'','','','','','no','','no','','','no','',5),(27,'127.0.0.1','2011-01-09 19:16:05',4,'Суперюзер','','su','booleanfield','yes','no','no','no',NULL,'да,нет','','','','','no','','no','','','no','no',6),(28,'127.0.0.1','2011-01-09 19:16:05',4,'Доступ ко всем субдоменам','','multidomain','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','no','','','no','no',7),(29,'127.0.0.1','2011-01-09 19:16:05',4,'Изменены права доступа','','is_changed','booleanfield','yes','no','no','no',NULL,'да,нет','','','','','no','','no','','','no','no',8),(30,'127.0.0.1','2011-01-09 19:16:05',4,'domain','','domain','domainfield','yes','no','no','no',NULL,'','','','','','no','','no','','','no','',9),(31,'127.0.0.1','2011-01-09 19:16:05',5,'Модель','','_models_id','intfield','no','yes','no','no',NULL,'','','','','','no','','no','','','no','0',1),(32,'127.0.0.1','2011-01-09 19:16:05',5,'Пользователь','','_users_id','foreignkeyfield','no','yes','no','no',NULL,'','','','','','no','','no','','_users','no','0',2),(33,'127.0.0.1','2011-01-09 19:16:05',5,'Добавление','','add_access','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','no',3),(34,'127.0.0.1','2011-01-09 19:16:05',5,'Редактирование','','edit_access','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','no',4),(35,'127.0.0.1','2011-01-09 19:16:05',5,'Удаление','','delete_access','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','no',5),(36,'127.0.0.1','2011-01-09 19:16:05',6,'Русское название модели','','txt_name','charfield','no','yes','no','no',255,'','','','','','no','','no','','','no','',1),(37,'127.0.0.1','2011-01-09 19:16:05',6,'Наименование класса модели','маленькие латинские буквы','name','charfield','no','yes','no','no',255,'','','','','','yes','','no','','','no','',2),(38,'127.0.0.1','2011-01-09 19:16:05',6,'Иконка','','icon','imagefield','yes','yes','no','no',NULL,'','','/admin/fw/media/img/','/\\.gif$/i','16/','no','','no','','','no','',3),(39,'127.0.0.1','2011-01-09 19:16:05',6,'ordering','','ordering','orderfield','yes','no','no','no',NULL,'','','','','','no','','no','','','no','1',4),(40,'127.0.0.1','2011-01-09 19:16:05',7,'Модель','','_models_id','foreignkeyfield','no','yes','no','no',NULL,'','','','','','no','','no','5','_models','no','0',1),(41,'127.0.0.1','2011-01-09 19:16:05',7,'Поле формы','','txt_name','charfield','no','yes','no','no',255,'','','','','','no','','yes','','','no','',2),(42,'127.0.0.1','2011-01-09 19:16:05',7,'Комментарий','','help_text','charfield','yes','yes','no','no',255,'','','','','','no','','yes','','','no','',3),(43,'127.0.0.1','2011-01-09 19:16:05',7,'Поле таблицы','','db_column','charfield','no','yes','no','no',255,'','','','','','yes','_models_id','yes','','','no','',4),(44,'127.0.0.1','2011-01-09 19:16:05',7,'Тип','','_name','charfield','no','yes','no','no',255,'fieldsChoices()','','','','','no','','yes','','','no','',5),(45,'127.0.0.1','2011-01-09 19:16:05',7,'blank','','blank','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','no',6),(46,'127.0.0.1','2011-01-09 19:16:05',7,'editable','','editable','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','yes',7),(47,'127.0.0.1','2011-01-09 19:16:05',7,'viewable','','viewable','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','no',8),(48,'127.0.0.1','2011-01-09 19:16:05',7,'allow tags','','allowtags','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','no',9),(49,'127.0.0.1','2011-01-09 19:16:05',7,'Длина','','maxlength','intfield','yes','yes','no','no',NULL,'','','','','','no','','yes','','','yes','0',10),(50,'127.0.0.1','2011-01-09 19:16:05',7,'choices','','choices','charfield','yes','yes','no','no',255,'','','','','','no','','yes','','','no','',11),(51,'127.0.0.1','2011-01-09 19:16:05',7,'editor','','editor','charfield','yes','yes','no','no',16,'editorTypes()','','','','','no','','yes','','','no','',12),(52,'127.0.0.1','2011-01-09 19:16:05',7,'path','','path','charfield','yes','yes','no','no',200,'','','','','','no','','yes','','','no','',13),(53,'127.0.0.1','2011-01-09 19:16:05',7,'match','','match','charfield','yes','yes','no','no',64,'','','','','','no','','yes','','','no','',14),(54,'127.0.0.1','2011-01-09 19:16:05',7,'sizes','','sizes','charfield','yes','yes','no','no',64,'','','','','','no','','yes','','','no','',15),(55,'127.0.0.1','2011-01-09 19:16:05',7,'unique','','unique','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','no',16),(56,'127.0.0.1','2011-01-09 19:16:05',7,'fieldrel','','fieldrel','charfield','yes','yes','no','no',24,'','','','','','no','','yes','','','no','',17),(57,'127.0.0.1','2011-01-09 19:16:05',7,'core','','core','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','no',18),(58,'127.0.0.1','2011-01-09 19:16:05',7,'num_in_admin','','num_in_admin','charfield','yes','yes','no','no',5,'','','','','','no','','yes','','','no','',19),(59,'127.0.0.1','2011-01-09 19:16:05',7,'modelrel','','modelrel','charfield','yes','yes','no','no',24,'','','','','','no','','yes','','','no','',20),(60,'127.0.0.1','2011-01-09 19:16:05',7,'null','','null','booleanfield','yes','yes','no','no',NULL,'да,нет','','','','','no','','yes','','','no','no',21),(61,'127.0.0.1','2011-01-09 19:16:05',7,'default','','default','charfield','yes','yes','no','no',100,'','','','','','no','','yes','','','no','',22),(62,'127.0.0.1','2011-01-09 19:16:05',7,'ordering','','ordering','orderfield','yes','no','no','no',NULL,'','','','','','no','_models_id','no','','','no','1',23),(63,'127.0.0.1','2011-01-09 19:16:05',8,'Действие','','action','charfield','no','yes','no','no',3,'actionChoices()','no','','','','no','','no','','','no','',1),(64,'127.0.0.1','2011-01-09 19:16:05',8,'Логин','','adminlogin','charfield','no','yes','no','no',16,'','','','','','no','','no','','','no','',2),(65,'127.0.0.1','2011-01-09 19:16:05',8,'ID','','adminid','intfield','no','yes','no','no',NULL,'','','','','','no','','no','','','no','0',3);
/*!40000 ALTER TABLE `_modelsfields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_users`
--

DROP TABLE IF EXISTS `_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `login` varchar(16) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `password1` varchar(16) NOT NULL,
  `password2` varchar(16) NOT NULL,
  `su` enum('yes','no') NOT NULL DEFAULT 'no',
  `domain` int(11) NOT NULL,
  `multidomain` enum('yes','no') NOT NULL DEFAULT 'no',
  `ip` varchar(15) NOT NULL,
  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_changed` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_users`
--

LOCK TABLES `_users` WRITE;
/*!40000 ALTER TABLE `_users` DISABLE KEYS */;
INSERT INTO `_users` VALUES (1,'su','admin','8cb2237d0679ca88db6464eac60da96345513964','','','yes',0,'yes','','2011-01-08 16:16:47','no');
/*!40000 ALTER TABLE `_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `structure`
--

DROP TABLE IF EXISTS `structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `structure` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ordering` int(11) unsigned NOT NULL DEFAULT '1',
  `parent` int(11) unsigned NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `view` varchar(32) NOT NULL,
  `template` varchar(32) NOT NULL,
  `cachetime` int(11) NOT NULL DEFAULT '0',
  `is_hidden` enum('yes','no') NOT NULL DEFAULT 'no',
  `domain` int(11) NOT NULL,
  `menu_on` enum('yes','no') NOT NULL DEFAULT 'no',
  `titletag` varchar(255) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `sitemap_on` enum('yes','no') NOT NULL DEFAULT 'no',
  `redirect` enum('yes','no') NOT NULL DEFAULT 'no',
  `alternative` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `structure`
--

LOCK TABLES `structure` WRITE;
/*!40000 ALTER TABLE `structure` DISABLE KEYS */;
INSERT INTO `structure` VALUES (1,'','2011-10-12 16:21:43',0,0,'ru','Название проекта','texts:content','p_first',500000,'no',1,'no','','','','yes','',''),(2,'','2011-10-12 16:21:49',0,1,'test','Тестовая внутренняя страница','','p_second',0,'no',1,'no','','','','yes','',''),(3,'','2011-10-12 16:21:55',0,1,'sitemap','Карта сайта','structure:sitemap','p_second',0,'no',1,'no','','','','no','','');
/*!40000 ALTER TABLE `structure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `texts`
--

DROP TABLE IF EXISTS `texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `texts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `structure_id` int(11) unsigned NOT NULL DEFAULT '0',
  `label` varchar(32) NOT NULL,
  `body` mediumtext NOT NULL,
  `structure_title` varchar(255) NOT NULL,
  `domain` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `texts`
--

LOCK TABLES `texts` WRITE;
/*!40000 ALTER TABLE `texts` DISABLE KEYS */;
INSERT INTO `texts` VALUES (1,'127.0.0.1','2011-10-12 14:19:46',1,'main','<p>\r\n	<strong>FW установлен и готов к работе!</strong></p>\r\n<p>\r\n	Созданы страницы:</p>\r\n<ul>\r\n	<li>\r\n		<a href=\"/test/\">внутренняя</a></li>\r\n	<li>\r\n		<a href=\"/sitemap/\">карта сайта</a></li>\r\n	<li>\r\n		<a href=\"/404/\">ошибка 404</a></li>\r\n</ul>','Название проекта',1),(2,'127.0.0.1','2011-01-09 09:18:38',2,'main','<p>\r\n	Большой и несколько запущенный вестибюль, про-сторный лифт и пестроглазый, в ржавых веснушках, мальчик Вася, вежливо стоявший в своем мундирчике, пока лифт медленно тянулся вверх, -- вдруг стало жалко покидать все это, давно знакомое, привычное. &quot;И правда, зачем я еду?&quot; Он посмотрел на себя в зеркало: молод, бодр, сухо-породист, глаза блестят, иней на красивых усах, хорошо и легко одет... в Ницце теперь чудесно, Генрих отличный товарищ... а главное, всегда кажется, что где-то там будет что-то особенно счастливое, какая-нибудь встреча... остановишься где-нибудь в пути, -- кто тут жил перед тобою, что висело и лежало в этом гардеробе, чьи это забытые в ночном столике женские шпильки? Опять будет запах газа, кофе и пива на венском вокзале, ярлыки на бутылках австрийских и итальянских вин на столиках в солнечном вагоне-ресто-ране в снегах Земмеринга, лица и одежды европейских мужчин и женщин, наполняющих этот вагон к завтраку... Потом ночь, Италия... Утром, по дороге вдоль моря к Ницце, то пролеты в грохочущей и дымящей темноте туннелей и слабо горящие лампочки на потолке купе, то остановки и что-то нежно и непрерывно звенящее на маленьких станциях в цветущих розах, возле млеющего в жарком солнце, как сплав драгоценных камней, за-ливчике... И он быстро пошел по коврам теплых ко-ридоров Лоскутной.</p>','Тестовая внутренняя страница',1);
/*!40000 ALTER TABLE `texts` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-10-28 16:23:36
