<?php /* INVENTORY $Id: inventory.php,v 1.1.1.1 2003/11/07 02:10:40 dylan_cuthbert Exp $ */
GLOBAL $m, $a, $project_id, $f, $min_view, $query_string, $durnTypes,$canRead,$canEdit;
/*
	inventory.php

	This file contains commen inventory list rendering code used
	by modules/inventory/index.php and others

	External used variables:

	* $min_view: hide some elements when active
	* $project_id
	* $f
	* $query_string
*/


if (empty($query_string)) {
	$query_string = "?m=$m&a=$a";
}


// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'InventoryVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'InventoryVwTab' ) !== NULL ? $AppUI->getState( 'InventoryVwTab' ) : 0;

// tabbed information boxes
$tabBox = new CTabBox( "?m=inventory", "{$AppUI->cfg['root_dir']}/modules/inventory/", $tab );
$tabBox->add( 'vw_idx_items', 'Inventory Items' );
$tabBox->add( 'vw_idx_orders', 'Orders' );
$tabBox->add( 'vw_idx_categories', 'Categories/Brands' );
$tabBox->show();



?>
