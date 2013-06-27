 
-- --------------------------------------------------------

ALTER TABLE `users_info` CHANGE `DisableIRC` `DisableIRC` ENUM( '0', '1' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0';

ALTER TABLE `users_info` ADD `DisableSignature` ENUM( '0', '1' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' AFTER `DisablePM` , 
ADD `DisableTorrentSig` ENUM( '0', '1' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' AFTER `DisablePM`;


