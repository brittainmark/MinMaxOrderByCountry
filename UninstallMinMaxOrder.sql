# Uninstall script for Minimum Maximum Order By Country
# 
# created by Mark Brittain. 
# 
# Donations via paypal to info@inner-light.co.uk
#
SET @configuration_group_id = 0;
SELECT configuration_group_id INTO @configuration_group_id FROM configuration WHERE configuration_key = 'MIN_MAX_ORDER_VERSION' LIMIT 1; 
DELETE FROM configuration WHERE configuration_group_id = @configuration_group_id AND configuration_group_id <> 0;
DELETE FROM configuration_group WHERE configuration_group_id = @configuration_group_id AND configuration_group_id <> 0;
DELETE FROM admin_pages WHERE page_key = 'locationtaxes_min_max_order';
DROP TABLE IF EXISTS min_max_order;
