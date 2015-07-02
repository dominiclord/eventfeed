CREATE TABLE `options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `options` (`name`, `value`) VALUES
  ('speed',15000);

CREATE TABLE `posts` (
  `id` varchar(40) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `timestamp_modified` int(11) NOT NULL,
  `author` varchar(128) NOT NULL,
  `text` text NOT NULL,
  `image` varchar(128) NOT NULL,
  `status` varchar(64) NOT NULL,
  `type` varchar(64) NOT NULL,
  UNIQUE (`id`),
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`,`status`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
