
ALTER TABLE `users_info` ADD `RunHour` TINYINT( 2 ) unsigned NOT NULL DEFAULT '0', ADD INDEX ( `RunHour` ) ;

UPDATE `users_info` SET  `RunHour`=FLOOR( RAND() * 24 );
