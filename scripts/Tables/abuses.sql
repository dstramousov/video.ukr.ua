DROP TABLE IF EXISTS abuses;
CREATE TABLE `abuses` (
  `id` integer NOT NULL auto_increment,
  `file_id` int(11) NOT NULL,
  `reason` smallint(5) unsigned NOT NULL,
  `descr` tinytext,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';
