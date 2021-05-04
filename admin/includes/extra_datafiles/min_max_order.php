<?php
//Extra file for admin of minmax order values by country
define('FILENAME_MIN_MAX_ORDER','min_max_order');
define('TABLE_MIN_MAX_ORDER', DB_PREFIX . 'min_max_order');

//saitizer settings
$sanitizer = AdminRequestSanitizer::getInstance();
$group = array(
    'min_max_description' => array('sanitizerType' => 'ALPHANUM_DASH_UNDERSCORE', 'method' => 'post'),
    'min_value' => array('sanitizerType' => 'FLOAT_VALUE_REGEX', 'method' => 'post'),
    'max_value' => array('sanitizerType' => 'FLOAT_VALUE_REGEX', 'method' => 'post'),
    'min_max_countries' => array('sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX', 'method' => 'post'),
);
$sanitizer->addComplexSanitization($group);