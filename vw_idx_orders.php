<?php /* INVENTORY $Id: vw_idx_orders.php,v 1.2 2004/08/04 07:51:25 dylan_cuthbert Exp $ */

error_reporting( E_ALL );

global $inventory_view_mode, $dPconfig;

$inventory_view_mode = "orders";

include("{$dPconfig['root_dir']}/modules/inventory/vw_idx_items.php");

?>
