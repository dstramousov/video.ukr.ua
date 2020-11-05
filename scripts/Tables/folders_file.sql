
CREATE TABLE IF NOT EXISTS `foldersfile` (
  `id` int(11) NOT NULL auto_increment,
  `folder_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `folder_id` (`folder_id`,`file_id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';
