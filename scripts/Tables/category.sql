
DROP TABLE IF EXISTS `filecategory`;
CREATE TABLE IF NOT EXISTS `filecategory` (
  `id` int(11) NOT NULL auto_increment,
  `file_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `file_id` (`file_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci';


DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `status` enum('1','2') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE='utf8_general_ci' AUTO_INCREMENT=17 ;


CREATE TABLE IF NOT EXISTS  `userfiles` (
 `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
 `user_id` INT( 10 ) UNSIGNED NOT NULL ,
 `file_id` INT( 10 ) UNSIGNED NOT NULL ,
PRIMARY KEY (  `id` ) ,
KEY  `user_id` (  `user_id` ) ,
KEY  `file_id` (  `file_id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8 COLLATE =  'utf8_general_ci';

--
-- Дамп данных таблицы `category`
--

INSERT INTO `category` (`id`, `name`, `status`) VALUES
(1, 'autos_vehicles', '2'),
(2, 'comedy', '2'),
(3, 'education', '2'),
(4, 'entertainment', '2'),
(5, 'film_animation', '2'),
(6, 'gaming', '2'),
(7, 'howto_style', '2'),
(8, 'movies', '2'),
(9, 'music', '2'),
(10, 'news_politics', '2'),
(12, 'people_blogs', '2'),
(13, 'pets_animals', '2'),
(14, 'science_technology', '2'),
(15, 'sports', '2'),
(16, 'travel_events', '2');
