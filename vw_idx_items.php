<?php

global $m,$df,$item_list;

error_reporting( E_ALL );

require_once( $AppUI->getModuleClass( "admin" ) );
require_once( $AppUI->getModuleClass( "projects" ) );

$df = $AppUI->getPref('SHDATEFORMAT');

include_once("{$AppUI->cfg['root_dir']}/modules/inventory/utility.php");

load_all_items();

?>



<TABLE BORDER="1" CELLPADDING="3" CELLSPACING="1" WIDTH="100%" CLASS="tbl" >
	<THEAD>
	<TR>
		<TH></TH>
		<TH><?php echo $AppUI->_( "Id" ); ?></TH>
		<TH NOWRAP><?php echo $AppUI->_( "Item Name" )." (".$AppUI->_("click to view details").")"; ?></TH>
		<TH><?php echo $AppUI->_( "Brand" ); ?></TH>
		<TH><?php echo $AppUI->_( "Category" ); ?></TH>
		<TH><?php echo $AppUI->_( "Assigned to" ); ?></TH>
		<TH><?php echo $AppUI->_( "Project" ); ?></TH>
		<TH><?php echo $AppUI->_( "Date" ); ?></TH>
		<TH><?php echo $AppUI->_( "Cost" ); ?></TH>
	</TR>
	</THEAD>
	<TBODY>
	<?php
		$user_list = array();
		$project_list = array();
		
		
		$child_parent = 0;
		if ( isset( $_GET[ "children" ] ) ) $child_parent = $_GET[ "children" ];
		
		reset( $item_list );
		foreach ($item_list as $item)
		{
			if ( (!$item['inventory_parent'] && (!$child_parent || $child_parent == "all"))
				 || ( $child_parent && $child_parent == $item['inventory_parent' ] ) )
			{
				display_item( $item, ($child_parent != "all" && $child_parent)?1:0, $child_parent );
			}
		}
		
	/* make sure orphaned items are also displayed */
		
		if (!$child_parent && 0)
		{
			reset( $item_list );
			foreach ($item_list as $item )
			{
				if ( !isset( $drawn_array[ $item[ 'inventory_id' ] ] ) )
				{
					display_item( $item, 0 );
				}
			}
		}
	?>
</TBODY>	
</TABLE>

