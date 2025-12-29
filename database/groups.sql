CREATE TABLE `guruh` (				//groups
  `id_guruh` int(11) NOT NULL AUTO_INCREMENT, 	//old_id
  `nomi_guruh` varchar(255) DEFAULT NULL,	//name
  `kurs` int(11) DEFAULT NULL,			//course
  `id_ikhtisos` int(11) DEFAULT '0',		//speciality_id
  `id_kafedra` int(11) DEFAULT '0',		
  `soli_khonish` int(11) DEFAULT NULL,
  `soli_dokhilshavi` int(11) DEFAULT NULL,	//wnrollment_year
  `semestr` int(11) DEFAULT NULL COMMENT 'az 1 to 8',
  `holat` int(1) DEFAULT NULL,			//status
  PRIMARY KEY (`id_guruh`),
  KEY `id_ikhtisos` (`id_ikhtisos`),
  KEY `id_kafedra` (`id_kafedra`)
) ENGINE=InnoDB AUTO_INCREMENT=318 DEFAULT CHARSET=utf8;

/*Data for the table `guruh` */

insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (147,'ТР-21 Фосилавӣ',4,34,4,2025,2022,8,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (150,'ТПИ-21 Таҳсилоти дуввум',4,39,4,2025,2022,8,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (151,'ТР-20 Таҳсилоти дуввум',4,34,4,2024,2022,3,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (152,'ТР-21 Таҳсилоти дуввум',4,34,4,2025,2022,8,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (153,'ММ-21 Таҳсилоти дуввум',4,26,4,2025,2022,8,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (154,'МҚ-21 Таҳсилоти дуввум',4,25,4,2025,2022,8,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (155,'ГИ-21 Таҳсилоти дуввум',5,40,4,2025,2022,9,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (156,'ГИ-22 Фосилавӣ',4,40,4,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (157,'ИҚТ-22А Фосилавӣ',4,10,4,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (158,'ТР-22 Фосилавӣ',4,34,4,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (159,'ИҚТ-20Б Фосилавӣ',4,11,4,2024,2020,8,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (160,'МҚ-22 Фосилавӣ',4,25,4,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (161,'ТПИ-22 Фосилавӣ',4,39,4,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (162,'МФ-22 Фосилавӣ',4,35,1,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (163,'ТЭТ-21 Таҳсилоти дуввум',4,37,1,2025,2022,8,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (164,'МФ-21 Таҳсилоти дуввум',5,35,1,2025,2022,9,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (165,'ИНФ-21 Таҳсилоти дуввум',4,2,1,2025,2022,8,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (166,'ТЭТ-22 Фосилавӣ',4,37,1,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (167,'ИНФ-22 Фосилавӣ',4,2,1,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (168,'ХБ-22 Фосилавӣ',4,41,1,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (169,'Б-22 Фосилавӣ',4,16,2,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (170,'ХБ-21 Таҳсилоти дуввум',5,41,1,2025,2022,9,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (171,'Т-22 Фосилавӣ',4,5,1,2025,2022,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (187,'ХБ-23 Фосилавӣ',3,41,0,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (188,'Б-23 фосилавӣ',3,16,2,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (189,'ГИ-23 фосилавӣ',3,40,4,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (190,'ИҚТ-23 фосилавӣ',3,10,4,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (191,'МФ-23 фосилавӣ',3,35,0,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (192,'ММ-23 фосилавӣ',3,26,4,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (193,'МҚ-23 фосилавӣ',2,25,4,2025,2023,3,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (194,'Т-23 фосилавӣ',3,5,0,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (195,'ТПИ-23 фосилавӣ',3,39,0,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (196,'МА-23 фосилавӣ',2,36,1,2025,2023,3,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (197,'ТНИ-23 фосилавӣ',2,1,0,2025,2023,3,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (198,'ИНФ-23 фосилавӣ',3,2,1,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (199,'ТЭТ-23 фосилавӣ',3,37,0,2025,2023,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (200,'Х-23 фосилавӣ',2,14,0,2025,2023,3,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (201,'ГИ-22 Таҳсилоти дуввум',4,40,4,2025,2023,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (202,'МФ-22 Таҳсилоти дуввум',4,35,0,2025,2023,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (203,'Б-22 Таҳсилоти дуввум',4,16,2,2025,2023,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (204,'ИНФ-22 Таҳсилоти дуввум',4,2,1,2025,2023,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (205,'ТПИ-22 Таҳсилоти дуввум',4,39,4,2025,2023,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (206,'ТР-22 Таҳсилоти дуввум',3,34,4,2024,2023,5,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (207,'ТНИ-22 Таҳсилоти дуввум',3,1,0,2024,2023,5,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (208,'ИҚТ-22 Таҳсилоти дуввум',4,10,4,2025,2023,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (209,'МА-22 Таҳсилоти дуввум',3,36,1,2024,2023,5,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (210,'ХБ-22 Таҳсилоти дуввум',4,41,0,2025,2023,7,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (211,'МҚ-22 Таҳсилоти дуввум',3,25,4,2024,2023,5,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (212,'ММ-22 Таҳсилоти дуввум',3,26,4,2024,2023,5,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (213,'ТЭТ-22 Таҳсилоти дуввум',3,37,1,2024,2023,5,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (242,'Б-24 Фосилавӣ',2,16,2,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (243,'ГИ-24 Фосилавӣ',2,40,4,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (244,'ИҚТ-24 Фосилавӣ',2,10,4,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (245,'МА-24 Фосилавӣ',2,36,1,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (246,'ММ-24 Фосилавӣ',1,26,4,2025,2024,2,2);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (247,'МҚ-24 Фосилавӣ',2,25,4,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (248,'ТЭТ-24 Фосилавӣ',2,37,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (249,'ТПИ-24 Фосилавӣ',2,39,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (250,'ТНИ-24 Фосилавӣ',2,1,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (251,'ИНФ-24 Фосилавӣ',2,2,1,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (252,'ТЗК-24 Фосилавӣ',2,30,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (253,'ФЭТ-24 Фосилавӣ',2,43,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (254,'Х-24 Фосилавӣ',2,14,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (255,'МСС-24 Фосилавӣ',2,24,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (256,'ГИ-23 Таҳсилоти дуввум',3,40,4,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (257,'ТПИ-23 Таҳсилоти дуввум',3,39,0,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (258,'МФ-23 Таҳсилоти дуввум',3,35,0,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (259,'МА-23 Таҳсилоти дуввум',3,36,1,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (260,'ХБ-23 Таҳсилоти дуввум',3,41,0,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (261,'ИҚТ-23 Таҳсилоти дуввум',3,10,4,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (262,'ММ-23 Таҳсилоти дуввум',3,26,4,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (263,'МҚ-23 Таҳсилоти дуввум',3,25,4,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (264,'Т-23 Таҳсилоти дуввум',3,5,0,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (265,'Б-23 Таҳсилоти дуввум',3,16,2,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (266,'ИНФ-23 Таҳсилоти дуввум',3,2,1,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (267,'ТНИИ-24 Фосилавӣ',2,1,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (268,'ХБ-24 Фосилавӣ',2,41,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (269,'Т-24 Фосилавӣ',2,5,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (270,'МФ-24 Фосилавӣ',2,35,0,2025,2024,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (271,'ИНФ-23 Идомаи таҳсил',3,2,1,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (272,'ХБ-23 Идомаи таҳсил',3,41,0,2025,2024,5,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (288,'Б-25 Фосилавӣ',1,16,2,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (289,'МФ-25 Фосилавӣ',1,35,0,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (290,'ТНИ-25 Фосилавӣ',1,1,0,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (291,'ИНФ-25 Фосилавӣ',1,2,1,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (292,'ТЗК-25 Фосилавӣ',1,30,0,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (293,'Т-25 Фосилавӣ',1,5,0,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (294,'Х-25 Фосилавӣ',1,14,0,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (295,'ХБ-25 Фосилавӣ',1,41,0,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (296,'ГИ-25 Фосилавӣ',1,40,4,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (297,'ИҚТ-25 Фосилавӣ',1,10,4,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (298,'МҚ-25 Фосилавӣ',1,25,4,2025,2025,1,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (299,'Б-24 идомаи таҳсил',2,16,2,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (300,'МФ-24 идомаи таҳсил',2,35,0,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (301,'ИНФ-24 идомаи таҳсил',2,2,1,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (302,'ХБ-24 идомаи таҳсил',2,41,0,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (303,'ТНИ-24 идомаи таҳсил',2,1,0,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (304,'ИҚТ-24 идомаи таҳсил',2,10,4,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (305,'МҚ-24 идомаи таҳсил',2,25,0,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (306,'ТЭТ-24 идомаи таҳсил',2,37,0,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (307,'ГИ-24 идомаи таҳсил',2,40,4,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (308,'ТР-24 идомаи таҳсил',2,34,0,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (309,'Б-24 Таҳсилоти дуввум',2,16,2,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (310,'МФ-24 Таҳсилоти дуввум',2,0,0,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (311,'ИНФ-24 Таҳсилоти дуввум',2,2,1,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (312,'ГИ-24 Таҳсилоти дуввум',2,40,4,2025,2025,3,1);
insert  into `guruh`(`id_guruh`,`nomi_guruh`,`kurs`,`id_ikhtisos`,`id_kafedra`,`soli_khonish`,`soli_dokhilshavi`,`semestr`,`holat`) values (313,'ТРК-24 Таҳсилоти дуввум',2,0,0,2025,2025,3,1);
