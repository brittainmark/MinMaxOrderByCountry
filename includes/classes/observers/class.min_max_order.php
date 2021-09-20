<?php

/**
 * class.min_max_order_amount.php
 *
 * @copyright Copyright 2005-2007 Andrew Berezin eCommerce-Service.com
 * @copyright Portions Copyright 2003-2006 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: config.minimum_order_amount.php 1.0.1 20.09.2007 0:06 AndrewBerezin $
 */

/**
 * Observer class used to check minimum order amount
 */
class min_max_order extends base
{

    /**
     * constructor method
     *
     * Attaches our class to the ... and watches for 4 notifier events.
     */
    public function __construct()
    {
        global $zco_notifier;
        // $_SESSION['cart']->attach($this, array('NOTIFIER_CART_GET_PRODUCTS_START', 'NOTIFIER_CART_GET_PRODUCTS_END'));
        $zco_notifier->attach($this, array(
            'NOTIFY_HEADER_END_SHOPPING_CART',
            'NOTIFY_HEADER_START_CHECKOUT_SHIPPING',
            'NOTIFY_HEADER_START_CHECKOUT_PAYMENT',
            'NOTIFY_HEADER_START_CHECKOUT_CONFIRMATION',
            'NOTIFIER_CART_ADD_CART_END',
        ));
    }

    /**
     * Update Method
     *
     * Called by observed class when any of our notifiable events occur
     *
     * @param object $class
     * @param string $eventID
     */
    function update(&$class, $eventID)
    {
        global $messageStack;
        global $currencies;
        // only check if cart has items and min max has been installed
        if ($_SESSION['cart']->count_contents() > 0 && $_SESSION['cart']->get_content_type() != 'virtual' && defined('MIN_MAX_ORDER_VERSION')) {
            $process = TRUE;
            /*
             * Check if customer Id to be ignored for minimum order.
             */
            if (isset($_SESSION['customer_id']) && defined('MIN_MAX_IGNORE_CUSTOMER_IDS') && MIN_MAX_IGNORE_CUSTOMER_IDS != '' ) {
                $ignore_ids = preg_split("/,/", preg_replace('/\s*/', '', MIN_MAX_IGNORE_CUSTOMER_IDS));
                $process = ! (in_array($_SESSION['customer_id'], $ignore_ids));
            }
            if ($process) {
                $values = $this->get_min_max_country_id();
                $min_order_amount = $values['min'];
                $max_order_amount = $values['max'];
                $country_id = $values["country"];
                switch ($eventID) {
                    case 'NOTIFIER_CART_GET_PRODUCTS_END':
                    case 'NOTIFY_HEADER_END_SHOPPING_CART':
                    case 'NOTIFIER_CART_ADD_CART_END':
                        // check over min if not 0
                        if ($min_order_amount > 0 && $_SESSION['cart']->show_total() < $min_order_amount && $_SESSION['cart']->show_total() > 0) {
                            $_SESSION['valid_to_checkout'] = false;
                            $messageStack->add('shopping_cart', sprintf(TEXT_ORDER_UNDER_MIN_AMOUNT, $currencies->format($min_order_amount)) . '<br />', 'caution');
                        } elseif ($max_order_amount > 0 && $_SESSION['cart']->show_total() > $max_order_amount && $_SESSION['cart']->show_total() > 0) {
                            $_SESSION['valid_to_checkout'] = false;
                            $messageStack->add('shopping_cart', sprintf(TEXT_ORDER_OVER_MAX_AMOUNT, $currencies->format($max_order_amount)) . '<br />', 'caution');
                        }
                        break;
                    case 'NOTIFY_HEADER_START_CHECKOUT_SHIPPING':
                    case 'NOTIFY_HEADER_START_CHECKOUT_PAYMENT':
                    case 'NOTIFY_HEADER_START_CHECKOUT_CONFIRMATION':
                        if ($min_order_amount > 0 && $_SESSION['cart']->show_total() < $min_order_amount && $_SESSION['cart']->show_total() > 0) {
                            zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
                        } elseif ($max_order_amount > 0 && $_SESSION['cart']->show_total() > $max_order_amount && $_SESSION['cart']->show_total() > 0) {
                            zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
                        }
                        break;
                    default:
                        break;
                }
            }
        }
    }

    // Determin the miniumum and maximum amounts for a country
    function get_min_max_country_id()
    {
        global $db;
        $check_country_id = 0;
        // find the shipping country
        if (isset($_REQUEST['zone_country_id'])) {
            // Request for specific country shipping costs
            $check_country_id = $_REQUEST['zone_country_id'];
        } elseif (isset($_REQUEST['address_id'])) {
            // Userlogged in Request for specific delivery address
            // get from db address id extract entry_country_id table address_book
            $check_query = "SELECT entry_country_id
                    FROM " . TABLE_ADDRESS_BOOK . "
                    WHERE address_book_id = :addressBookID ;";
            $check_query = $db->bindVars($check_query, ':addressBookID', $_REQUEST['address_id'], 'integer');
            $check = $db->Execute($check_query);
            if ($check->RecordCount() > 0)
                $check_country_id = $check->fields['entry_country_id'];
        } elseif (isset($_SESSION['sendto'])) {
            // Session has delivery address
            // get from db address id extract entry_country_id table address_book
            $check_query = "SELECT entry_country_id
                    FROM " . TABLE_ADDRESS_BOOK . "
                    WHERE address_book_id = :addressBookID ;";
            $check_query = $db->bindVars($check_query, ':addressBookID', $_SESSION['sendto'], 'integer');
            $check = $db->Execute($check_query);
            if ($check->RecordCount() > 0)
                $check_country_id = $check->fields['entry_country_id'];
        } elseif (isset($_SESSION['customer_default_address_id'])) {
            // Customer is loggin in and has default address
            // get from db address id extract entry_country_id table address_book
            $check_query = "SELECT entry_country_id
                    FROM " . TABLE_ADDRESS_BOOK . "
                    WHERE address_book_id = :addressBookID ;";
            $check_query = $db->bindVars($check_query, ':addressBookID', $_SESSION['customer_default_address_id'], 'integer');
            $check = $db->Execute($check_query);
            if ($check->RecordCount() > 0)
                $check_country_id = $check->fields['entry_country_id'];
        } elseif (isset($_SESSION['cart_country_id'])) {
            // Cart has shipping address
            $check_country_id = $_SESSION['cart_country_id'];
        }
        if ($check_country_id == 0) {
            // no other information so use site default country id
            $check_country_id = STORE_COUNTRY;
        }
        // Check to see if same as last
        if (isset($_SESSION['minMaxCounrty']) && $_SESSION['minMaxCounrty'] == $check_country_id) {
            // Check to see if same as last
            $minCountryAmount = $_SESSION['MinCounrtyAmount'];
            $maxCountryAmount = $_SESSION['MaxCounrtyAmount'];
        } elseif ($check_country_id == 0) {
            // No country specified so use default
            $minCountryAmount = defined('DEFAULT_MIN_ORDER_AMOUNT') ? DEFAULT_MIN_ORDER_AMOUNT : 0;
            $maxCountryAmount = defined('DEFAULT_MAX_ORDER_AMOUNT') ? DEFAULT_MAX_ORDER_AMOUNT : 0;
        } else {
            // determin values get iso 2 code for required country
            $check_iso2 = 'SELECT  countries_iso_code_2 FROM ' . TABLE_COUNTRIES . ' WHERE countries_id = :countryId ;';
            $check_query = $db->bindVars($check_iso2, ':countryId', $check_country_id, 'integer');
            $check = $db->Execute($check_query);
            if ($check->RecordCount() > 0) {
                // country found get values from database
                $check_country_iso2 = $check->fields['countries_iso_code_2'];
                $min_max_sql = 'SELECT min_value, max_value, min_max_countries FROM ' . TABLE_MIN_MAX_ORDER . ' WHERE min_max_countries LIKE "%' . $check_country_iso2 . '%" limit 1;';
                $min_max_values = $db->Execute($min_max_sql);
                if ($min_max_values->RecordCount() > 0) {
                    $minCountryAmount = $min_max_values->fields['min_value'];
                    $maxCountryAmount = $min_max_values->fields['max_value'];
                } else {
                    // no entries so use default
                    $minCountryAmount = defined('DEFAULT_MIN_ORDER_AMOUNT') ? DEFAULT_MIN_ORDER_AMOUNT : 0;
                    $maxCountryAmount = defined('DEFAULT_MAX_ORDER_AMOUNT') ? DEFAULT_MAX_ORDER_AMOUNT : 0;
                }
            } else {
                // Cannot find country iso 2 code so use default
                $minCountryAmount = defined('DEFAULT_MIN_ORDER_AMOUNT') ? DEFAULT_MIN_ORDER_AMOUNT : 0;
                $maxCountryAmount = defined('DEFAULT_MAX_ORDER_AMOUNT') ? DEFAULT_MAX_ORDER_AMOUNT : 0;
            }
        }
        // Set session variable to save repeat processing
        $_SESSION['minMaxCounrty'] = $check_country_id;
        $_SESSION['MinCounrtyAmount'] = $minCountryAmount;
        $_SESSION['MaxCounrtyAmount'] = $maxCountryAmount;
        return array(
            "min" => $minCountryAmount,
            "max" => $maxCountryAmount,
            "country" => $check_country_id,
        );
    }
}
