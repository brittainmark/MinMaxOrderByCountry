<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Brittainmark 13 April 2021  Modified in v1.5.7c $
 */
require ('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (zen_not_null($action)) {
    switch ($action) {
        // Add new min max values for country
        case 'insert':
            $min_max_description = zen_db_prepare_input($_POST['min_max_description']);
            $min_value = zen_db_prepare_input($_POST['min_value']);
            $max_value = zen_db_prepare_input($_POST['max_value']);
            $min_max_countries = strtoupper(zen_db_prepare_input($_POST['min_max_countries']));

            $db->Execute("INSERT INTO " . TABLE_MIN_MAX_ORDER . " (min_max_description, min_value, max_value,  min_max_countries)
                    VALUES ('" . zen_db_input($min_max_description) . "',
                            '" . zen_db_input($min_value) . "',
                            '" . zen_db_input($max_value) . "',
                            '" . zen_db_input($min_max_countries) . "')");
            $min_max_id =  $db->Insert_ID();
            zen_record_admin_activity('Min Max Values added: ' . $min_max_description, 'info');
            zen_redirect(zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID']) . '&mmID=' . $min_max_id);
            break;
        case 'save':
            if (isset($_GET['cID'])) {
                // Save changed constant in configuration table
                $cID = zen_db_prepare_input($_GET['cID']);
                $configuration_value = zen_db_prepare_input($_POST['configuration_value']);
                // See if there are any configuration checks
                $checks = $db->Execute("SELECT val_function FROM " . TABLE_CONFIGURATION . " WHERE configuration_id = " . (int) $cID);
                if (! $checks->EOF && $checks->fields['val_function'] != NULL) {
                    require_once ('includes/functions/configuration_checks.php');
                    if (! zen_validate_configuration_entry($configuration_value, $checks->fields['val_function'])) {
                        zen_redirect(zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&cID=' . (int) $_GET['cID'] . '&action=edit'));
                    }
                }
                $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_value = '" . zen_db_input($configuration_value) . "',
                        last_modified = now()
                    WHERE configuration_id = " . (int) $cID);
                $result = $db->Execute("SELECT configuration_key
                              FROM " . TABLE_CONFIGURATION . "
                              WHERE configuration_id = " . (int) $cID . "
                              LIMIT 1");
                zen_record_admin_activity('Configuration setting changed for ' . $result->fields['configuration_key'] . ': ' . $configuration_value, 'warning');
                zen_redirect(zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&cID=' . (int) $cID));
            } else {
                // Update min max values for country list
                $min_max_id = zen_db_prepare_input($_GET['mmID']);
                $min_max_description = zen_db_prepare_input($_POST['min_max_description']);
                $min_value = zen_db_prepare_input($_POST['min_value']);
                $max_value = zen_db_prepare_input($_POST['max_value']);
                $min_max_countries = strtoupper(zen_db_prepare_input($_POST['min_max_countries']));
                $db->Execute("UPDATE " . TABLE_MIN_MAX_ORDER . "
                    SET min_max_description = '" . zen_db_input($min_max_description) . "',
                        min_value = '" . zen_db_input($min_value) . "',
                        max_value = '" . zen_db_input($max_value) . "',
                        min_max_countries = '" . zen_db_input($min_max_countries) . "'
                    WHERE min_max_id = " . (int) $min_max_id);
                zen_record_admin_activity('Min Max Values updated: ' . $min_max_description, 'info');
                zen_redirect(zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&mmID=' . $min_max_id));
            }
            break;
        case 'deleteconfirm':
            // Delete entry from Min max table
            $min_max_id = zen_db_prepare_input($_POST['mmID']);
            $db->Execute("DELETE FROM " . TABLE_MIN_MAX_ORDER . "
                          WHERE min_max_id = " . (int) $min_max_id);
            zen_record_admin_activity('Min Max set deleted: ' . $min_max_description, 'warning');
            zen_redirect(zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page']));
            break;
    }
}
$gID = $_GET['gID'];
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta charset="<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css"
	href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script src="includes/menu.js"></script>
<script src="includes/general.js"></script>
<script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
</head>
<body onload="init()">
	<!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

	<!-- body //-->
	<div class="container-fluid">
		<h1><?php echo HEADING_TITLE; ?></h1>
		<div class="row">
			<div
				class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
				<table class="table table-hover">
					<thead>
						<tr class="dataTableHeadingRow">
							<th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></th>
							<th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></th>
							<th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
<?php
$configuration = $db->Execute("SELECT configuration_id, configuration_title, configuration_value, configuration_key, use_function
                               FROM " . TABLE_CONFIGURATION . "
                               WHERE configuration_group_id = " . (int) $gID . "
                               ORDER BY sort_order");
foreach ($configuration as $item) {
    if (zen_not_null($item['use_function'])) {
        $use_function = $item['use_function'];
        if (preg_match('/->/', $use_function)) {
            $class_method = explode('->', $use_function);
            if (! (isset(${$class_method[0]}) && is_object(${$class_method[0]}))) {
                include (DIR_WS_CLASSES . $class_method[0] . '.php');
                ${$class_method[0]} = new $class_method[0]();
            }
            $cfgValue = zen_call_function($class_method[1], $item['configuration_value'], ${$class_method[0]});
        } else {
            $cfgValue = zen_call_function($use_function, $item['configuration_value']);
        }
    } else {
        $cfgValue = $item['configuration_value'];
    }

    if (((! isset($_GET['cID']) && ! isset($_GET['mmID'])) || (isset($_GET['cID']) && ($_GET['cID'] == $item['configuration_id']))) && ! isset($cInfo) && (substr($action, 0, 3) != 'new')) {
        $cfg_extra = $db->Execute("SELECT configuration_key, configuration_description, date_added, last_modified, use_function, set_function
                                               FROM " . TABLE_CONFIGURATION . "
                                               WHERE configuration_id = " . (int) $item['configuration_id']);
        $cInfo_array = array_merge($item, $cfg_extra->fields);
        $cInfo = new objectInfo($cInfo_array);
    }

    if ((isset($cInfo) && is_object($cInfo)) && ($item['configuration_id'] == $cInfo->configuration_id)) {
        ?>
                        <tr id="defaultSelected"
							class="dataTableRowSelected"
							onclick="<?php echo 'document.location.href=\'' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'';?>"
							role="button">
    <?php
    } else {
        echo '                        <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&cID=' . $item['configuration_id'] . '&action=edit') . '\'" role="button">' . "\n";
    }
    // multilanguage support:
    // For example, in admin/includes/languages/spanish/configuration.php
    // define('CFGTITLE_STORE_NAME', 'Nombre de la Tienda');
    // define('CFGDESC_STORE_NAME', 'El nombre de mi tienda');
    if (defined('CFGTITLE_' . $item['configuration_key'])) {
        $item['configuration_title'] = constant('CFGTITLE_' . $item['configuration_key']);
    }
    if (defined('CFGDESC_' . $item['configuration_key'])) {
        $item['configuration_description'] = constant('CFGDESC_' . $item['configuration_key']);
    }
    ?>
                            <td class="dataTableContent"><?php echo $item['configuration_title']; ?></td>
							<td class="dataTableContent"><?php
    $setting = htmlspecialchars($cfgValue, ENT_COMPAT, CHARSET, TRUE);
    if (strlen($setting) > 40) {

        echo htmlspecialchars(substr($cfgValue, 0, 35), ENT_COMPAT, CHARSET, TRUE) . "...";
    } else {
        echo $setting;
    }
    ?>
                            </td>
							<td class="dataTableContent text-right">
<?php
    if ((isset($cInfo) && is_object($cInfo)) && ($item['configuration_id'] == $cInfo->configuration_id)) {
        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
    } else {
        echo '<a href="' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&cID=' . $item['configuration_id']) . '" name="link_' . $item['configuration_key'] . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
    }
    ?>&nbsp;</td>
						</tr>
              <?php
}
?>
                    </tbody>
				</table>
				<h2><?php echo TABLE_HEADING_TITLE; ?></h2>
				<table class="table table-hover">
					<thead>
						<tr class="dataTableHeadingRow">
							<th class="dataTableHeadingContent" width="50%"><?php echo TABLE_HEADING_MIN_MAX_DESCRIPTION; ?></th>
							<th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_MIN_VALUE; ?></th>
							<th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_MAX_VALUE; ?></th>
							<th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_MIN_MAX_COUNTRIES; ?></th>
							<th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
						</tr>
					</thead>
					<tbody>
                <?php
                $min_max_query_raw = "SELECT  min_max_id,  min_value, max_value, min_max_countries,  min_max_description
                                        FROM " . TABLE_MIN_MAX_ORDER . "
                                        ORDER BY min_max_id";
                $min_max_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $min_max_query_raw, $min_max_query_numrows);
                $min_max = $db->Execute($min_max_query_raw);
                foreach ($min_max as $mm_value) {
                    if (isset($_GET['mmID']) && $_GET['mmID'] ==  $mm_value['min_max_id'] && substr($action, 0, 3) != 'new') {
                        $cinfo = new objectInfo($mm_value);
                    }
                    ?>
                        <tr class="dataTableRow"
							onclick="<?php echo 'document.location.href=\'' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&mmID=' . $mm_value['min_max_id']) . '\'';?>"
							role="button">
							<td class="dataTableContent" width="50%"><?php echo zen_output_string_protected($mm_value['min_max_description']); ?></td>
							<td class="dataTableContent text-center"><?php echo $mm_value['min_value']; ?></td>
							<td class="dataTableContent text-center"><?php echo $mm_value['max_value']; ?></td>
							<td class="dataTableContent text-center"><?php echo $mm_value['min_max_countries']; ?></td>
							<td class="dataTableContent text-right"><?php
                    if (isset($_GET['mmID']) && $mm_value['min_max_id'] == $_GET['mmID']) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                    } else {
                        echo '<a href="' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&mmID=' . $mm_value['min_max_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                    }
                    ?>&nbsp;</td>

              <?php
                }
                ?>
					</tbody>
				</table>
			</div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
        <?php
        $heading = array();
        $contents = array();
        if (isset($_GET['mmID']) || $action == 'new') {
            //rpepare side box for min max countries entry

            switch ($action) {
                case 'new':
                    $heading[] = array(
                        'text' => '<h4>' . TEXT_INFO_HEADING_NEW_MIN_MAX . '</h4>'
                    );
                    $contents = array(
                        'form' => zen_draw_form('countries', FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&action=insert', 'post', 'class="form-horizontal"')
                    );
                    $contents[] = array(
                        'text' => TEXT_INFO_INSERT_INTRO
                    );
                    $contents[] = array(
                        'text' => '<br>' . zen_draw_label(TEXT_INFO_DESCRIPTION, 'min_max_description', 'class="control-label"') . zen_draw_input_field('min_max_description', '', 'class="form-control"')
                    );
                    $contents[] = array(
                        'text' => '<br>' . zen_draw_label(TEXT_INFO_MIN_VALUE, 'min_value', 'class="control-label"') . zen_draw_input_field('min_value', '', 'class="form-control"')
                    );
                    $contents[] = array(
                        'text' => '<br>' . zen_draw_label(TEXT_INFO_MAX_VALUE, 'max_value', 'class="control-label"') . zen_draw_input_field('max_value', '', 'class="form-control"')
                    );
                    $contents[] = array(
                        'text' => '<br>' . zen_draw_label(TEXT_INFO_COUNTRIES_LIST, 'min_max_countries', 'class="control-label"') . zen_draw_input_field('min_max_countries', '', 'class="form-control"')
                    );
                    $contents[] = array(
                        'align' => 'text-center',
                        'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
                    );
                    break;
                case 'edit':
                    $heading[] = array(
                        'text' => '<h4>' . TEXT_INFO_HEADING_EDIT_MIN_MAX . '</h4>'
                    );
                    $contents = array(
                        'form' => zen_draw_form('countries', FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&mmID=' . $cinfo->min_max_id . '&action=save', 'post', 'class="form-horizontal"')
                    );
                    $contents[] = array(
                        'text' => TEXT_INFO_EDIT_INTRO
                    );
                    $contents[] = array(
                        'text' => '<br>' . zen_draw_label(TEXT_INFO_DESCRIPTION, 'min_max_description', 'class="control-label"') . zen_draw_input_field('min_max_description', htmlspecialchars($cinfo->min_max_description, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"')
                    );
                    $contents[] = array(
                        'text' => '<br>' . zen_draw_label(TEXT_INFO_MIN_VALUE, 'min_value', 'class="control-label"') . zen_draw_input_field('min_value', $cinfo->min_value, 'class="form-control"')
                    );
                    $contents[] = array(
                        'text' => '<br>' . zen_draw_label(TEXT_INFO_MAX_VALUE, 'max_value', 'class="control-label"') . zen_draw_input_field('max_value', $cinfo->max_value, 'class="form-control"')
                    );
                    $contents[] = array(
                        'text' => '<br>' . zen_draw_label(TEXT_INFO_COUNTRIES_LIST, 'min_max_countries', 'class="control-label"') . zen_draw_input_field('min_max_countries', $cinfo->min_max_countries, 'class="form-control"')
                    );
                    $contents[] = array(
                        'align' => 'text-center',
                        'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&mmID=' . $cinfo->min_max_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
                    );
                    break;
                case 'delete':
                    $heading[] = array(
                        'text' => '<h4>' . TEXT_INFO_HEADING_DELETE_MIN_MAX . '</h4>'
                    );
                    $contents = array(
                        'form' => zen_draw_form('countries', FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mmID', $cinfo->min_max_id)
                    );
                    $contents[] = array(
                        'text' => TEXT_INFO_DELETE_INTRO
                    );
                    $contents[] = array(
                        'text' => '<br><b>' . zen_output_string_protected($cinfo->min_max_description) . '</b>'
                    );
                    $contents[] = array(
                        'align' => 'text-center',
                        'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_CONFIRM . '</button> <a href="' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&mmID=' . $cinfo->min_max_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
                    );
                    break;
                default:
                    if (isset($cinfo) && is_object($cinfo)) {
                        $heading[] = array(
                            'text' => '<h4>' . zen_output_string_protected($cinfo->min_max_description) . '</h4>'
                        );
                        $contents[] = array(
                            'align' => 'text-center',
                            'text' => '<a href="' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&mmID=' . $cinfo->min_max_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&mmID=' . $cinfo->min_max_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>'
                        );
                        $contents[] = array(
                            'text' => '<br>' . TEXT_INFO_DESCRIPTION . '<br>' . zen_output_string_protected($cinfo->min_max_description)
                        );
                        $contents[] = array(
                            'text' => '<br>' . TEXT_INFO_MIN_VALUE . ' ' . $cinfo->min_value
                        );
                        $contents[] = array(
                            'text' => '<br>' . TEXT_INFO_MAX_VALUE . ' ' . $cinfo->max_value
                        );
                        $contents[] = array(
                            'text' => '<br>' . TEXT_INFO_COUNTRIES_LIST . ' ' . $cinfo->min_max_countries
                        );
                    }
                    break;
            }
        } else {
            // Prepare side box for convigurstion values

            // Translation for contents
            if (defined('CFGTITLE_' . $cInfo->configuration_key)) {
                $cInfo->configuration_title = constant('CFGTITLE_' . $cInfo->configuration_key);
            }
            if (defined('CFGDESC_' . $cInfo->configuration_key)) {
                $cInfo->configuration_description = constant('CFGDESC_' . $cInfo->configuration_key);
            }

            switch ($action) {
                case 'edit':
                    $heading[] = array(
                        'text' => '<h4>' . $cInfo->configuration_title . '</h4>'
                    );

                    if ($cInfo->set_function) {
                        eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value, ENT_COMPAT, CHARSET, TRUE) . '");');
                    } else {
                        $value_field = zen_draw_input_field('configuration_value', htmlspecialchars($cInfo->configuration_value, ENT_COMPAT, CHARSET, TRUE), 'size="60" class="cfgInput form-control" autofocus');
                    }

                    $contents = array(
                        'form' => zen_draw_form('configuration', FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=save', 'post', 'class="from-horizontal"')
                    );
                    if (ADMIN_CONFIGURATION_KEY_ON == 1) {
                        $contents[] = array(
                            'text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br>'
                        );
                    }
                    $contents[] = array(
                        'text' => TEXT_INFO_EDIT_INTRO
                    );
                    $contents[] = array(
                        'text' => '<br><strong>' . $cInfo->configuration_title . '</strong><br>' . $cInfo->configuration_description . '<br>' . $value_field
                    );
                    $contents[] = array(
                        'align' => 'text-center',
                        'text' => '<br>' . '<button type="submit" name="submit' . $cInfo->configuration_key . '" class="btn btn-primary">' . IMAGE_UPDATE . '</button>' . '&nbsp;<a href="' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
                    );
                    break;
                default:
                    if (isset($cInfo) && is_object($cInfo)) {
                        $heading[] = array(
                            'text' => '<h4>' . $cInfo->configuration_title . '</h4>'
                        );
                        if (ADMIN_CONFIGURATION_KEY_ON == 1) {
                            $contents[] = array(
                                'text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br>'
                            );
                        }

                        $contents[] = array(
                            'align' => 'text-center',
                            'text' => '<a href="' . zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '" class="btn btn-primary" role="button"> ' . IMAGE_EDIT . '</a>'
                        );
                        $contents[] = array(
                            'text' => '<br>' . $cInfo->configuration_description
                        );
                        $contents[] = array(
                            'text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($cInfo->date_added)
                        );
                        if (zen_not_null($cInfo->last_modified))
                            $contents[] = array(
                                'text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($cInfo->last_modified)
                            );
                    }
                    break;
            }

        }
        if ((zen_not_null($heading)) && (zen_not_null($contents))) {
            $box = new box();
            echo $box->infoBox($heading, $contents);
        }
        ?>
            <!-- body_text_eof //-->
			</div>

			<div class="row">
				<table class="table">
					<tr>
						<td><?php echo $min_max_split->display_count($min_max_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_MIN_MAX); ?></td>
						<td class="text-right"><?php echo $min_max_split->display_links($min_max_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
					</tr>
          <?php
        if (empty($action)) {
            ?>
                    <tr>
						<td colspan="2" class="text-right"><a
							href="<?php echo zen_href_link(FILENAME_MIN_MAX_ORDER, 'gID=' . $_GET['gID'] . '&page=' . $_GET['page'] . '&action=new'); ?>"
							class="btn btn-primary" role="button"><?php echo IMAGE_NEW_MIN_MAX_COUNRTIES; ?></a></td>
					</tr>
            <?php
        }
        ?>
                </table>
			</div>
		</div>
		<!-- body_eof //-->
	</div>
	<!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
