<?php

global $m,$df,$item_list,$AppUI;

global $user_list, $project_list, $company_list, $department_list;

error_reporting( E_ALL );

require_once( $AppUI->getModuleClass( "admin" ) );
require_once( $AppUI->getModuleClass( "projects" ) );
require_once( $AppUI->getModuleClass( "companies" ) );
require_once( $AppUI->getModuleClass( "departments" ) );

$df = $AppUI->getPref('SHDATEFORMAT');

$user_list = array();
$project_list = array();
$company_list = array();
$department_list = array();

include_once("{$AppUI->cfg['root_dir']}/modules/inventory/utility.php");

load_all_items();

$canEdit = !getDenyEdit( $m );

?>


<TABLE BORDER="1" CELLPADDING="3" CELLSPACING="1" CLASS="tbl" WIDTH="100%" >
<FORM ACTION="?m=inventory" METHOD="post" ID="markForm">
	<THEAD>
	
	<TR>
		<TH><?php if ($canEdit) echo $AppUI->_( "Mark" )."/<BR />".$AppUI->_( "Edit" );?> </TH>
		<TH><?php echo $AppUI->_( "Asset No" ); ?></TH>
		<TH NOWRAP><?php echo $AppUI->_( "Item Name" )." (".$AppUI->_("click for details").")"; ?></TH>
		<TH><?php echo $AppUI->_( "Brand" ); ?></TH>
		<TH><?php echo $AppUI->_( "Category" ); ?></TH>
		<TH><?php echo $AppUI->_( "Company" ); ?></TH>
		<TH><?php echo $AppUI->_( "Department" ); ?></TH>
		<TH><?php echo $AppUI->_( "Assigned to" ); ?></TH>
		<TH><?php echo $AppUI->_( "Project" ); ?></TH>
		<TH><?php echo $AppUI->_( "Date" ); ?></TH>
		<TH><?php echo $AppUI->_( "Cost" ); ?></TH>
	</TR>
	</THEAD>
	<TBODY>
	<?php
		
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
		
		global $drawn_array;
		
		if (!$child_parent )
		{
			reset( $item_list );
			foreach ($item_list as $item )
			{
				if ( !isset( $drawn_array[ $item[ 'inventory_id' ] ] ) )
				{
					$item[ 'inventory_name' ] = $item[ 'inventory_name' ] . " (" . $AppUI->_( "parent not shown" ) . ")" ;
					display_item( $item, ($item[ 'inventory_parent' ] ) ? 1 : 0 );
				}
			}
		}
	?>
<TR>
	<TD COLSPAN="11">
		<?php
			$num_marked = count( get_marked_inventory() );
		
			if ( $canEdit )
			{
				echo '<INPUT TYPE="submit" class="button" name="remember_marked" VALUE="'.$AppUI->_( "Remember Marked" ).'"> ';
				if ( $num_marked ) echo '<INPUT TYPE="submit" class="button" name="remember_more" VALUE="'.$AppUI->_( "Remember More" ).'"> ';
				echo '<INPUT TYPE="submit" class="button" name="remember_clear" VALUE="'.$AppUI->_( "Clear Marked" ).'"> ';
				echo '<INPUT TYPE="hidden" NAME="dosql" VALUE="do_inventory_aed" />';
			}
		?>
	</TD>
</TR>
</TBODY>
</FORM>
</TABLE>

<?php
			
	$div_begun = false;
			
	if ( $num_marked )
	{
		echo "<div style='font-size: 9px; padding-top: 4px;'>";
		echo $num_marked." ".$AppUI->_( "items remembered" );
		$div_begun = true;
	}
			
	global $company_list;
	load_company_list();
		
	$filter_company = $AppUI->getState( 'InventoryIdxFilterCompany' ) ? $AppUI->getState( 'InventoryIdxFilterCompany' ) : 0;
	$filter_type    = $AppUI->getState( 'InventoryIdxFilterType' ) ? $AppUI->getState( 'InventoryIdxFilterType' ) : 0;
	$filter_index   = $AppUI->getState( 'InventoryIdxFilterIndex' ) ? $AppUI->getState( 'InventoryIdxFilterIndex' ) : 0;
		
	if ( $filter_company )
	{
		if ( !$div_begun )
		{
			echo "<div style='font-size: 9px; padding-top: 4px;'>";
			$div_begun = true;
		}
		else echo " | ";
		
		
		echo $AppUI->_( "filtered by" )." ";
		
		if ( $filter_type !="choose" && $filter_index )
		{
			echo " ".$AppUI->_( $filter_type );
		}
		else
		{
			echo $company_list[ $filter_company ][ 'company_name' ];
		}

	}
	
	if ( $div_begun ) echo "</div>";
	
?>

