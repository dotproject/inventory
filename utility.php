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

// gets the current sort state (and sets it if specified in $_GET

function getSortState()
{
	global $AppUI;
	
	$sort_state = array();
	if ( $AppUI->getState( 'InventoryIdxSortState' ) )
	{
		$sort_state = $AppUI->getState( 'InventoryIdxSortState' );
	}
	
	if ( isset( $_GET[ 'sort_item' ] ) )
	{
		if ( isset( $sort_state[ 'sort_item1' ] ) )
		{
			if ( !isset( $sort_state[ 'sort_item2' ] ) || $sort_state[ 'sort_item1' ] != $sort_state[ 'sort_item2' ] )
			{
				$sort_state[ 'sort_item2' ] = $sort_state[ 'sort_item1' ];
				$sort_state[ 'sort_order2' ] = $sort_state[ 'sort_order1' ];
			}
		}
		
		$sort_state[ 'sort_item1' ] = $_GET[ 'sort_item' ];
		$sort_state[ 'sort_order1' ] = dPgetParam( $_GET, 'sort_order', 0 );
		
		$AppUI->setState( 'InventoryIdxSortState', $sort_state );
	}
	return $sort_state;
}

global $item_list, $drawn_array, $item_list_parents, $sort_state;

function load_all_items()
{
	global $item_list, $item_list_parents, $AppUI;
	global $sorted_item_list;
	global $inventory_view_mode;
	
	$filter_company = $AppUI->getState( 'InventoryIdxFilterCompany' ) ? $AppUI->getState( 'InventoryIdxFilterCompany' ) : 0;
	$filter_type    = $AppUI->getState( 'InventoryIdxFilterType' ) ? $AppUI->getState( 'InventoryIdxFilterType' ) : 0;
	$filter_index   = $AppUI->getState( 'InventoryIdxFilterIndex' ) ? $AppUI->getState( 'InventoryIdxFilterIndex' ) : 0;
	
	$sql =
		"SELECT inventory.*, inventory_category_name, inventory_brand_name
		 , company_name AS inventory_company_name
		 , dept_name AS inventory_department_name
		 , user_username AS inventory_user_username
		 , project_name AS inventory_project_name
		 , contact_first_name AS inventory_user_first_name
		 , contact_last_name AS inventory_user_last_name
		 FROM inventory
		 LEFT JOIN inventory_categories ON inventory_category=inventory_category_id
		 LEFT JOIN inventory_brands ON inventory_brand=inventory_brand_id
		 LEFT JOIN companies ON inventory_company=company_id
		 LEFT JOIN departments ON inventory_department=dept_id
		 LEFT JOIN users ON inventory_user=user_id
		 LEFT JOIN contacts ON users.user_contact=contacts.contact_id
		 LEFT JOIN projects ON inventory_project=project_id
		";
		
	$sql .= "WHERE 1 \n";
	
	if ( $filter_company )
	{
		if ( $filter_index && $filter_type != "choose" )
		{
			$sql .= "AND inventory_${filter_type} = $filter_index \n";
		}
		else
		{
			$sql .= "AND inventory_company = $filter_company \n";
		}
	}
	
	if ( $inventory_view_mode == "orders" )
	{
		$sql .= "AND inventory_purchase_state <> 'A' AND inventory_purchase_state IS NOT NULL \n";
	}
	else
	{
		$sql .= "AND ( inventory_purchase_state IS NULL OR inventory_purchase_state = 'A' )  \n";
	}
	
	$sql_list = db_loadList( $sql ); db_error();
	
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
	
// sort the list
	
	global $sort_state;
	if ( !isset( $sort_state ) ) $sort_state = getSortState();
	
	if ( isset( $sort_state[ 'sort_item1' ] ) )
	{
		if ( isset( $sort_state[ 'sort_item2' ] ) )
		{
			$sorted_item_list = array_csort_inventory( $item_list, $sort_state['sort_item1'], intval( $sort_state['sort_order1'] )
											 , $sort_state[ 'sort_item2' ], intval( $sort_state['sort_order2'] ) );
		}
		else
		{
			$sorted_item_list = array_csort_inventory( $item_list, $sort_state['sort_item1'], intval($sort_state['sort_order1']) );
		}
	}
	else $sorted_item_list = array_csort_inventory( $item_list, 'inventory_id' );
	
}

// load up accessible company list

function load_company_list()
{
	global $company_list;
	
	$compsql = "
	SELECT company_id, company_name
	FROM companies
	ORDER BY company_name
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


// returns array of marked inventory ids

function get_marked_inventory( )
{
	global $AppUI;
	
	$marked_inventory = array();
	
	if ( isset( $_POST[ "mark" ] ) ) $AppUI->setState( 'InventoryIdxMarked', $_POST[ 'mark' ] );
	$marked_inventory = $AppUI->getState( 'InventoryIdxMarked' ) != NULL ? $AppUI->getState( 'InventoryIdxMarked' ) : array();
		
	return $marked_inventory;
}


// displays a table row containing the item's information

function display_item( &$item, $indent, $children = 0 )
{
	global $m,$a,$brand_list,$category_list,$df,$item_list,$drawn_array;
	global $item_list_parents,$AppUI;
	global $user_list, $project_list, $company_list, $department_list;
	global $marked;
	global $inventory_view_mode;
	
// load up the marked array
	
	if ( !isset( $marked ) )
	{
		$marked = get_marked_inventory();
	}
	
	$drawn_array[ $item['inventory_id' ] ] = true;
	
	echo "<TR><TD align='center'>";
	$canEdit = !getDenyEdit( $m, $item['inventory_id'] );
	if ( $canEdit )
	{
		echo '<INPUT TYPE="checkbox" NAME="mark[]" VALUE="'.$item['inventory_id'].'" ';
		echo (in_array( $item['inventory_id'], $marked )?"CHECKED":"").'>&nbsp;';
		
		echo '<A HREF="?m=inventory&a=addedit&inventory_id='.$item['inventory_id'].'">';
		echo dPshowImage( "./images/icons/stock_edit-16.png", 16, 16, "" ).'</A>';
	}
		
	echo "</TD><TD>";
	if ( isset( $item['inventory_asset_no']) && $item['inventory_asset_no'])
	{
		echo $item['inventory_asset_no'];
	}
	else printf( "%06d", $item['inventory_id'] );
	
	echo "</TD><TD WIDTH='100%' NOWRAP>";
	for ($y=0; $y < $indent; $y++) {
		if ($y+1 == $indent) {
			echo '<img src="./images/corner-dots.gif" width="16" height="12" border="0">';
		} else {
			echo '<img src="./images/shim.gif" width="16" height="12"  border="0">';
		}
	}
	echo "<A HREF='?m=inventory&a=view&inventory_id={$item['inventory_id']}' ".(($children && !$item[ 'inventory_parent' ] )?" style='font-weight: bold'":"").">";
	echo $item['inventory_name'];
	if ( !$children && isset( $item_list_parents[ $item[ 'inventory_id' ] ] ) )
	{
		$num = count( $item_list_parents[ $item[ 'inventory_id' ] ] );
		echo "<BR />(".$num." ".(($num == 1)?$AppUI->_( "sub-item" ):$AppUI->_( "sub-items" )).")";
	}
	echo "</A>";
	
	echo "</TD><TD NOWRAP>";
	echo dPgetParam( $item, 'inventory_brand_name', $AppUI->_( "Unknown" ) );
	
	echo "</TD><TD NOWRAP>";
	echo dPgetParam( $item, 'inventory_category_name', $AppUI->_( "Unknown" ) );
	
/* lookup of company-name */
	
	echo "</TD><TD NOWRAP>";
	echo dPgetParam( $item, 'inventory_company_name', $AppUI->_( "Unknown" ) );
	
/* lookup of department name */
	
	echo "</TD><TD NOWRAP>";
	echo dPgetParam( $item, 'inventory_department_name', $AppUI->_( "Unknown" ) );
	
/* lookup of user-name */
	
	echo "</TD><TD NOWRAP>";
	$username = dPgetParam( $item, 'inventory_user_username', '' );
	if ( $username != '' )
	{
		echo '<A HREF="?m=inventory&';
		echo 'quick_filter=user&quick_filter_id='.$item[ 'inventory_user' ].'" />';
		echo $username;
		echo '</A>';
	}
	else
	{
		echo $AppUI->_( "Unassigned" );
	}
	
/* lookup of project-name */
	
	echo "</TD><TD NOWRAP>";
	$projname = dPgetParam( $item, 'inventory_project_name', '' );
	if ( $projname != '' )
	{
		echo '<A HREF="?m=inventory&';
		echo 'quick_filter=project&quick_filter_id='.$item[ 'inventory_project' ].'" />';
		echo $projname;
		echo '</A>';
	}
	else
	{
		echo $AppUI->_( "Unassigned" );
	}
	
	echo "</TD><TD NOWRAP>";
	
	$date = "";
	if ( $inventory_view_mode == "orders" )
	{
		$date = new CDate( $item[ 'inventory_purchased' ] );
	}
	else
	{
		$date = new CDate( $item[ 'inventory_delivered' ] );
	}
	echo $date->format( $df );
	
	echo "</TD><TD align='right'>";
	
	if ( $inventory_view_mode == "normal" )
	{
// if children display is disabled then print total cost
	
		if ( !$children )
		{
			$obj = new CInventory;
			
			echo $item[ 'inventory_cost' ] + $obj->calcChildrenTotal( $item[ 'inventory_id' ], $item_list, $item_list_parents );
		}
		else echo $item[ 'inventory_cost' ];
	}
	
	if ( $inventory_view_mode == "orders" )
	{
		$terms = array( "O" => "Ordered", "C" => "Confirmed", "D" => "Delayed", "A" => "Arrived" );
		if ( !isset( $item[ 'inventory_purchase_state' ] ) ) $item[ 'inventory_purchase_state' ] = "A";
		echo $AppUI->_( $terms[ $item[ 'inventory_purchase_state' ] ] );
	}
	
	echo "</TD></TR>";
	
/* search for children - uses item_list_parents cross-indexing */
	
	if ( $children )
	{
		if ( isset( $item_list_parents[ $item[ 'inventory_id' ] ] ) )
		{
			reset( $item_list_parents[ $item[ 'inventory_id' ] ] );
			foreach ( $item_list_parents[ $item[ 'inventory_id' ] ] as $id )
			{
				if ( !isset( $drawn_array[ $id ] ) )
				{
					display_item( $item_list[ $id ], $indent+1, $children );
				}
			}
		}
	}
}


function array_csort_inventory()   //coded by Ichier2003
{
    $args = func_get_args();
    $marray = array_shift($args);
	
	if ( empty( $marray )) return array();
	
	$i = 0;
    $msortline = "return(array_multisort(";
	$sortarr = array();
    foreach ($args as $arg) {
        $i++;
        if (is_string($arg)) {
            foreach ($marray as $row) {
                $sortarr[$i][] = $row[$arg];
            }
        } else {
            $sortarr[$i] = $arg;
        }
        $msortline .= "\$sortarr[".$i."],";
    }
    $msortline .= "\$marray));";

    eval($msortline);
    return $marray;
}


function sortHeader( $header, $field )
{
	global $AppUI;
	global $sort_state;
	
	$a = dPgetParam( $_GET, 'a', "" );
	
	if ( !isset( $sort_state ) ) $sort_state = getSortState();
	
	echo "<TH NOWRAP>";
	
	$order = SORT_ASC;
	if ( isset( $sort_state[ 'sort_item1' ] ) && $sort_state[ 'sort_item1' ] == $field )
	{
		$order = ( $sort_state[ 'sort_order1' ] == SORT_ASC ) ? SORT_DESC : SORT_ASC;
		
		if ( $sort_state[ 'sort_order1' ] == SORT_ASC ) echo '<img src="./images/icons/low.gif" width=13 height=16>';
		else echo '<img src="./images/icons/1.gif" width=13 height=16>';
		
	}
	
	
	
	echo '<A HREF="?m=inventory&';
	if ( $a ) echo 'a='.$a.'&';
	if ( isset( $_GET[ 'inventory_id' ] ) ) echo 'inventory_id='.$_GET[ 'inventory_id' ].'&';
	echo 'sort_item='.$field.'&sort_order='.$order.'">';
	
	echo $header;
	
	echo '</A>';
	
	echo "</TH>";
}


?>
