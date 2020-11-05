DROP TABLE IF EXISTS `clickhistory`;
CREATE TABLE IF NOT EXISTS `clickhistory` (
  `id` int(11) NOT NULL auto_increment,
  `day` date NOT NULL,
  `count_see_it` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `day` (`day`,`count_see_it`,`file_id`),
  KEY `count_see_it_day` (`count_see_it`,`day`),
  KEY `file_id` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';



DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `checkit` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) default NULL,
  `file_id` int(11) NOT NULL,
  `body` mediumtext NOT NULL,
  `rate` int(11) NOT NULL default '0',
  `stay` enum('1','2','3') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';
