
CREATE TABLE IF NOT EXISTS `userplaylist` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';
