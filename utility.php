<?php	/* INVENTORY $ld: utility.php, v1.00 2003/11/05 13:53 dylan_cuthbert Exp $ */

// common functions and variables used by the inventory module


// load up the category and brand lists

global $category_list,$brand_list;

$sql = "SELECT * from inventory_categories where 1;";
$category_list = db_loadList( $sql );
echo db_error();

$sql = "SELECT * from inventory_brands where 1;";
$brand_list = db_loadList( $sql );
echo db_error();


function get_category_name( &$category_list, $catind )
{
	foreach ($category_list as $category)
	{
		if ( $category['inventory_category_id'] == $catind )
		{
			return( $category['inventory_category_name'] );
		}
	}
	return( $AppUI->_( "Category not found" ) );
}

function get_brand_name( &$brand_list, $braind )
{
	foreach ($brand_list as $brand)
	{
		if ( $brand['inventory_brand_id'] == $braind )
		{
			return( $brand['inventory_brand_name'] );
		}
	}
	return( $AppUI->_( "Brand not found" ) );
}

global $item_list, $drawn_array, $item_list_parents;

function load_all_items()
{
	global $item_list, $item_list_parents, $AppUI;
	
	$filter_company = $AppUI->getState( 'InventoryIdxFilterCompany' ) ? $AppUI->getState( 'InventoryIdxFilterCompany' ) : 0;
	$filter_type    = $AppUI->getState( 'InventoryIdxFilterType' ) ? $AppUI->getState( 'InventoryIdxFilterType' ) : 0;
	$filter_index   = $AppUI->getState( 'InventoryIdxFilterIndex' ) ? $AppUI->getState( 'InventoryIdxFilterIndex' ) : 0;
	
	$sql = "SELECT * from inventory\n";
	
	if ( $filter_company )
	{
		$sql .= "WHERE inventory_company = $filter_company \n";
		
		if ( $filter_index && $filter_type != "choose" )
		{
			$sql .= "AND inventory_${filter_type} = $filter_index";
		}
	}
	
	
	
	$sql_list = db_loadList( $sql );
	echo db_error();
	
// re-index the list based on inventory_id
	
	$item_list = array();
	$item_list_parents = array();
	foreach ($sql_list as $item)
	{
		$item_list[ $item[ 'inventory_id' ] ] = $item;
		
		if ( $item[ 'inventory_parent' ] )
		{
			$item_list_parents[ $item[ 'inventory_parent' ] ][] = $item[ 'inventory_id' ];
		}
	}
	
}

// load up accessible company list

function load_company_list()
{
	global $company_list;
	
	$compsql = "
	SELECT company_id, company_name
	FROM companies
	";
		
	$company_list = array();
	
	if (($rows = db_loadList( $compsql, NULL )))
	{
		foreach ($rows as $row)
		{
			if ( !getDenyRead( "companies", $row[ 'company_id' ] ) )
			{
		/* store it for later use */
				$company_list[ $row[ "company_id" ] ] = $row;
			}
		}
	}
}

function display_item( &$item, $indent, $children = 0 )
{
	global $m,$brand_list,$category_list,$df,$item_list,$drawn_array;
	global $item_list_parents,$AppUI;
	global $user_list, $project_list, $company_list, $department_list;
	
	$drawn_array[ $item['inventory_id' ] ] = 1;
	
	echo "<TR><TD>";
	$canEdit = !getDenyEdit( $m, $item['inventory_id'] );
	if ( $canEdit ) echo '<A HREF="?m=inventory&a=addedit&inventory_id='.$item['inventory_id'].'">'.dPshowImage( "./images/icons/stock_edit-16.png", 16, 16, "" ).'</A>';
		
	echo "</TD><TD>";
	if ( isset( $item['inventory_asset_no']) && $item['inventory_asset_no'])
	{
		echo $item['inventory_asset_no'];
	}
	else printf( "%06d", $item['inventory_id'] );
	
	echo "</TD><TD WIDTH='100%'>";
	for ($y=0; $y < $indent; $y++) {
		if ($y+1 == $indent) {
			echo '<img src="./images/corner-dots.gif" width="16" height="12" border="0">';
		} else {
			echo '<img src="./images/shim.gif" width="16" height="12"  border="0">';
		}
	}
	echo "<A HREF='?m=inventory&a=view&inventory_id={$item['inventory_id']}'>";
	echo $item['inventory_name'];
	if ( !$children && isset( $item_list_parents[ $item[ 'inventory_id' ] ] ) )
	{
		$num = count( $item_list_parents[ $item[ 'inventory_id' ] ] );
		echo "&nbsp;&nbsp;&nbsp;(".$num." ".(($num == 1)?$AppUI->_( "sub-item" ):$AppUI->_( "sub-items" )).")";
	}
	echo "</A>";
	
	echo "</TD><TD NOWRAP>";
	echo get_brand_name( $brand_list, $item['inventory_brand'] );
	
	echo "</TD><TD NOWRAP>";
	echo get_category_name( $category_list, $item['inventory_category'] );
	
/* cache lookup of company-names */
	
	echo "</TD><TD NOWRAP>";
	if ( !isset( $company_list[ $item[ 'inventory_company' ] ] ) )
	{
		$company = new CCompany();
		$company_list[ $item[ 'inventory_company' ] ] = ( ( $company->load( $item[ 'inventory_company' ] ) )
													? $company->company_name : $AppUI->_( "Unknown" ) );
	}
	echo $company_list[ $item[ 'inventory_company' ] ];
	
/* cache lookup of department names */
	
	echo "</TD><TD NOWRAP>";
	if ( !isset( $department_list[ $item[ 'inventory_department' ] ] ) )
	{
		$dept = new CDepartment();
		$department_list[ $item[ 'inventory_department' ] ] = ( ( $dept->load( $item[ 'inventory_department' ] ) )
													? $dept->dept_name : $AppUI->_( "Unknown" ) );
	}
	echo $department_list[ $item[ 'inventory_department' ] ];
	
/* cache lookup of user-names */
	
	echo "</TD><TD NOWRAP>";
	if ( !isset( $user_list[ $item[ 'inventory_user' ] ] ) )
	{
		$user = new CUser();
		$user_list[ $item[ 'inventory_user' ] ] = ( ( $user->load( $item[ 'inventory_user' ] ) )
													? $user->user_first_name." ".$user->user_last_name : $AppUI->_( "Unassigned" ) );
	}
	echo $user_list[ $item[ 'inventory_user' ] ];
	
/* cache lookup of project-names */
	
	echo "</TD><TD NOWRAP>";
	if ( !isset( $project_list[ $item[ 'inventory_project' ] ] ) )
	{
		$proj = new CProject();
		$project_list[ $item[ 'inventory_project' ] ] = ( ( $proj->load( $item[ 'inventory_project' ] ) )
													? $proj->project_name : $AppUI->_( "Unassigned" ) );
	}
	echo $project_list[ $item[ 'inventory_project' ] ];
	
	echo "</TD><TD NOWRAP>";
	
	$date = new CDate( $item[ 'inventory_purchased' ] );
	echo $date->format( $df );
	
	echo "</TD><TD>";
	
// if children display is disabled then print total cost
	
	if ( !$children )
	{
		$obj = new CInventory;
		
		echo $item[ 'inventory_cost' ] + $obj->calcChildrenTotal( $item[ 'inventory_id' ], $item_list, $item_list_parents );
	}
	else echo $item[ 'inventory_cost' ];
	
	echo "</TD></TR>";
	
/* search for children - uses item_list_parents cross-indexing */
	
	if ( $children )
	{
		if ( isset( $item_list_parents[ $item[ 'inventory_id' ] ] ) )
		{
			reset( $item_list_parents[ $item[ 'inventory_id' ] ] );
			foreach ( $item_list_parents[ $item[ 'inventory_id' ] ] as $id )
			{
				display_item( $item_list[ $id ], $indent+1, $children );
			}
		}
	}
}


?>
