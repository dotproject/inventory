<?php
/*
 * Name:      Inventory
 * Directory: inventory
 * Version:   0.1
 * Class:     user
 * UI Name:   Inventory
 * UI Icon:
 */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'Inventory';
$config['mod_version'] = '0.1';
$config['mod_directory'] = 'inventory';
$config['mod_setup_class'] = 'CSetupInventory';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Inventory';
$config['mod_ui_icon'] = '48_my_computer.png';
$config['mod_description'] = 'A module for tracking designated hardware and software';

if (@$a == 'setup') {
	echo dPshowModuleConfig( $config );
}

class CSetupInventory {   

	function install() {
		$sql = "CREATE TABLE inventory ( " .
			"inventory_id int(11) unsigned NOT NULL auto_increment," .
			"inventory_user int(11) NOT NULL default '0'," .
			"inventory_company int(11) NOT NULL default '0'," .
			"inventory_department int(11) NOT NULL default '0'," .
			"inventory_project int(11) NOT NULL default '0'," .
			"inventory_category int(11) NOT NULL default '0'," .
			"inventory_brand int(11) NOT NULL default '0'," .
			"inventory_purchased date NOT NULL default '2001-01-01'," .
			"inventory_assign_from date NOT NULL default '2001-01-01'," .
			"inventory_assign_until date NOT NULL default '2001-01-01'," .
			"inventory_cost int(20) NOT NULL default '0'," .
			"inventory_rental_period char NOT NULL default ' '," .
			"inventory_name char(80)," .
			"inventory_serial char(40)," .
			"inventory_asset_no char(40)," .
			"inventory_costcode char(40)," .
			"inventory_description text," .
			"inventory_parent int(11) NOT NULL default '0'," .
			"PRIMARY KEY  (inventory_id)," .
			"UNIQUE KEY inventory_id (inventory_id)" .
			") TYPE=MyISAM;";
		
		db_exec( $sql ); db_error();
		
		$sql = "CREATE TABLE inventory_categories ( " .
			"inventory_category_id int(11) unsigned NOT NULL auto_increment," .
			"inventory_category_name text," .
			"PRIMARY KEY (inventory_category_id)," .
			"UNIQUE KEY inventory_category_id (inventory_category_id)" .
			") TYPE=MyISAM;";
		
		db_exec( $sql ); db_error();
		
		$sql = "CREATE TABLE inventory_brands ( " .
			"inventory_brand_id int(11) unsigned NOT NULL auto_increment," .
			"inventory_brand_name text," .
			"PRIMARY KEY (inventory_brand_id)," .
			"UNIQUE KEY inventory_brand_id (inventory_brand_id)" .
			") TYPE=MyISAM;";
		
		db_exec( $sql ); db_error();
		
		return null;
	}
	
	function remove() {
		db_exec( "DROP TABLE inventory;" );
		db_exec( "DROP TABLE inventory_categories;" );
		db_exec( "DROP TABLE inventory_brands;" );
		return null;
	}
	
	function upgrade() {
		return null;
	}
}

?>	
	

