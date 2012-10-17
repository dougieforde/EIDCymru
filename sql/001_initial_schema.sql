# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.1.59)
# Database: scoteid_clean
# Generation Time: 2012-07-19 10:56:44 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table bpex_movement_types
# ------------------------------------------------------------

DROP TABLE IF EXISTS `bpex_movement_types`;

CREATE TABLE `bpex_movement_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `visible` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `bpex_movement_types` WRITE;
/*!40000 ALTER TABLE `bpex_movement_types` DISABLE KEYS */;

INSERT INTO `bpex_movement_types` (`id`, `name`, `description`, `visible`)
VALUES
	(1,'FarmToFarm','Farm to Farm',1),
	(2,'FarmToMarketOnwardFarm','Farm to Market onward Farm',1),
	(3,'FarmToMarketOnwardSlaughter','Farm to Market onward Slaughter',1),
	(4,'FarmToSlaughter','Farm to Slaughter',1),
	(5,'FarmToShow','Farm to Show',1),
	(6,'FarmToOther','Farm to Other',1),
	(7,'FarmToPort','A move between a keeper\'s premises and a port for animals being exported to another country.',0),
	(8,'MarketToFarm','Market to Farm',1),
	(9,'MarketToSlaughter','Market to Slaughter',1),
	(10,'ShowToFarm','Show to Farm',1),
	(11,'OtherToFarm','Other to Farm',1),
	(12,'PortToFarm','A move from a port to a keeper\'s premises for animals being imported from another country.',0),
	(13,'PortToMarketOnwardFarm','A move from a port to a market or collection centre for animals being imported from another country, with the expectation that the onward move would be to another farm.',0),
	(14,'PortToMarketOnwardSlaughter','A move from a port to a market or collection centre for animals being imported from another country, with the expectation that the onward move would be to an abattoir. FCI information must be provided for onward transmission to the FBO.',0),
	(15,'PortToSlaughter','A move from a port to an abattoir for animals being imported from another country, with the expectation that the onward move would be to an abattoir. FCI information must be provided for onward transmission to the FBO.',0),
	(16,'PortToOther','A move between a port and an AI centre, Laboratory or performance location. For moves to an AI centre, individual animal identifiers are required.',0);

/*!40000 ALTER TABLE `bpex_movement_types` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table ccp_data
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ccp_data`;

CREATE TABLE `ccp_data` (
  `UID` int(10) unsigned NOT NULL DEFAULT '0',
  `CPH` varchar(45) DEFAULT NULL,
  `Locationtype` varchar(45) DEFAULT NULL,
  `Reader` varchar(45) DEFAULT NULL,
  `Reader_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table ccppp_names
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ccppp_names`;

CREATE TABLE `ccppp_names` (
  `CC` int(2) unsigned zerofill NOT NULL DEFAULT '00',
  `PPP` int(3) unsigned zerofill NOT NULL DEFAULT '000',
  `County` varchar(45) DEFAULT NULL COMMENT 'Seem to be called preserved counties in Wales',
  `Parish` varchar(45) NOT NULL,
  `Country` varchar(45) DEFAULT NULL,
  `AHDO_no` int(2) unsigned DEFAULT NULL COMMENT 'Animal Health Divisional Office',
  `CCPPP_str` char(7) DEFAULT NULL,
  `map_url` text,
  PRIMARY KEY (`CC`,`PPP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table country_codes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `country_codes`;

CREATE TABLE `country_codes` (
  `Country_code` int(3) NOT NULL,
  `alpha_2` char(2) NOT NULL,
  `alpha_3` char(3) NOT NULL,
  `Country_name` varchar(100) NOT NULL,
  `abbreviation` char(2) DEFAULT NULL COMMENT 'Strangely the code used in this country is 826 which equates to GB. The outside of all the tags is UK so this is wrong. This column takes apha_2 and fixes it for the UK position',
  PRIMARY KEY (`Country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `country_codes` WRITE;
/*!40000 ALTER TABLE `country_codes` DISABLE KEYS */;

INSERT INTO `country_codes` (`Country_code`, `alpha_2`, `alpha_3`, `Country_name`, `abbreviation`)
VALUES
	(4,'AF','AFG','Afghanistan','AF'),
	(8,'AL','ALB','Albania, People\'s Socialist Republic of ','AL'),
	(10,'AQ','ATA','Antarctica (the territory South of 60 deg S) ','AQ'),
	(12,'DZ','DZA','Algeria, People\'s Democratic Republic of ','DZ'),
	(16,'AS','ASM','American Samoa ','AS'),
	(20,'AD','AND','Andorra, Principality of ','AD'),
	(24,'AO','AGO','Angola, Republic of ','AO'),
	(28,'AG','ATG','Antigua and Barbuda ','AG'),
	(31,'AZ','AZE','Azerbaijan, Republic of','AZ'),
	(32,'AR','ARG','Argentina, Argentine Republic ','AR'),
	(36,'AU','AUS','Australia, Commonwealth of ','AU'),
	(40,'AT','AUT','Austria, Republic of ','AT'),
	(44,'BS','BHS','Bahamas, Commonwealth of the ','BS'),
	(48,'BH','BHR','Bahrain, Kingdom of ','BH'),
	(50,'BD','BGD','Bangladesh, People\'s Republic of ','BD'),
	(51,'AM','ARM','Armenia ','AM'),
	(52,'BB','BRB','Barbados ','BB'),
	(56,'BE','BEL','Belgium, Kingdom of ','BE'),
	(60,'BM','BMU','Bermuda ','BM'),
	(64,'BT','BTN','Bhutan, Kingdom of ','BT'),
	(68,'BO','BOL','Bolivia, Republic of ','BO'),
	(70,'BA','BIH','Bosnia and Herzegovina','BA'),
	(72,'BW','BWA','Botswana, Republic of ','BW'),
	(74,'BV','BVT','Bouvet Island (Bouvetoya) ','BV'),
	(76,'BR','BRA','Brazil, Federative Republic of ','BR'),
	(84,'BZ','BLZ','Belize ','BZ'),
	(86,'IO','IOT','British Indian Ocean Territory (Chagos Archipelago) ','IO'),
	(90,'SB','SLB','Solomon Islands (was British Solomon Islands) ','SB'),
	(92,'VG','VGB','British Virgin Islands ','VG'),
	(96,'BN','BRN','Brunei Darussalam ','BN'),
	(100,'BG','BGR','Bulgaria, People\'s Republic of ','BG'),
	(104,'MM','MMR','Myanmar (was Burma) ','MM'),
	(108,'BI','BDI','Burundi, Republic of ','BI'),
	(112,'BY','BLR','Belarus ','BY'),
	(116,'KH','KHM','Cambodia, Kingdom of (was Khmer Republic/Kampuchea, Democratic) ','KH'),
	(120,'CM','CMR','Cameroon, United Republic of ','CM'),
	(124,'CA','CAN','Canada ','CA'),
	(132,'CV','CPV','Cape Verde, Republic of ','CV'),
	(136,'KY','CYM','Cayman Islands ','KY'),
	(140,'CF','CAF','Central African Republic ','CF'),
	(144,'LK','LKA','Sri Lanka, Democratic Socialist Republic of (was Ceylon) ','LK'),
	(148,'TD','TCD','Chad, Republic of ','TD'),
	(152,'CL','CHL','Chile, Republic of ','CL'),
	(156,'CN','CHN','China, People\'s Republic of ','CN'),
	(158,'TW','TWN','Taiwan, Province of China ','TW'),
	(162,'CX','CXR','Christmas Island ','CX'),
	(166,'CC','CCK','Cocos (Keeling) Islands ','CC'),
	(170,'CO','COL','Colombia, Republic of ','CO'),
	(174,'KM','COM','Comoros, Union of the','KM'),
	(175,'YT','MYT','Mayotte ','YT'),
	(178,'CG','COG','Congo, People\'s Republic of ','CG'),
	(180,'CD','COD','Congo, Democratic Republic of (was Zaire) ','CD'),
	(184,'CK','COK','Cook Islands ','CK'),
	(188,'CR','CRI','Costa Rica, Republic of ','CR'),
	(191,'HR','HRV','Hrvatska (Croatia) ','HR'),
	(192,'CU','CUB','Cuba, Republic of ','CU'),
	(196,'CY','CYP','Cyprus, Republic of ','CY'),
	(203,'CZ','CZE','Czech Republic ','CZ'),
	(204,'BJ','BEN','Benin (was Dahomey), People\'s Republic of ','BJ'),
	(208,'DK','DNK','Denmark, Kingdom of ','DK'),
	(212,'DM','DMA','Dominica, Commonwealth of ','DM'),
	(214,'DO','DOM','Dominican Republic ','DO'),
	(218,'EC','ECU','Ecuador, Republic of ','EC'),
	(222,'SV','SLV','El Salvador, Republic of ','SV'),
	(226,'GQ','GNQ','Equatorial Guinea, Republic of ','GQ'),
	(231,'ET','ETH','Ethiopia ','ET'),
	(232,'ER','ERI','Eritrea ','ER'),
	(233,'EE','EST','Estonia ','EE'),
	(234,'FO','FRO','Faeroe Islands ','FO'),
	(238,'FK','FLK','Falkland Islands (Malvinas) ','FK'),
	(239,'GS','SGS','South Georgia and the South Sandwich Islands ','GS'),
	(242,'FJ','FJI','Fiji, Republic of the Fiji Islands','FJ'),
	(246,'FI','FIN','Finland, Republic of ','FI'),
	(250,'FR','FRA','France, French Republic ','FR'),
	(254,'GF','GUF','French Guiana ','GF'),
	(258,'PF','PYF','French Polynesia ','PF'),
	(260,'TF','ATF','French Southern Territories ','TF'),
	(262,'DJ','DJI','Djibouti, Republic of (was French Afars and Issas) ','DJ'),
	(266,'GA','GAB','Gabon, Gabonese Republic ','GA'),
	(268,'GE','GEO','Georgia ','GE'),
	(270,'GM','GMB','Gambia, Republic of the ','GM'),
	(275,'PS','PSE','Palestinian Territory, Occupied','PS'),
	(276,'DE','DEU','Germany ','DE'),
	(288,'GH','GHA','Ghana, Republic of ','GH'),
	(292,'GI','GIB','Gibraltar ','GI'),
	(296,'KI','KIR','Kiribati, Republic of (was Gilbert Islands) ','KI'),
	(300,'GR','GRC','Greece, Hellenic Republic ','GR'),
	(304,'GL','GRL','Greenland ','GL'),
	(308,'GD','GRD','Grenada ','GD'),
	(312,'GP','GLP','Guadaloupe ','GP'),
	(316,'GU','GUM','Guam ','GU'),
	(320,'GT','GTM','Guatemala, Republic of ','GT'),
	(324,'GN','GIN','Guinea, Revolutionary People\'s Rep\'c of ','GN'),
	(328,'GY','GUY','Guyana, Republic of ','GY'),
	(332,'HT','HTI','Haiti, Republic of ','HT'),
	(334,'HM','HMD','Heard and McDonald Islands ','HM'),
	(336,'VA','VAT','Holy See (Vatican City State) ','VA'),
	(340,'HN','HND','Honduras, Republic of ','HN'),
	(344,'HK','HKG','Hong Kong, Special Administrative Region of China','HK'),
	(348,'HU','HUN','Hungary, Hungarian People\'s Republic ','HU'),
	(352,'IS','ISL','Iceland, Republic of ','IS'),
	(356,'IN','IND','India, Republic of ','IN'),
	(360,'ID','IDN','Indonesia, Republic of ','ID'),
	(364,'IR','IRN','Iran, Islamic Republic of ','IR'),
	(368,'IQ','IRQ','Iraq, Republic of ','IQ'),
	(372,'IE','IRL','Ireland ','IE'),
	(376,'IL','ISR','Israel, State of ','IL'),
	(380,'IT','ITA','Italy, Italian Republic ','IT'),
	(384,'CI','CIV','Cote D\'Ivoire, Ivory Coast, Republic of the ','CI'),
	(388,'JM','JAM','Jamaica ','JM'),
	(392,'JP','JPN','Japan ','JP'),
	(398,'KZ','KAZ','Kazakhstan, Republic of','KZ'),
	(400,'JO','JOR','Jordan, Hashemite Kingdom of ','JO'),
	(404,'KE','KEN','Kenya, Republic of ','KE'),
	(408,'KP','PRK','Korea, Democratic People\'s Republic of ','KP'),
	(410,'KR','KOR','Korea, Republic of ','KR'),
	(414,'KW','KWT','Kuwait, State of ','KW'),
	(417,'KG','KGZ','Kyrgyz Republic ','KG'),
	(418,'LA','LAO','Lao People\'s Democratic Republic ','LA'),
	(422,'LB','LBN','Lebanon, Lebanese Republic ','LB'),
	(426,'LS','LSO','Lesotho, Kingdom of ','LS'),
	(428,'LV','LVA','Latvia ','LV'),
	(430,'LR','LBR','Liberia, Republic of ','LR'),
	(434,'LY','LBY','Libyan Arab Jamahiriya ','LY'),
	(438,'LI','LIE','Liechtenstein, Principality of ','LI'),
	(440,'LT','LTU','Lithuania ','LT'),
	(442,'LU','LUX','Luxembourg, Grand Duchy of ','LU'),
	(446,'MO','MAC','Macao, Special Administrative Region of China','MO'),
	(450,'MG','MDG','Madagascar, Republic of ','MG'),
	(454,'MW','MWI','Malawi, Republic of ','MW'),
	(458,'MY','MYS','Malaysia ','MY'),
	(462,'MV','MDV','Maldives, Republic of ','MV'),
	(466,'ML','MLI','Mali, Republic of ','ML'),
	(470,'MT','MLT','Malta, Republic of ','MT'),
	(474,'MQ','MTQ','Martinique ','MQ'),
	(478,'MR','MRT','Mauritania, Islamic Republic of ','MR'),
	(480,'MU','MUS','Mauritius ','MU'),
	(484,'MX','MEX','Mexico, United Mexican States ','MX'),
	(492,'MC','MCO','Monaco, Principality of ','MC'),
	(496,'MN','MNG','Mongolia, Mongolian People\'s Republic ','MN'),
	(498,'MD','MDA','Moldova, Republic of ','MD'),
	(500,'MS','MSR','Montserrat ','MS'),
	(504,'MA','MAR','Morocco, Kingdom of ','MA'),
	(508,'MZ','MOZ','Mozambique, People\'s Republic of ','MZ'),
	(512,'OM','OMN','Oman, Sultanate of (was Muscat and Oman) ','OM'),
	(516,'NA','NAM','Namibia ','NA'),
	(520,'NR','NRU','Nauru, Republic of ','NR'),
	(524,'NP','NPL','Nepal, Kingdom of ','NP'),
	(528,'NL','NLD','Netherlands, Kingdom of the ','NL'),
	(530,'AN','ANT','Netherlands Antilles ','AN'),
	(533,'AW','ABW','Aruba ','AW'),
	(540,'NC','NCL','New Caledonia ','NC'),
	(548,'VU','VUT','Vanuatu (was New Hebrides) ','VU'),
	(554,'NZ','NZL','New Zealand ','NZ'),
	(558,'NI','NIC','Nicaragua, Republic of ','NI'),
	(562,'NE','NER','Niger, Republic of the ','NE'),
	(566,'NG','NGA','Nigeria, Federal Republic of ','NG'),
	(570,'NU','NIU','Niue, Republic of','NU'),
	(574,'NF','NFK','Norfolk Island ','NF'),
	(578,'NO','NOR','Norway, Kingdom of ','NO'),
	(580,'MP','MNP','Northern Mariana Islands ','MP'),
	(581,'UM','UMI','United States Minor Outlying Islands ','UM'),
	(583,'FM','FSM','Micronesia, Federated States of ','FM'),
	(584,'MH','MHL','Marshall Islands ','MH'),
	(585,'PW','PLW','Palau ','PW'),
	(586,'PK','PAK','Pakistan, Islamic Republic of ','PK'),
	(591,'PA','PAN','Panama, Republic of ','PA'),
	(598,'PG','PNG','Papua New Guinea ','PG'),
	(600,'PY','PRY','Paraguay, Republic of ','PY'),
	(604,'PE','PER','Peru, Republic of ','PE'),
	(608,'PH','PHL','Philippines, Republic of the ','PH'),
	(612,'PN','PCN','Pitcairn Island ','PN'),
	(616,'PL','POL','Poland, Polish People\'s Republic ','PL'),
	(620,'PT','PRT','Portugal, Portuguese Republic ','PT'),
	(624,'GW','GNB','Guinea-Bissau, Republic of (was Portuguese Guinea) ','GW'),
	(626,'TL','TLS','Timor-Leste, Democratic Republic of','TL'),
	(630,'PR','PRI','Puerto Rico ','PR'),
	(634,'QA','QAT','Qatar, State of ','QA'),
	(638,'RE','REU','Reunion ','RE'),
	(642,'RO','ROU','Romania, Socialist Republic of ','RO'),
	(643,'RU','RUS','Russian Federation ','RU'),
	(646,'RW','RWA','Rwanda, Rwandese Republic ','RW'),
	(654,'SH','SHN','St. Helena','SH'),
	(659,'KN','KNA','St. Kitts and Nevis','KN'),
	(660,'AI','AIA','Anguilla ','AI'),
	(662,'LC','LCA','St. Lucia','LC'),
	(666,'PM','SPM','St. Pierre and Miquelon','PM'),
	(670,'VC','VCT','St. Vincent and the Grenadines','VC'),
	(674,'SM','SMR','San Marino, Republic of ','SM'),
	(678,'ST','STP','Sao Tome and Principe, Democratic Republic of ','ST'),
	(682,'SA','SAU','Saudi Arabia, Kingdom of ','SA'),
	(686,'SN','SEN','Senegal, Republic of ','SN'),
	(690,'SC','SYC','Seychelles, Republic of ','SC'),
	(694,'SL','SLE','Sierra Leone, Republic of ','SL'),
	(702,'SG','SGP','Singapore, Republic of ','SG'),
	(703,'SK','SVK','Slovakia (Slovak Republic) ','SK'),
	(704,'VN','VNM','Viet Nam, Socialist Republic of (was Democratic Republic of & Republic of) ','VN'),
	(705,'SI','SVN','Slovenia ','SI'),
	(706,'SO','SOM','Somalia, Somali Republic ','SO'),
	(710,'ZA','ZAF','South Africa, Republic of ','ZA'),
	(716,'ZW','ZWE','Zimbabwe (was Southern Rhodesia) ','ZW'),
	(724,'ES','ESP','Spain, Spanish State ','ES'),
	(732,'EH','ESH','Western Sahara (was Spanish Sahara) ','EH'),
	(736,'SD','SDN','Sudan, Democratic Republic of the ','SD'),
	(740,'SR','SUR','Suriname, Republic of ','SR'),
	(744,'SJ','SJM','Svalbard & Jan Mayen Islands ','SJ'),
	(748,'SZ','SWZ','Swaziland, Kingdom of ','SZ'),
	(752,'SE','SWE','Sweden, Kingdom of ','SE'),
	(756,'CH','CHE','Switzerland, Swiss Confederation ','CH'),
	(760,'SY','SYR','Syrian Arab Republic ','SY'),
	(762,'TJ','TJK','Tajikistan ','TJ'),
	(764,'TH','THA','Thailand, Kingdom of ','TH'),
	(768,'TG','TGO','Togo, Togolese Republic ','TG'),
	(772,'TK','TKL','Tokelau (Tokelau Islands) ','TK'),
	(776,'TO','TON','Tonga, Kingdom of ','TO'),
	(780,'TT','TTO','Trinidad and Tobago, Republic of ','TT'),
	(784,'AE','ARE','United Arab Emirates (was Trucial States) ','AE'),
	(788,'TN','TUN','Tunisia, Republic of ','TN'),
	(792,'TR','TUR','Turkey, Republic of ','TR'),
	(795,'TM','TKM','Turkmenistan ','TM'),
	(796,'TC','TCA','Turks and Caicos Islands ','TC'),
	(798,'TV','TUV','Tuvalu (was part of Gilbert & Ellice Islands) ','TV'),
	(800,'UG','UGA','Uganda, Republic of ','UG'),
	(804,'UA','UKR','Ukraine ','UA'),
	(807,'MK','MKD','Macedonia, the former Yugoslav Republic of ','MK'),
	(818,'EG','EGY','Egypt, Arab Republic of ','EG'),
	(826,'GB','GBR','United Kingdom of Great Britain & N. Ireland ','UK'),
	(834,'TZ','TZA','Tanzania, United Republic of ','TZ'),
	(840,'US','USA','United States of America ','US'),
	(850,'VI','VIR','US Virgin Islands ','VI'),
	(854,'BF','BFA','Burkina Faso (was Upper Volta) ','BF'),
	(858,'UY','URY','Uruguay, Eastern Republic of ','UY'),
	(860,'UZ','UZB','Uzbekistan ','UZ'),
	(862,'VE','VEN','Venezuela, Bolivarian Republic of ','VE'),
	(876,'WF','WLF','Wallis and Futuna Islands ','WF'),
	(882,'WS','WSM','Samoa, Independent State of (was Western Samoa)','WS'),
	(887,'YE','YEM','Yemen ','YE'),
	(891,'CS','SCG','Serbia and Montenegro','CS'),
	(894,'ZM','ZMB','Zambia, Republic of ','ZM');

/*!40000 ALTER TABLE `country_codes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table eid_tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `eid_tags`;

CREATE TABLE `eid_tags` (
  `defra_tag_code` char(3) NOT NULL DEFAULT '',
  `tag_id` int(11) DEFAULT NULL,
  `scoteid_code` int(11) DEFAULT NULL,
  `is_EID` int(1) NOT NULL DEFAULT '1',
  `non_wysiwyg` int(1) NOT NULL DEFAULT '0',
  `aircoil_tag` int(1) DEFAULT NULL,
  `two_part_tag` int(1) DEFAULT NULL,
  `Bolus` int(1) NOT NULL DEFAULT '0',
  `tag_description` longtext,
  `suppliers` longtext,
  `comments` longtext CHARACTER SET utf8,
  `protocol` char(3) DEFAULT NULL,
  `tag_name` varchar(50) NOT NULL,
  PRIMARY KEY (`defra_tag_code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table fieldsmen_counties
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fieldsmen_counties`;

CREATE TABLE `fieldsmen_counties` (
  `UID` int(10) unsigned NOT NULL DEFAULT '0',
  `CountyVal` char(2) NOT NULL DEFAULT '',
  `CountyName` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`UID`,`CountyVal`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table hauliers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hauliers`;

CREATE TABLE `hauliers` (
  `Haulier_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(10) DEFAULT NULL,
  `Business` varchar(40) DEFAULT NULL,
  `Address2` varchar(26) DEFAULT NULL,
  `Address3` varchar(23) DEFAULT NULL,
  `Address4` varchar(18) DEFAULT NULL,
  `PostCode` varchar(8) DEFAULT NULL,
  `Telephone` varchar(16) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Pigs` int(1) DEFAULT NULL,
  `Cattle` int(1) DEFAULT NULL,
  `Sheep` int(1) DEFAULT NULL,
  `QMS_no` int(3) DEFAULT NULL,
  PRIMARY KEY (`Haulier_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table holding_inventory
# ------------------------------------------------------------

DROP TABLE IF EXISTS `holding_inventory`;

CREATE TABLE `holding_inventory` (
  `CPH` char(11) NOT NULL DEFAULT '',
  `UID` int(10) unsigned NOT NULL DEFAULT '0',
  `Year` int(4) unsigned NOT NULL DEFAULT '0',
  `Entry_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Inventory` int(10) unsigned NOT NULL,
  `Loss` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`CPH`,`UID`,`Year`,`Entry_timestamp`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Annual inventory for each holding; InnoDB free: 1853440 kB';



# Dump of table keeper_holdings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `keeper_holdings`;

CREATE TABLE `keeper_holdings` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `cph` varchar(11) NOT NULL DEFAULT '',
  `resident` tinyint(1) NOT NULL DEFAULT '0',
  `pigs` int(11) DEFAULT NULL,
  `pig_herd_number` varchar(10) DEFAULT NULL,
  `pig_slap_mark` varchar(10) DEFAULT NULL,
  `pig_slap_mark_2` varchar(10) DEFAULT NULL,
  `sheep` int(11) DEFAULT NULL,
  `sheep_flock_number` int(11) DEFAULT NULL,
  `cattle` int(11) DEFAULT NULL,
  `cattle_herd_number` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`uid`,`cph`),
  CONSTRAINT `keeper_holdings_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `keepers` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table keepers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `keepers`;

CREATE TABLE `keepers` (
  `uid` int(11) NOT NULL,
  `name` varchar(125) DEFAULT NULL,
  `address_1` varchar(100) DEFAULT NULL,
  `address_2` varchar(100) DEFAULT NULL,
  `address_3` varchar(100) DEFAULT NULL,
  `address_4` varchar(100) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `landline_tel` varchar(20) DEFAULT NULL,
  `mobile_tel` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `flock_assurance_number` int(6) unsigned zerofill DEFAULT NULL,
  `pig_assurance_number` int(6) unsigned zerofill DEFAULT NULL,
  `pig_producer_group_id` int(11) DEFAULT NULL,
  `sheep_breeds` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  KEY `pig_producer_group_id` (`pig_producer_group_id`),
  CONSTRAINT `keepers_ibfk_1` FOREIGN KEY (`pig_producer_group_id`) REFERENCES `pig_producer_groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table lot_descriptions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `lot_descriptions`;

CREATE TABLE `lot_descriptions` (
  `description_id` int(11) NOT NULL,
  `description` varchar(20) NOT NULL,
  `criteria` varchar(70) NOT NULL,
  PRIMARY KEY (`description_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='descriptions';

LOCK TABLES `lot_descriptions` WRITE;
/*!40000 ALTER TABLE `lot_descriptions` DISABLE KEYS */;

INSERT INTO `lot_descriptions` (`description_id`, `description`, `criteria`)
VALUES
	(0,'','Unspecified'),
	(1,'Porkers','60 - 75 kg'),
	(2,'Cutters','76 - 85 kg'),
	(3,'Baconers','86 - 104 kg'),
	(4,'Backfatters','105+ kg'),
	(5,'Breeding pigs',''),
	(6,'Sows',''),
	(7,'Boars',''),
	(8,'Boars & Sows',''),
	(9,'Weaners',''),
	(10,'Babies',''),
	(11,'Nursery Stores',''),
	(12,'Finisher Stores',''),
	(13,'Fat',''),
	(14,'Foster huts',''),
	(15,'Mixed',''),
	(16,'Gilts',''),
	(17,'Wild Boar',''),
	(18,'Culls',''),
	(19,'Breeding Stores',''),
	(20,'In Pig Sows','');

/*!40000 ALTER TABLE `lot_descriptions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table lot_problems
# ------------------------------------------------------------

DROP TABLE IF EXISTS `lot_problems`;

CREATE TABLE `lot_problems` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sams_movement_reference` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sams_movement_reference` (`sams_movement_reference`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table pig_producer_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pig_producer_groups`;

CREATE TABLE `pig_producer_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table species_codes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `species_codes`;

CREATE TABLE `species_codes` (
  `Species_code` int(1) NOT NULL DEFAULT '0',
  `Species_type` varchar(15) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `species_codes` WRITE;
/*!40000 ALTER TABLE `species_codes` DISABLE KEYS */;

INSERT INTO `species_codes` (`Species_code`, `Species_type`)
VALUES
	(1,'Equines'),
	(2,'Cattle'),
	(3,'Pigs'),
	(4,'Sheep and goats'),
	(5,'Poultry'),
	(6,'Other livestock');

/*!40000 ALTER TABLE `species_codes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tblaudititems
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblaudititems`;

CREATE TABLE `tblaudititems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `sams_movement_reference` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `data` longtext,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table tblindividualsheepeidregister
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblindividualsheepeidregister`;

CREATE TABLE `tblindividualsheepeidregister` (
  `EIDHexNumber` char(16) NOT NULL DEFAULT '',
  `UKNumber` varchar(14) DEFAULT NULL,
  `Holding` varchar(11) DEFAULT NULL,
  `Issue_Date` date DEFAULT NULL,
  `S_Breed_Code` varchar(5) DEFAULT NULL,
  `Sex` char(1) DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `AnimalEID` bigint(12) unsigned DEFAULT NULL,
  `lastmovecph` char(11) DEFAULT NULL,
  `Lastmovedate` date DEFAULT NULL,
  `lastmovetype` tinyint(1) unsigned DEFAULT NULL,
  `lastmoveref` int(10) unsigned DEFAULT NULL,
  `prevmoveref` int(10) unsigned DEFAULT NULL,
  `tagcolour` varchar(25) DEFAULT NULL,
  `defra_tag_code` char(3) DEFAULT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `lastreadcph` char(11) DEFAULT NULL,
  `country_code` int(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`EIDHexNumber`),
  KEY `AnimalEID` (`AnimalEID`),
  KEY `Holding` (`Holding`),
  KEY `lastmovecph` (`lastmovecph`),
  KEY `lastmovetype` (`lastmovetype`),
  KEY `lastmoveref` (`lastmoveref`),
  KEY `country_code` (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table tblmovementtypes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblmovementtypes`;

CREATE TABLE `tblmovementtypes` (
  `MovementType` int(10) NOT NULL DEFAULT '8',
  `Description` varchar(50) DEFAULT NULL,
  `ShortName` char(15) DEFAULT NULL,
  `Colour` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`MovementType`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `tblmovementtypes` WRITE;
/*!40000 ALTER TABLE `tblmovementtypes` DISABLE KEYS */;

INSERT INTO `tblmovementtypes` (`MovementType`, `Description`, `ShortName`, `Colour`)
VALUES
	(0,'Tags Issued','Issued',NULL),
	(1,'UNASSIGNED','UNASSIGNED',NULL),
	(2,'ON (Farm move)','ON','PaleGreen'),
	(3,'ON/OFF (Farm move)','ON/OFF','GreenYellow'),
	(4,'MART (CCP use only)','MART','Khaki'),
	(5,'DEATH (Not Abattoir)','Death','CRIMSON'),
	(6,'TAGGED','Tagged','Yellow'),
	(7,'SURPLUS (unused tags)','Surplus','SILVER'),
	(8,'ABATTOIR (CCP use only)','ABATTOIR','RED'),
	(9,'DROP-OUTS','Drop-out','Blue'),
	(10,'MANAGEMENT','Management','Grey'),
	(11,'INSERT FAIL (broken tag)','Insert break','purple'),
	(12,'ONLY TESTING','Testing','pink'),
	(13,'IDs missed at CCP','CCP Missed','RosyBrown'),
	(14,'UNREADABLE TAG','Unreadable','brown'),
	(15,'INVISIBLE','HIDDEN',NULL),
	(16,'INVENTORY','Inventory',NULL),
	(21,'RETAG (Flock tag homebred)','Retag',NULL),
	(22,'RETAG (Flock tag bought-in)','Retag',NULL),
	(23,'RETAG (Full ID homebred)','Retag',NULL),
	(24,'RETAG (Full ID bought-in)','Retag',NULL),
	(31,'UPGRADE (homebred)','Upgrade',NULL),
	(32,'UPGRADE (bought-in)','Upgrade',NULL);

/*!40000 ALTER TABLE `tblmovementtypes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tblofficialtagsuppliers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblofficialtagsuppliers`;

CREATE TABLE `tblofficialtagsuppliers` (
  `TG_SupplierID` char(3) NOT NULL,
  `TG_Name` varchar(50) DEFAULT NULL,
  `TG_AD1` varchar(50) DEFAULT NULL,
  `TG_AD2` varchar(50) DEFAULT NULL,
  `TG_AD3` varchar(50) DEFAULT NULL,
  `TG_AD4` varchar(50) DEFAULT NULL,
  `TG_PostCode` varchar(10) DEFAULT NULL,
  `TG_Tel` varchar(18) DEFAULT NULL,
  `TG_Fax` varchar(18) DEFAULT NULL,
  `TG_Email` varchar(80) DEFAULT NULL,
  `TG_Website` longtext,
  `EIDTagType` varchar(50) DEFAULT NULL,
  `Contact_Name` varchar(50) DEFAULT NULL,
  `Notes` longtext,
  `Countrycode` smallint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`TG_SupplierID`),
  KEY `PostCode` (`TG_PostCode`),
  KEY `SupplierID` (`TG_SupplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table tblreadscheduledefinitions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblreadscheduledefinitions`;

CREATE TABLE `tblreadscheduledefinitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `read_location` varchar(11) DEFAULT NULL,
  `schedule_serialized` text,
  `sheep` tinyint(1) DEFAULT '0',
  `cattle` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table tblreadscheduleitems
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblreadscheduleitems`;

CREATE TABLE `tblreadscheduleitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `read_location_cph` varchar(11) DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `cattle` tinyint(4) DEFAULT '0',
  `sheep` tinyint(4) DEFAULT '0',
  `comments` varchar(250) DEFAULT NULL,
  `warning_count` int(11) DEFAULT '0',
  `generated` tinyint(1) DEFAULT '0',
  `start_date_string` varchar(25) DEFAULT NULL,
  `start_time_string` varchar(25) DEFAULT NULL,
  `cattle_string` varchar(10) DEFAULT NULL,
  `uid` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `starts_at` (`starts_at`),
  KEY `read_location_cph` (`read_location_cph`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table tblsamholdings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblsamholdings`;

CREATE TABLE `tblsamholdings` (
  `CPH` char(11) NOT NULL DEFAULT '',
  `Name` varchar(60) DEFAULT NULL,
  `Business` varchar(60) DEFAULT NULL,
  `Address2` varchar(60) DEFAULT NULL,
  `Address3` varchar(60) DEFAULT NULL,
  `Address4` varchar(60) DEFAULT NULL,
  `Postcode` varchar(9) DEFAULT NULL,
  `FlockNumber` varchar(10) DEFAULT NULL,
  `AbattoirNo` varchar(4) DEFAULT NULL,
  `LocationType` varchar(50) DEFAULT NULL,
  `Scottish` tinyint(1) NOT NULL DEFAULT '0',
  `Trial` tinyint(1) NOT NULL DEFAULT '0',
  `Business_Telephone` varchar(16) DEFAULT NULL,
  `Business_Email` varchar(70) DEFAULT NULL,
  `Business_Fax` varchar(16) DEFAULT NULL,
  `Business_Website` varchar(60) DEFAULT NULL,
  `UID` int(10) unsigned DEFAULT NULL,
  `pigs` int(11) DEFAULT NULL,
  `cattle` int(11) DEFAULT NULL,
  `sheep` int(11) DEFAULT NULL,
  `data_source` varchar(45) DEFAULT NULL,
  `Inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`CPH`) USING BTREE,
  KEY `Scottish` (`Scottish`),
  KEY `LocationType` (`LocationType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table tblsheepbreeds
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblsheepbreeds`;

CREATE TABLE `tblsheepbreeds` (
  `S_Breed_Code` varchar(5) NOT NULL DEFAULT '',
  `S_Breed` varchar(32) DEFAULT NULL,
  `list_order` smallint(5) unsigned DEFAULT '0',
  PRIMARY KEY (`S_Breed_Code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `tblsheepbreeds` WRITE;
/*!40000 ALTER TABLE `tblsheepbreeds` DISABLE KEYS */;

INSERT INTO `tblsheepbreeds` (`S_Breed_Code`, `S_Breed`, `list_order`)
VALUES
	('BDM','BLEU DU MAINE ',0),
	('BEL','BELTEX',0),
	('BER','BERRICHON DU CHER ',0),
	('BEU','BEULAH SPECKLED FACE ',0),
	('BFL','BLUEFACED LEICESTER ',0),
	('BFS','BLACKFACE',0),
	('BINR','BRITISH INRA 401',0),
	('BL','BORDER LEICESTER ',0),
	('BMS','BRITISH MILKSHEEP ',0),
	('BOR','BORERAY',0),
	('CAM','CAMBRIDGE ',0),
	('CAS','CASTLEMILK MOORIT ',0),
	('CBD','COLBRED ',0),
	('CF','CLUN FOREST ',0),
	('CHA','CHAROLLAIS ',0),
	('CMS','CHARMOISE',0),
	('CTN','COTENTIN',0),
	('CTW','COTSWOLD ',0),
	('CVBH','CHEVIOT (BRECKNOCK HILL )',0),
	('CVNC','CHEVIOT (North Country)',0),
	('CVSC','CHEVIOT (South Country)',0),
	('CVSH','CHEVIOT (Shetland)',0),
	('DBR','DALESBRED',0),
	('DCL','DEVON & CORNWALL LONGWOOL',0),
	('DD','DORSET DOWN',0),
	('DH','DORSET HORN & POLL DORSET',0),
	('DRB','DERBYSHIRE GRITSTONE',0),
	('DRT','DARTMOOR ',0),
	('DVC','DEVON CLOSEWOOL',0),
	('ELM','EST A LAINE MERINO',0),
	('EXM','EXMOOR HORN',0),
	('FLD','FRIESLAND',0),
	('GF','Greyface',0),
	('GLD','GOTLAND',0),
	('HBS','HALFBRED (Scotch)',0),
	('HBW','HALFBRED (Welsh)',0),
	('HD','HAMPSHIRE DOWN',0),
	('HDW','HERDWICK',0),
	('HEB7','HEBRIDEAN',0),
	('HRA','HILL RADNOR',0),
	('IDF','ILE DE FRANCE',0),
	('ILD','ICELANDIC',0),
	('JCB','JACOB',0),
	('KH','KERRY HILL',0),
	('LELW','LEICESTER LONGWOOL',0),
	('LLE','LLEYN',0),
	('LLW','LLANWENOG',0),
	('LNK','LONK',0),
	('LNLW','LINCOLN LONGWOOL',0),
	('MAS','MASHAM',0),
	('MLC','MEATLINC',0),
	('MLG','MANX LOGHTAN',0),
	('MU','MULE',0),
	('NH','NORFOLK HORN',0),
	('NRY','NORTH RONALDSAY ',0),
	('ODN','OXFORD DOWN ',0),
	('PTL','PORTLAND ',0),
	('RDL','ROUGE DE L\'OUEST ',0),
	('RF','ROUGH FELL',0),
	('RMN','ROMNEY',0),
	('RSN','ROUSSIN ',0),
	('RYL','RYELAND',0),
	('SDN','SOUTHDOWN ',0),
	('SHET','SHETLAND',0),
	('SHP','SHROPSHIRE ',0),
	('SOY','SOAY ',0),
	('SUF','SUFFOLK',0),
	('SWA','SWALEDALE',0),
	('SWM','SOUTH WALES MOUNTAIN',0),
	('TE','TEXEL',0),
	('TWT','TEESWATER',0),
	('USB','Unlisted Sheep Breed',10),
	('VDN','VENDEEN',0),
	('WFD','WHITE FACE DARTMOOR',0),
	('WFW','WHITE FACED WOODLAND',0),
	('WH','WILTSHIRE HORN',0),
	('WHS','WELSH HILL SPECKLED FACE',0),
	('WM','WELSH MOUNTAIN',0),
	('WMBF','WELSH Mnt. BADGER FACE',0),
	('WMBL','WELSH Mnt.  BLACK',0),
	('WMBW','WELSH Mnt.  BALWEN',0),
	('WND','WENSLEYDALE',0),
	('ZWA','ZWARTBLES',0);

/*!40000 ALTER TABLE `tblsheepbreeds` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tblsheepitemread
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblsheepitemread`;

CREATE TABLE `tblsheepitemread` (
  `Lot_No` varchar(20) NOT NULL DEFAULT '',
  `ReadLocationCPH` char(11) NOT NULL DEFAULT '99/999/9999',
  `LotDate` date NOT NULL,
  `EIDHexNumber` char(16) NOT NULL DEFAULT '',
  `MovementType` int(10) NOT NULL,
  `Timestamp` datetime DEFAULT NULL,
  `AnimalEID` bigint(20) unsigned DEFAULT '0',
  `Country_code` int(3) unsigned DEFAULT NULL,
  `Flock_tag` int(1) unsigned DEFAULT '0' COMMENT 'To indicate if it is a visually recorded flock tag',
  `Tag_count` int(3) unsigned DEFAULT '1' COMMENT 'How many. This is needed to cope with the flocktags',
  `Species` tinyint(3) unsigned NOT NULL DEFAULT '4',
  `prev_ID` bigint(20) unsigned DEFAULT NULL COMMENT 'For recorded replacements this is the original animal ID (if known)',
  `prev_country_code` int(3) unsigned NOT NULL DEFAULT '826' COMMENT 'For recorded replacements this si the original country code if known',
  PRIMARY KEY (`Lot_No`,`ReadLocationCPH`,`LotDate`,`EIDHexNumber`,`MovementType`,`Species`),
  KEY `LocationID` (`ReadLocationCPH`),
  KEY `animaleid` (`AnimalEID`),
  KEY `country_code` (`Country_code`),
  KEY `Lot_No` (`Lot_No`,`ReadLocationCPH`,`LotDate`,`MovementType`,`Species`),
  KEY `ReadLocationCPH` (`ReadLocationCPH`,`LotDate`,`Lot_No`,`MovementType`,`Species`),
  KEY `previous_id` (`prev_ID`,`prev_country_code`),
  CONSTRAINT `tblsheepitemread_ibfk_1` FOREIGN KEY (`ReadLocationCPH`, `LotDate`, `Lot_No`, `MovementType`, `Species`) REFERENCES `tblsslots` (`ReadLocationCPH`, `LotDate`, `Lot_No`, `MovementType`, `Species`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `after_insert_item_read` AFTER INSERT ON `tblsheepitemread` FOR EACH ROW BEGIN
	UPDATE 
		tblsslots 
	SET 
		tblsslots.Qty_Reads = tblsslots.Qty_Reads + NEW.Tag_Count
	WHERE
  	tblsslots.Lot_No = NEW.Lot_No AND
  	tblsslots.LotDate = NEW.LotDate AND
  	tblsslots.ReadLocationCPH = NEW.ReadLocationCPH AND
  	tblsslots.MovementType = NEW.MovementType AND
  	tblsslots.Species = NEW.Species;
END */;;
/*!50003 SET SESSION SQL_MODE="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `after_update_item_read` AFTER UPDATE ON `tblsheepitemread` FOR EACH ROW BEGIN
	UPDATE 
		tblsslots 
	SET 
		tblsslots.Qty_Reads = tblsslots.Qty_Reads + (NEW.Tag_Count - OLD.Tag_Count) 
	WHERE
		tblsslots.Lot_No = OLD.Lot_No AND
		tblsslots.LotDate = OLD.LotDate AND
		tblsslots.ReadLocationCPH = OLD.ReadLocationCPH AND
		tblsslots.MovementType = OLD.MovementType AND
		tblsslots.Species = OLD.Species;
END */;;
/*!50003 SET SESSION SQL_MODE="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `after_delete_item_read` AFTER DELETE ON `tblsheepitemread` FOR EACH ROW BEGIN
	UPDATE 
		tblsslots 
	SET 
		tblsslots.Qty_Reads = tblsslots.Qty_Reads - OLD.Tag_Count
	WHERE
  	tblsslots.Lot_No = OLD.Lot_No AND
  	tblsslots.LotDate = OLD.LotDate AND
  	tblsslots.ReadLocationCPH = OLD.ReadLocationCPH AND
  	tblsslots.MovementType = OLD.MovementType AND
  	tblsslots.Species = OLD.Species;
END */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table tblsslots
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblsslots`;

CREATE TABLE `tblsslots` (
  `SAMS_Movement_Reference` int(10) NOT NULL AUTO_INCREMENT,
  `foreign_reference` varchar(32) DEFAULT NULL,
  `Lot_No` varchar(20) NOT NULL,
  `ReadLocationCPH` char(11) NOT NULL DEFAULT '',
  `LotDate` date NOT NULL,
  `ArrivalDate` date DEFAULT NULL,
  `DepartureDate` date DEFAULT NULL,
  `MovementType` int(10) NOT NULL DEFAULT '10',
  `Species` tinyint(3) unsigned NOT NULL DEFAULT '4',
  `transport_details_id` int(10) unsigned DEFAULT NULL,
  `Qty_Sheep` int(6) unsigned NOT NULL DEFAULT '0',
  `Qty_DOA` int(11) DEFAULT NULL,
  `Qty_Reads` int(11) NOT NULL DEFAULT '0',
  `DepartureCPH` char(11) DEFAULT NULL,
  `DestinationCPH` char(11) DEFAULT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `WebEntry` smallint(1) unsigned DEFAULT '0',
  `UID` smallint(5) unsigned DEFAULT NULL,
  `test` smallint(1) unsigned DEFAULT '0',
  `Dep_Keeper_Flock_No` int(10) unsigned DEFAULT NULL,
  `Dest_Keeper_Flock_No` int(10) unsigned DEFAULT NULL,
  `Reader_Lot_No` varchar(20) DEFAULT NULL,
  `Buyer_Inv_No` int(10) unsigned DEFAULT NULL,
  `Buyer_Acc_No` varchar(10) DEFAULT NULL,
  `Seller_Pay_No` int(10) unsigned DEFAULT NULL,
  `Seller_Acc_No` varchar(10) DEFAULT NULL,
  `AllEID` tinyint(1) DEFAULT NULL,
  `Visually_read` tinyint(1) unsigned DEFAULT '0',
  `batch_mark` varchar(20) DEFAULT NULL,
  `fci_declaration` tinyint(4) DEFAULT NULL,
  `description_id` tinyint(4) DEFAULT '0',
  `receiving_keeper_id` int(11) DEFAULT NULL,
  `bpex_movement_type_id` int(11) DEFAULT NULL,
  `sent_to_bpex` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`ReadLocationCPH`,`LotDate`,`Lot_No`,`MovementType`,`Species`),
  KEY `TblMovementTypesTblSSLots` (`MovementType`),
  KEY `SAMS_Movement` (`SAMS_Movement_Reference`),
  KEY `departure` (`DepartureCPH`) USING BTREE,
  KEY `destination` (`DestinationCPH`) USING BTREE,
  KEY `uid` (`UID`),
  KEY `Species` (`Species`),
  CONSTRAINT `FK_tblsslots_4` FOREIGN KEY (`MovementType`) REFERENCES `tblmovementtypes` (`MovementType`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table tblwebservicelog
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tblwebservicelog`;

CREATE TABLE `tblwebservicelog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `operation` varchar(45) DEFAULT NULL,
  `application_name` varchar(45) DEFAULT NULL,
  `application_version` varchar(45) DEFAULT NULL,
  `schema_version` varchar(45) DEFAULT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  `successful` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `error_message` varchar(50) DEFAULT '',
  `filename` varchar(150) DEFAULT NULL,
  `old_filename` varchar(150) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table tempitemread
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tempitemread`;

CREATE TABLE `tempitemread` (
  `Lot_No` varchar(20) NOT NULL,
  `ReadLocationCPH` char(11) DEFAULT '99/999/9999',
  `LotDate` date NOT NULL,
  `EIDHexNumber` char(16) NOT NULL DEFAULT '',
  `MovementType` int(10) DEFAULT '10',
  `Timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `AnimalEID` bigint(12) unsigned DEFAULT '0',
  `uid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Filename` varchar(256) NOT NULL DEFAULT '',
  `Country_code` int(3) unsigned DEFAULT NULL,
  `Flock_tag` int(1) unsigned DEFAULT '0',
  `Tag_count` int(3) unsigned DEFAULT '1',
  `Species` tinyint(3) unsigned NOT NULL DEFAULT '4',
  PRIMARY KEY (`Lot_No`,`LotDate`,`EIDHexNumber`,`uid`,`Filename`) USING BTREE,
  KEY `FK_tempitemread_1` (`Lot_No`,`LotDate`,`uid`,`Filename`),
  KEY `Animaleid` (`AnimalEID`),
  KEY `country_code` (`Country_code`),
  CONSTRAINT `FK_tempitemread_1` FOREIGN KEY (`Lot_No`, `LotDate`, `uid`, `Filename`) REFERENCES `templots` (`Lot_No`, `LotDate`, `uid`, `Filename`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `after_insert_temp_item` AFTER INSERT ON `tempitemread` FOR EACH ROW BEGIN
	UPDATE 
		templots 
	SET 
		templots.Qty_Reads = templots.Qty_Reads + NEW.Tag_Count
	WHERE
		templots.Lot_No = NEW.Lot_No AND
		templots.LotDate = NEW.LotDate AND
		templots.uid = NEW.uid AND
		templots.Filename = NEW.Filename;
END */;;
/*!50003 SET SESSION SQL_MODE="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `after_update_temp_item` AFTER UPDATE ON `tempitemread` FOR EACH ROW BEGIN
	UPDATE 
		templots 
	SET 
		templots.Qty_Reads = Qty_Reads + (NEW.Tag_Count - OLD.Tag_Count) 
	WHERE
		templots.Lot_No = OLD.Lot_No AND
		templots.LotDate = OLD.LotDate AND
		templots.uid = OLD.uid AND
		templots.Filename = OLD.Filename;
END */;;
/*!50003 SET SESSION SQL_MODE="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `after_delete_temp_item` AFTER DELETE ON `tempitemread` FOR EACH ROW BEGIN
	UPDATE 
		templots 
	SET 
		templots.Qty_Reads = templots.Qty_Reads - OLD.Tag_Count
	WHERE
		templots.Lot_No = OLD.Lot_No AND
		templots.LotDate = OLD.LotDate AND
		templots.uid = OLD.uid AND
		templots.Filename = OLD.Filename;
END */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table templots
# ------------------------------------------------------------

DROP TABLE IF EXISTS `templots`;

CREATE TABLE `templots` (
  `SAMS_Movement_Reference` int(10) NOT NULL AUTO_INCREMENT,
  `foreign_reference` varchar(32) DEFAULT NULL,
  `Lot_No` varchar(20) NOT NULL,
  `ReadLocationCPH` char(11) DEFAULT NULL,
  `LotDate` date NOT NULL DEFAULT '0000-00-00',
  `ArrivalDate` date DEFAULT NULL,
  `DepartureDate` date DEFAULT NULL,
  `MovementType` int(10) DEFAULT '10',
  `Species` tinyint(3) unsigned NOT NULL DEFAULT '4',
  `transport_details_id` int(11) DEFAULT NULL,
  `Qty_Sheep` int(6) unsigned DEFAULT NULL,
  `Qty_DOA` int(11) DEFAULT NULL,
  `Qty_Reads` int(11) NOT NULL DEFAULT '0',
  `DepartureCPH` char(11) DEFAULT NULL,
  `DestinationCPH` char(11) DEFAULT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `WebEntry` smallint(1) unsigned DEFAULT '1',
  `uid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Filename` varchar(256) NOT NULL DEFAULT '',
  `test` smallint(1) DEFAULT '1',
  `Dep_Keeper_Flock_No` int(10) unsigned DEFAULT NULL,
  `Dest_Keeper_Flock_No` int(10) unsigned DEFAULT NULL,
  `Reader_Lot_No` varchar(20) DEFAULT NULL,
  `Buyer_Inv_No` int(10) unsigned DEFAULT NULL,
  `Buyer_Acc_No` varchar(10) DEFAULT NULL,
  `Seller_Pay_No` int(10) unsigned DEFAULT NULL,
  `Seller_Acc_No` varchar(10) DEFAULT NULL,
  `AllEID` tinyint(1) DEFAULT NULL,
  `Visually_read` int(1) unsigned DEFAULT '0',
  `batch_mark` varchar(20) DEFAULT NULL,
  `fci_declaration` tinyint(4) DEFAULT NULL,
  `description_id` tinyint(4) DEFAULT NULL,
  `receiving_keeper_id` int(11) DEFAULT NULL,
  `bpex_movement_type_id` int(11) DEFAULT NULL,
  `sent_to_bpex` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`Lot_No`,`LotDate`,`uid`,`Filename`) USING BTREE,
  KEY `SAMX_Movement_Reference` (`SAMS_Movement_Reference`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table transport_details
# ------------------------------------------------------------

DROP TABLE IF EXISTS `transport_details`;

CREATE TABLE `transport_details` (
  `transport_details_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` varchar(14) DEFAULT NULL COMMENT 'Could be either a transport ID or a registration number',
  `dep_assurance_no` varchar(20) DEFAULT NULL COMMENT 'Consigning keeper details',
  `dep_name` varchar(60) DEFAULT NULL,
  `dep_business` varchar(60) DEFAULT NULL,
  `dep_add2` varchar(60) DEFAULT NULL,
  `dep_add3` varchar(60) DEFAULT NULL,
  `dep_add4` varchar(60) DEFAULT NULL,
  `dep_postcode` varchar(9) DEFAULT NULL,
  `dep_tel` varchar(16) DEFAULT NULL,
  `dep_email` varchar(100) DEFAULT NULL,
  `dep_slap` varchar(8) DEFAULT NULL,
  `dest_assurance_no` varchar(20) DEFAULT NULL COMMENT 'Receiving keeper details',
  `dest_name` varchar(60) DEFAULT NULL,
  `dest_business` varchar(60) DEFAULT NULL,
  `dest_add2` varchar(60) DEFAULT NULL,
  `dest_add3` varchar(60) DEFAULT NULL,
  `dest_add4` varchar(60) DEFAULT NULL,
  `dest_postcode` varchar(9) DEFAULT NULL,
  `dest_tel` varchar(16) DEFAULT NULL,
  `dest_email` varchar(100) DEFAULT NULL,
  `dest_slap` varchar(8) DEFAULT NULL,
  `haulier_name` varchar(60) DEFAULT NULL,
  `haulier_business` varchar(60) DEFAULT NULL,
  `haulier_permit_number` varchar(10) DEFAULT NULL,
  `expected_duration` int(11) DEFAULT NULL,
  `time_of_loading` datetime DEFAULT NULL,
  `time_of_unloading` datetime DEFAULT NULL,
  `time_of_departure` time DEFAULT NULL,
  `individual_ids` varchar(1000) DEFAULT NULL COMMENT 'Originally started as a varchar 255 but vion claim to be moving sometimes up to 70 breeding pigs',
  `birth_cph` char(11) DEFAULT NULL,
  `ID_type` varchar(8) DEFAULT NULL,
  `driver_instructions` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`transport_details_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
