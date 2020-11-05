DROP TABLE IF EXISTS files;
CREATE TABLE files
( 
    `id` integer NOT NULL auto_increment,
    `created` int UNSIGNED  NOT NULL, 
    `accessed` int UNSIGNED  NOT NULL, 
    `title` varchar(255) NOT NULL, 
    `user_id` int UNSIGNED  NOT NULL,
    `category_id` int UNSIGNED  NOT NULL, 
    `state` tinyint UNSIGNED  NOT NULL, 
    `path` varchar(255) NOT NULL, 
    `key` varchar(32) NOT NULL, 
    `requested` int UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `key` (`key`),
    KEY `accessed` (`accessed`),
    KEY `title` (`title`),
    KEY `user_id_state` (`user_id`, `state`),
    KEY `requested` (`requested`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';

ALTER TABLE files ADD `params` tinytext NOT NULL;

DROP TABLE IF EXISTS fileprop;
CREATE TABLE fileprop
( 
    `id` integer NOT NULL auto_increment,
    `file_id` int UNSIGNED NOT NULL,
    `description` tinytext NOT NULL,
    PRIMARY KEY (`id`),
    KEY `file_id` (`file_id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';

DROP TABLE IF EXISTS tags;
CREATE TABLE tags
( 
    `id` integer NOT NULL auto_increment,
    `tag` varchar(128) NOT NULL,
    `hash` varchar(32) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `hash` (`hash`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';

DROP TABLE IF EXISTS filetags;
CREATE TABLE filetags
( 
    `id` integer NOT NULL auto_increment,
    `file_id` int UNSIGNED NOT NULL,
    `tag_id` int UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `file_id` (`file_id`),
    KEY `tag_id` (`tag_id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';


DROP TABLE IF EXISTS `filerate`;
CREATE TABLE IF NOT EXISTS `filerate` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `rateval` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`file_id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';
