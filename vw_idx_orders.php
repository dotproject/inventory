<?php /* INVENTORY $Id: index.php,v 1.4 2003/11/14 11:51:19 dylan_cuthbert Exp $ */

error_reporting( E_ALL );

global $inventory_view_mode;

$inventory_view_mode = "orders";

include("{$AppUI->cfg['root_dir']}/modules/inventory/vw_idx_items.php");

?>
