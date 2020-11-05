DROP TABLE IF EXISTS tasks;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL auto_increment,
  `file_id` int(11) NOT NULL,
  `created` int UNSIGNED  NOT NULL, 
  `state` enum('0','1','2','3') NOT NULL,
  `params` tinytext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `file_id` (`file_id`, `state`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';

