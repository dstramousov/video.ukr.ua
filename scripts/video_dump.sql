-- phpMyAdmin SQL Dump
-- version 2.11.5.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июн 02 2009 г., 14:21
-- Версия сервера: 5.0.18
-- Версия PHP: 5.2.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- База данных: `video_ua`
--

-- --------------------------------------------------------

--
-- Структура таблицы `convertdetails`
--

DROP TABLE IF EXISTS `convertdetails`;
CREATE TABLE IF NOT EXISTS `convertdetails` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `task_id` int(11) NOT NULL,
  `size` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251 AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `convertdetails`
--


-- --------------------------------------------------------

--
-- Структура таблицы `fileprop`
--

DROP TABLE IF EXISTS `fileprop`;
CREATE TABLE IF NOT EXISTS `fileprop` (
  `id` int(11) NOT NULL auto_increment,
  `file_id` int(10) unsigned NOT NULL,
  `description` tinytext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `file_id` (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `fileprop`
--


-- --------------------------------------------------------

--
-- Структура таблицы `files`
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL auto_increment,
  `fname` varchar(255) NOT NULL,
  `ftype` tinyint(3) unsigned NOT NULL,
  `fsize` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `accessed` int(10) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `key` varchar(32) NOT NULL,
  `requested` int(10) unsigned NOT NULL,
  `status` enum('1','2','3','4','5','6','7') NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `key` (`key`),
  KEY `accessed` (`accessed`),
  KEY `requested` (`requested`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `files`
--

INSERT INTO `files` (`id`, `fname`, `ftype`, `fsize`, `created`, `accessed`, `path`, `key`, `requested`, `status`) VALUES
(4, 'новый клип Гражданки в Омске.avi', 4, 107779, 1243928282, 1243928282, 'bd06113b2966ee5995c98d9d7df7f265', 'f1b40e693aa5f89adf7e7238d8036368', 0, '1'),
(3, 'Гражданская оборона.avi', 4, 107779, 1243867135, 1243867135, 'f523cc8612bc214eb02e6510d3f2fa3b', '007c606facd8646c7b1311d0b886b87c', 0, '1');

-- --------------------------------------------------------

--
-- Структура таблицы `filetags`
--

DROP TABLE IF EXISTS `filetags`;
CREATE TABLE IF NOT EXISTS `filetags` (
  `id` int(11) NOT NULL auto_increment,
  `file_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `file_id` (`file_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `filetags`
--


-- --------------------------------------------------------

--
-- Структура таблицы `task`
--

DROP TABLE IF EXISTS `task`;
CREATE TABLE IF NOT EXISTS `task` (
  `id` int(11) NOT NULL auto_increment,
  `file_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `status` enum('1','2','3','4','5','6','7') NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `old_path` varchar(255) NOT NULL,
  `new_path` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `file_id` (`file_id`,`created`,`status`),
  KEY `old_path` (`old_path`,`new_path`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `task`
--

INSERT INTO `task` (`id`, `file_id`, `created`, `status`, `updated`, `old_path`, `new_path`) VALUES
(3, 3, '2009-06-01 17:38:55', '1', '2009-06-02 14:17:41', 'f523cc8612bc214eb02e6510d3f2fa3b', ''),
(4, 4, '2009-06-02 10:38:03', '1', '2009-06-02 14:17:41', 'bd06113b2966ee5995c98d9d7df7f265', '');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` smallint(6) NOT NULL auto_increment,
  `login` varchar(32) NOT NULL,
  `password` char(32) NOT NULL,
  `state` smallint(6) NOT NULL,
  `fname` varchar(64) NOT NULL,
  `email` varchar(128) default NULL,
  `lname` varchar(64) default NULL,
  `ext_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `login` (`login`),
  KEY `ext_id` (`ext_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `users`
--

