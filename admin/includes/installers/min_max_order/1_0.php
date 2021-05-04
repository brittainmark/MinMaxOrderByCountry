<?php

// Create Table products_location
$db->Execute('CREATE TABLE IF NOT EXISTS ' . TABLE_MIN_MAX_ORDER . ' (
  min_max_id int(11) NOT NULL auto_increment,
  min_value decimal(15,4) NOT NULL,
  max_value decimal(15,4) NOT NULL,
  min_max_countries varchar(370) NOT NULL,
  min_max_description varchar(32) NOT NULL,
  PRIMARY KEY (min_max_id) )
  ENGINE=MyISAM DEFAULT CHARSET=utf8;');

//
$minMaxOrder_PageExists = false;

// Attempt to use the ZC function to test for the existence of the page otherwise detect using SQL.
if (function_exists('zen_page_key_exists')) {
    $minMaxOrder_PageExists = zen_page_key_exists('locationtaxes_min_max_order');
} else {
    $minMaxOrder_PageExists_result = $db->Execute("SELECT FROM " . TABLE_ADMIN_PAGES . " WHERE page_key = 'locationtaxes_min_max_order' LIMIT 1");
    if ($minMaxOrder_PageExists_result->EOF && $minMaxOrder_PageExists_result->RecordCount() == 0) {} else {
        $minMaxOrder_PageExists = true;
    }
}

// if the admin page is not installed, then insert it using either the ZC function or straight SQL.
if (! $minMaxOrder_PageExists) {
    if ((int) $configuration_group_id > 0) {

        $page_sort_query = "SELECT MAX(sort_order) + 1 AS max_sort FROM `" . TABLE_ADMIN_PAGES . "` WHERE menu_key='configuration'";
        $page_sort = $db->Execute($page_sort_query);
        $page_sort = $page_sort->fields['max_sort'];

        zen_register_admin_page('locationtaxes_min_max_order', 'BOX_MIN_MAX_ORDER', 'FILENAME_MIN_MAX_ORDER', 'gID=' . $configuration_group_id, 'taxes', 'Y', $page_sort);

        $messageStack->add('Enabled ' . $module_name . ' Configuration Menu. Under '.BOX_HEADING_LOCATION_AND_TAXES . " menu.", 'success');
    }
}
// Initialize the variable.
$sort_order = array();

/*
 * Add Values to Products Location Configuration Group (Admin > Configuration > Products Location)
 * Identify the order in which the keys should be added for display.
 */
$sort_order = array(

    array(
        'configuration_group_id' => array(
            'value' => $configuration_group_id,
            'type' => 'integer'
        ),
        'configuration_key' => array(
            'value' => $module_constant . '_PLUGIN_CHECK',
            'type' => 'string'
        ),
        'configuration_title' => array(
            'value' => $module_name . ' (Update Check)',
            'type' => 'string'
        ),
        'configuration_value' => array(
            'value' => SHOW_VERSION_UPDATE_IN_HEADER,
            'type' => 'string'
        ),
        'configuration_description' => array(
            'value' => 'Allow version checking if Zen Cart version checking enabled<br/><br/>If false, no version checking performed.<br/>If true, then only if Zen Cart version checking is on:',
            'type' => 'string'
        ),
        'date_added' => array(
            'value' => 'NOW()',
            'type' => 'noquotestring'
        ),
        'use_function' => array(
            'value' => 'NULL',
            'type' => 'noquotestring'
        ),
        'set_function' => array(
            'value' => 'zen_cfg_select_option(array(\'true\', \'false\'),',
            'type' => 'string'
        )
    ),
    array(
        'configuration_group_id' => array(
            'value' => $configuration_group_id,
            'type' => 'integer'
        ),
        'configuration_key' => array(
            'value' => $module_constant . '_VERSION',
            'type' => 'string'
        ),
        'configuration_title' => array(
            'value' => $module_name . '<b> Version</b>',
            'type' => 'string'
        ),
        'configuration_value' => array(
            'value' => '0.0.0',
            'type' => 'string'
        ),
        'configuration_description' => array(
            'value' => $module_name . ' Version',
            'type' => 'string'
        ),
        'date_added' => array(
            'value' => 'NOW()',
            'type' => 'noquotestring'
        ),
        'use_function' => array(
            'value' => 'NULL',
            'type' => 'noquotestring'
        ),
        'set_function' => array(
            'value' => 'zen_cfg_select_option(array(\'0.0.0\'),',
            'type' => 'string'
        )
    ),
    array(
        'configuration_group_id' => array(
            'value' => $configuration_group_id,
            'type' => 'integer'
        ),
        'configuration_key' => array(
            'value' => 'DEFAULT_MIN_ORDER_AMOUNT',
            'type' => 'string'
        ),
        'configuration_title' => array(
            'value' => 'Default minimum order amount',
            'type' => 'string'
        ),
        'configuration_value' => array(
            'value' => 0,
            'type' => 'integer'
        ),
        'configuration_description' => array(
            'value' => '<br>The default minumum order amount allowed to check out<br><b>Default: 0</b>',
            'type' => 'string'
        ),
        'date_added' => array(
            'value' => 'NOW()',
            'type' => 'noquotestring'
        ),
        'use_function' => array(
            'value' => 'NULL',
            'type' => 'noquotestring'
        ),
        'set_function' => array(
            'value' => 'NULL',
            'type' => 'noquotestring'
        )
    ),
    array(
        'configuration_group_id' => array(
            'value' => $configuration_group_id,
            'type' => 'integer'
        ),
        'configuration_key' => array(
            'value' => 'DEFAULT_MAX_ORDER_AMOUNT',
            'type' => 'string'
        ),
        'configuration_title' => array(
            'value' => 'Default maximum order amount',
            'type' => 'string'
        ),
        'configuration_value' => array(
            'value' => 0,
            'type' => 'integer'
        ),
        'configuration_description' => array(
            'value' => '<br>The default maximum order amount that will be allowed to check out. 0 (zero) will allow any amount<br><b>Default: 0</b>',
            'type' => 'string'
        ),
        'date_added' => array(
            'value' => 'NOW()',
            'type' => 'noquotestring'
        ),
        'use_function' => array(
            'value' => 'NULL',
            'type' => 'noquotestring'
        ),
        'set_function' => array(
            'value' => 'NULL',
            'type' => 'noquotestring'
        )
    ),
    array(
        'configuration_group_id' => array(
            'value' => $configuration_group_id,
            'type' => 'integer'
        ),
        'configuration_key' => array(
            'value' => 'MIN_MAX_IGNORE_CUSTOMER_IDS',
            'type' => 'string'
        ),
        'configuration_title' => array(
            'value' => 'Customer ID\'s to Ignore Min Max conditions',
            'type' => 'string'
        ),
        'configuration_value' => array(
            'value' => 0,
            'type' => 'integer'
        ),
        'configuration_description' => array(
            'value' => '<br>A comma seperated list of customer id\'s that will ignore the min max conditions<br><b>Default: </b>',
            'type' => 'string'
        ),
        'date_added' => array(
            'value' => 'NOW()',
            'type' => 'noquotestring'
        ),
        'use_function' => array(
            'value' => 'NULL',
            'type' => 'noquotestring'
        ),
        'set_function' => array(
            'value' => 'NULL',
            'type' => 'noquotestring'
        )
    ),
);
foreach ($sort_order as $config_key => $config_item) {

    $sql = "INSERT IGNORE INTO " . TABLE_CONFIGURATION . " (configuration_group_id, configuration_key, configuration_title, configuration_value, configuration_description, sort_order, date_added, use_function, set_function)
          VALUES (:configuration_group_id:, :configuration_key:, :configuration_title:, :configuration_value:, :configuration_description:, :sort_order:, :date_added:, :use_function:, :set_function:)
          ON DUPLICATE KEY UPDATE configuration_group_id = :configuration_group_id:, sort_order = :sort_order:";
    $sql = $db->bindVars($sql, ':configuration_group_id:', $config_item['configuration_group_id']['value'], $config_item['configuration_group_id']['type']);
    $sql = $db->bindVars($sql, ':configuration_key:', $config_item['configuration_key']['value'], $config_item['configuration_key']['type']);
    $sql = $db->bindVars($sql, ':configuration_title:', $config_item['configuration_title']['value'], $config_item['configuration_title']['type']);
    $sql = $db->bindVars($sql, ':configuration_value:', $config_item['configuration_value']['value'], $config_item['configuration_value']['type']);
    $sql = $db->bindVars($sql, ':configuration_description:', $config_item['configuration_description']['value'], $config_item['configuration_description']['type']);
    $sql = $db->bindVars($sql, ':sort_order:', ((int) $config_key + 1) * 10, 'integer');
    $sql = $db->bindVars($sql, ':date_added:', $config_item['date_added']['value'], $config_item['date_added']['type']);
    $sql = $db->bindVars($sql, ':use_function:', $config_item['use_function']['value'], $config_item['use_function']['type']);
    $sql = $db->bindVars($sql, ':set_function:', $config_item['set_function']['value'], $config_item['set_function']['type']);
    $db->Execute($sql);
}