
ALTER TABLE `bitcoin_donations` ADD `staffID` INT( 11 ) NOT NULL AFTER `ID`;

UPDATE `bitcoin_donations` SET  `staffID`=47234;


