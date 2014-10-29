

CREATE TABLE `user_type` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `user_type_id` tinyint(4) NOT NULL DEFAULT '1',
  `fullname` varchar(100) NOT NULL,
  `email` varchar(250) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` char(40) NOT NULL,
  `salt` char(40) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `inactive` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `uuid_UNIQUE` (`uuid`),
  KEY `fk_user_type_id` (`user_type_id`),
  KEY `idx_user_deleted` (`deleted`),
  CONSTRAINT `fk_user_type_id` FOREIGN KEY (`user_type_id`) REFERENCES `user_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `user_type`
(`id`,
 `name`)
  VALUES
  (1,'User');
INSERT INTO `user_type`
(`id`,
 `name`)
  VALUES
  (99,'Administrator');