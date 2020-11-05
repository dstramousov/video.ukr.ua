
CREATE TABLE IF NOT EXISTS `usersfolder` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `parentid` int(11) default NULL,
  `name` varchar(50) NOT NULL,
  `order` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';
