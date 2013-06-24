
--
-- Table structure for table `users_languages`
--

DROP TABLE IF EXISTS `users_languages`;
CREATE TABLE IF NOT EXISTS `users_languages` (
  `UserID` int(10) NOT NULL,
  `LangID` smallint(3) NOT NULL,
  PRIMARY KEY (`UserID`,`LangID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE IF NOT EXISTS `languages` (
  `ID` smallint(3) NOT NULL AUTO_INCREMENT,
  `language` VARCHAR(64) NOT NULL,
  `code` char(2) NOT NULL,
  `flag_cc` char(2) DEFAULT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=137 ;

ALTER TABLE `languages` CHANGE `language` `language` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `gazelle`.`languages` ADD UNIQUE (`code`);

--
-- Dumping data for table `languages`
--

INSERT IGNORE INTO `languages` (`ID`, `language`, `code`, `flag_cc`, `active`) VALUES
(1, 'English', 'en', 'en', '1'),
(2, 'Afar', 'aa', NULL, '0'),
(3, 'Abkhazian', 'ab', NULL, '0'),
(4, 'Afrikaans', 'af', NULL, '0'),
(5, 'Amharic', 'am', NULL, '0'),
(6, 'Arabic', 'ar', NULL, '0'),
(7, 'Assamese', 'as', NULL, '0'),
(8, 'Aymara', 'ay', NULL, '0'),
(9, 'Azerbaijani', 'az', NULL, '0'),
(10, 'Bashkir', 'ba', NULL, '0'),
(11, 'Byelorussian', 'be', NULL, '0'),
(12, 'Bulgarian', 'bg', NULL, '0'),
(13, 'Bihari', 'bh', NULL, '0'),
(14, 'Bislama', 'bi', NULL, '0'),
(15, 'Bengali/Bangla', 'bn', NULL, '0'),
(16, 'Tibetan', 'bo', NULL, '0'),
(17, 'Breton', 'br', NULL, '0'),
(18, 'Catalan', 'ca', NULL, '0'),
(19, 'Corsican', 'co', NULL, '0'),
(20, 'Czech', 'cs', NULL, '0'),
(21, 'Welsh', 'cy', NULL, '0'),
(22, 'Danish', 'da', NULL, '0'),
(23, 'German', 'de', NULL, '0'),
(24, 'Bhutani', 'dz', NULL, '0'),
(25, 'Greek', 'el', 'gr', '1'),
(26, 'Esperanto', 'eo', NULL, '0'),
(27, 'Spanish', 'es', 'es', '1'),
(28, 'Estonian', 'et', NULL, '0'),
(29, 'Basque', 'eu', NULL, '0'),
(30, 'Persian', 'fa', NULL, '0'),
(31, 'Finnish', 'fi', NULL, '0'),
(32, 'Fiji', 'fj', NULL, '0'),
(33, 'Faeroese', 'fo', NULL, '0'),
(34, 'French', 'fr', 'fr', '1'),
(35, 'Frisian', 'fy', NULL, '0'),
(36, 'Irish', 'ga', NULL, '0'),
(37, 'Scots/Gaelic', 'gd', NULL, '0'),
(38, 'Galician', 'gl', NULL, '0'),
(39, 'Guarani', 'gn', NULL, '0'),
(40, 'Gujarati', 'gu', NULL, '0'),
(41, 'Hausa', 'ha', NULL, '0'),
(42, 'Hindi', 'hi', 'in', '1'),
(43, 'Croatian', 'hr', NULL, '0'),
(44, 'Hungarian', 'hu', NULL, '0'),
(45, 'Armenian', 'hy', NULL, '0'),
(46, 'Interlingua', 'ia', NULL, '0'),
(47, 'Interlingue', 'ie', NULL, '0'),
(48, 'Inupiak', 'ik', NULL, '0'),
(49, 'Indonesian', 'in', NULL, '0'),
(50, 'Icelandic', 'is', NULL, '0'),
(51, 'Italian', 'it', 'it', '1'),
(52, 'Hebrew', 'iw', NULL, '0'),
(53, 'Japanese', 'ja', NULL, '0'),
(54, 'Yiddish', 'ji', NULL, '0'),
(55, 'Javanese', 'jw', NULL, '0'),
(56, 'Georgian', 'ka', NULL, '0'),
(57, 'Kazakh', 'kk', NULL, '0'),
(58, 'Greenlandic', 'kl', NULL, '0'),
(59, 'Cambodian', 'km', NULL, '0'),
(60, 'Kannada', 'kn', NULL, '0'),
(61, 'Korean', 'ko', NULL, '0'),
(62, 'Kashmiri', 'ks', NULL, '0'),
(63, 'Kurdish', 'ku', NULL, '0'),
(64, 'Kirghiz', 'ky', NULL, '0'),
(65, 'Latin', 'la', NULL, '0'),
(66, 'Lingala', 'ln', NULL, '0'),
(67, 'Laothian', 'lo', NULL, '0'),
(68, 'Lithuanian', 'lt', NULL, '0'),
(69, 'Latvian/Lettish', 'lv', NULL, '0'),
(70, 'Malagasy', 'mg', NULL, '0'),
(71, 'Maori', 'mi', NULL, '0'),
(72, 'Macedonian', 'mk', NULL, '0'),
(73, 'Malayalam', 'ml', NULL, '0'),
(74, 'Mongolian', 'mn', NULL, '0'),
(75, 'Moldavian', 'mo', NULL, '0'),
(76, 'Marathi', 'mr', NULL, '0'),
(77, 'Malay', 'ms', NULL, '0'),
(78, 'Maltese', 'mt', NULL, '0'),
(79, 'Burmese', 'my', NULL, '0'),
(80, 'Nauru', 'na', NULL, '0'),
(81, 'Nepali', 'ne', NULL, '0'),
(82, 'Dutch', 'nl', 'nl', '1'),
(83, 'Norwegian', 'no', NULL, '0'),
(84, 'Occitan', 'oc', NULL, '0'),
(85, '(Afan)/Oromoor/Oriya', 'om', NULL, '0'),
(86, 'Punjabi', 'pa', NULL, '0'),
(87, 'Polish', 'pl', NULL, '0'),
(88, 'Pashto/Pushto', 'ps', NULL, '0'),
(89, 'Portuguese', 'pt', NULL, '0'),
(90, 'Quechua', 'qu', NULL, '0'),
(91, 'Rhaeto-Romance', 'rm', NULL, '0'),
(92, 'Kirundi', 'rn', NULL, '0'),
(93, 'Romanian', 'ro', 'ro', '1'),
(94, 'Russian', 'ru', NULL, '0'),
(95, 'Kinyarwanda', 'rw', NULL, '0'),
(96, 'Sanskrit', 'sa', NULL, '0'),
(97, 'Sindhi', 'sd', NULL, '0'),
(98, 'Sangro', 'sg', NULL, '0'),
(99, 'Serbo-Croatian', 'sh', NULL, '0'),
(100, 'Singhalese', 'si', NULL, '0'),
(101, 'Slovak', 'sk', NULL, '0'),
(102, 'Slovenian', 'sl', NULL, '0'),
(103, 'Samoan', 'sm', NULL, '0'),
(104, 'Shona', 'sn', NULL, '0'),
(105, 'Somali', 'so', NULL, '0'),
(106, 'Albanian', 'sq', NULL, '0'),
(107, 'Serbian', 'sr', NULL, '0'),
(108, 'Siswati', 'ss', NULL, '0'),
(109, 'Sesotho', 'st', NULL, '0'),
(110, 'Sundanese', 'su', NULL, '0'),
(111, 'Swedish', 'sv', 'se', '1'),
(112, 'Swahili', 'sw', NULL, '0'),
(113, 'Tamil', 'ta', NULL, '0'),
(114, 'Tegulu', 'te', NULL, '0'),
(115, 'Tajik', 'tg', NULL, '0'),
(116, 'Thai', 'th', NULL, '0'),
(117, 'Tigrinya', 'ti', NULL, '0'),
(118, 'Turkmen', 'tk', NULL, '0'),
(119, 'Tagalog', 'tl', NULL, '0'),
(120, 'Setswana', 'tn', NULL, '0'),
(121, 'Tonga', 'to', NULL, '0'),
(122, 'Turkish', 'tr', 'tr', '1'),
(123, 'Tsonga', 'ts', NULL, '0'),
(124, 'Tatar', 'tt', NULL, '0'),
(125, 'Twi', 'tw', NULL, '0'),
(126, 'Ukrainian', 'uk', NULL, '0'),
(127, 'Urdu', 'ur', NULL, '0'),
(128, 'Uzbek', 'uz', NULL, '0'),
(129, 'Vietnamese', 'vi', NULL, '0'),
(130, 'Volapuk', 'vo', NULL, '0'),
(131, 'Wolof', 'wo', NULL, '0'),
(132, 'Xhosa', 'xh', NULL, '0'),
(133, 'Yoruba', 'yo', NULL, '0'),
(134, 'Chinese', 'zh', 'cn', '1'),
(135, 'Zulu', 'zu', NULL, '0');







 
