CREATE TABLE `ikhtisos` (				//specialities
  `id_ikhtisos` int(11) NOT NULL AUTO_INCREMENT,	//old_id
  `nomi_ikhtisos` varchar(255) DEFAULT NULL,		//name
  `nomi_ikhtisos_kutoh` varchar(100) DEFAULT NULL,	//short_name
  `ramzi_ikhtisos` varchar(100) DEFAULT NULL,		//code
  `id_kafedra` int(11) DEFAULT NULL,			
  `status` tinyint(4) DEFAULT '2',			//is_active
  `id_fakultet` tinyint(2) DEFAULT '1',			//faculty_id
  PRIMARY KEY (`id_ikhtisos`),
  KEY `id_kafedra` (`id_kafedra`),
  CONSTRAINT `ikhtisos_ibfk_1` FOREIGN KEY (`id_kafedra`) REFERENCES `kafedra` (`id_kafedra`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

/*Data for the table `ikhtisos` */

insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (1,'Технология ва низоми иттилооти (дар нақшакашӣ ва истеҳсолот)','ТНИ','1-40010201',1,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (2,'Технология. Информатика','ИНФ','1-02060201',1,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (3,'Мошинҳо ва дастгоҳҳои истеҳсоли маводи хӯрока','МД','1-360901',2,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (4,'Мошинҳо ва дастгоҳҳои истеҳсолоти химиявӣ ва корхонаҳои масолеҳи сохтмон','МС','1-360701',2,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (5,'Технологияи нигаҳдорӣ ва коркарди ашёи хоми ғизоӣ','Т','1-490101',2,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (6,'Технологияи масолеҳи сохтмонӣ дар асоси моддаҳои часпак','ТМСЧ','1-48010107',2,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (7,'Саноати борпечонӣ (Технология ва таҷҳизоти саноати борпечонӣ)','СБ','1-362002',2,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (8,'Таркиб (тарҳрезӣ) ва технологияи маҳсулоти дӯзандагӣ','ТД','1-500102',2,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (9,'Технологияи вакуумӣ ва компрессорӣ','ТВК','1-362004',2,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (10,'Иқтисодиёт ва идора дар корхонаҳои саноатӣ','ИҚТ','1-250107-11',3,1,2);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (11,'Иқтисодиёт ва ташкили истеҳсолот(саноати хӯрокворӣ)','ИСХ','1-270101-20',3,1,2);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (12,'Математика','М','00000',3,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (13,'Физика','Ф','1-02050404',3,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (14,'Химия (фоъолияти илмию педагогӣ)','Х','1-310501-02',3,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (15,'Педагогикаи иҷтимоӣ','ПИ','00000',3,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (16,'Биология (Биотехнология)','Б','1-310101-03',3,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (17,'МТХ','МТХ','1-360901',2,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (18,'Технологияи химия','ТХ','00000',2,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (19,'Таъминоти техникии равандҳои саноати хоҷагии деҳот','ТДХ','1-740601',2,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (20,'Технологияи ғизои функсионалӣ ва кӯдакона','ТФК','1-49010108',2,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (21,'Маркетинги корхонаҳои саноатӣ',NULL,'1-26020307',NULL,1,2);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (22,'Технология ва низоми иттилоотӣ (дар иқтисодиёт)','ТНИИ','1-400102.02',NULL,1,2);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (23,'Коркарди бадеии чӯб','ЧӮБ','1-15020103 ',NULL,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (24,'Метрология, стандарткунонӣ ва сертификатсия (саноати хӯрокворӣ)','МСС','1-54010105',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (25,'Молия ва қарз','МҚ','1-250104',3,1,2);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (26,'Менеҷменти молиявӣ','ММ','1-25010410',NULL,1,2);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (27,'Сафоли бадеӣ','САФОЛ','1-15020101',NULL,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (28,'Технологияи сафоли сохтмонӣ ва функсионалии тунук','ТСС','1-48010109',NULL,2,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (30,'Технологияи зеҳнии компютерии ҳифзи иттилоот','ТЗКХИ','1-400301.02',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (31,'Технологияи зеҳнии компютерии реинжиниринг ва бизнес-равандҳо','ТЗР','1-40030103',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (32,'Технологияи зеҳнии компютерии таълими фосилагӣ','ТЗФ','1-40030104',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (33,'Тарҳрезӣ ва технологияи маҳсулоти дӯзандагӣ',NULL,'1-500102',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (34,'Технология. Равоншиноси раҳнамоии касбӣ','ТР','1-020602-02',NULL,1,2);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (35,'Математика. Физика','МФ','1-020503.01',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (36,'Математикаи амалӣ (фаъолияти илмию педагогӣ)','МА','1-310303.02',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (37,'Физика. Эҷодиёти техникӣ','ЭТ','1-020504-04',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (38,'Технология (меҳнати техникӣ). Эҷодиёти техникӣ','ТЭТ','1-020602-08',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (39,'Технология (хизматрасонӣ). Педагогикаи иҷтимоӣ','ТПИ','1-020602-06',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (40,'География. Иқтисодиёт','ГИ','1-020405-03',NULL,1,2);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (41,'Химия. Биология','ХБ','1-020406-01',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (42,'Истеҳсол, нигоҳдорӣ ва коркарди маҳсулоти растанипарварӣ (аз рӯи самтҳо)',NULL,'1-740206',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (43,'Физика. Эҷодиёти техникӣ','ФЭТ','1-02050404',NULL,1,1);
insert  into `ikhtisos`(`id_ikhtisos`,`nomi_ikhtisos`,`nomi_ikhtisos_kutoh`,`ramzi_ikhtisos`,`id_kafedra`,`status`,`id_fakultet`) values (44,'Маркетинги корхонаҳои саноатӣ','МКС','1-26020307',NULL,2,1);
