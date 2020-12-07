
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `auth_key` varchar(100) NOT NULL DEFAULT '',
  `confirmed_at` datetime DEFAULT NULL,
  `blocked_at` datetime DEFAULT NULL,
  `registration_ip` varchar(40) DEFAULT '' COMMENT 'Stores ip v4 or ip v6',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `timezone` varchar(40) DEFAULT '',
  `profil` text NOT NULL DEFAULT '{}' COMMENT 'Json encoded profil',
  PRIMARY KEY  (`id`),
  INDEX `username` (`username`),
  UNIQUE INDEX `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
