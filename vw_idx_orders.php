<?php /* INVENTORY $Id: vw_idx_orders.php,v 1.1 2003/11/28 10:16:21 dylan_cuthbert Exp $ */

error_reporting( E_ALL );

global $inventory_view_mode;

$inventory_view_mode = "orders";

include("{$dPconfig['root_dir']}/modules/inventory/vw_idx_items.php");

?>
