<?php /* INVENTORY $Id: do_inventory_aed.php,v 1.5 2003/11/11 15:31:54 dylan_cuthbert Exp $ */

global $m;

// do we need to create a new category?
error_reporting( E_ALL );

// deal with brand or category renaming

$canEdit = !getDenyEdit( $m );
$canRead = !getDenyRead( $m );

if ( !$canEdit || !$canRead )
{
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}


if ( isset( $_POST[ "remember_marked" ] ) )
{
	if ( isset( $_POST[ "mark" ] ) )
	{
		$AppUI->setState( 'InventoryIdxMarked', $_POST[ 'mark' ] );
		$AppUI->setMsg( count( $_POST[ 'mark' ] )." ".$AppUI->_("items remembered" ), UI_MSG_OK );
	}
	else $AppUI->setMsg( $AppUI->_( "No items marked to remember" ), UI_MSG_ALERT );
	$AppUI->redirect();
}

if ( isset( $_POST[ "remember_more" ] ) )
{
	if ( isset( $_POST[ "mark" ] ) )
	{
		$old_marks = $AppUI->getState( 'InventoryIdxMarked' ) ? $AppUI->getState( 'InventoryIdxMarked' ) : array();
		$old_marks = array_unique( array_merge( $old_marks, $_POST[ "mark" ] ) );
		$AppUI->setState( 'InventoryIdxMarked', $old_marks );
		
		$AppUI->setMsg( count( $_POST[ 'mark' ] )." "
							.$AppUI->_("new items remembered" )
							." (".$AppUI->_("Total").": ".count( $old_marks ).")"
						, UI_MSG_OK );
	}
	else $AppUI->setMsg( $AppUI->_( "No items marked to remember" ), UI_MSG_ALERT );
	$AppUI->redirect();
}

if ( isset( $_POST[ "remember_clear" ] ) )
{
	$AppUI->setState( 'InventoryIdxMarked', array() );
	$AppUI->setMsg( $AppUI->_( "Marked Items Cleared" ), UI_MSG_OK );
	$AppUI->redirect();
}


if ( isset( $_POST[ 'change_category_id' ] ) )
{
	$category_id = intval( $_POST[ 'change_category_id' ] );
	$cat = new CInventoryCategory();
	if ( $cat->load( $category_id ) )
	{
		$cat->inventory_category_name = $_POST[ 'change_category_name' ];
		$AppUI->setMsg( $AppUI->_( "Category renamed" ), UI_MSG_ALERT );
		$cat->store();
	}
	else $AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ( isset( $_POST[ 'change_brand_id' ] ) )
{
	$brand_id = intval( $_POST[ 'change_brand_id' ] );
	$brand = new CInventoryBrand();
	if ( $brand->load( $brand_id ) )
	{
		$brand->inventory_brand_name = $_POST[ 'change_brand_name' ];
		$AppUI->setMsg( $AppUI->_( "Brand renamed" ), UI_MSG_ALERT );
		$brand->store();
	}
	else $AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}



$del = isset($_POST['del']) ? $_POST['del'] : 0;
$del_children = isset($_POST['delete_children']) ? $_POST['delete_children'] : 0;
$brand = intval( isset( $_POST[ 'inventory_brand' ] ) ? $_POST[ 'inventory_brand' ] : 0 );
$category = intval( isset( $_POST[ 'inventory_category' ] ) ? $_POST[ 'inventory_category' ] : 0 );
$inventory_id = intval( isset( $_POST[ 'inventory_id' ] ) ? $_POST[ 'inventory_id' ] : 0 );

// check permissions for this record
$canRead = !getDenyRead( $m, $inventory_id );
$canEdit = !getDenyEdit( $m, $inventory_id );

if ( !$canEdit || !$canRead )
{
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ( $del )
{
	$AppUI->setMsg( 'Inventory Item' );
	$obj = new CInventory();
	
	if ( ($msg = $obj->delete( $inventory_id, $del_children )) )
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	else
	{
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
	
}

// create a new brand?

if ( $brand == -1 )
{
	$newbrand = new CInventoryBrand();
	$newbrand->inventory_brand_name = $_POST['inventory_newbrand'];
	
	if ( strlen( $newbrand->inventory_brand_name ) >= 2 )
	{
		$newbrand->store();
		unset( $_POST[ 'inventory_newbrand' ] );
		$_POST['inventory_brand'] = $newbrand->inventory_brand_id;
	}
	else
	{
		$AppUI->setMsg( $AppUI->_("Brand name must be longer than 1 character"), UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

// create a new category?

if ( $category == -1 )
{
	$newcat = new CInventoryCategory();
	$newcat->inventory_category_name = $_POST['inventory_newcategory'];
	
	if ( strlen( $newcat->inventory_category_name ) > 1 )
	{
		$newcat->store();
		unset( $_POST[ 'inventory_newcategory' ] );
		$_POST['inventory_category'] = $newcat->inventory_category_id;
	}
	else
	{
		$AppUI->setMsg( $AppUI->_("Category name must be longer than 1 characters"), UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

// load up existing data for checking later

$oldobj = new CInventory();

if ( $inventory_id )
{
	if ( !$oldobj->load( $inventory_id ) )
	{
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

// now bind and write out new data

$obj = new CInventory();

if (!$obj->bind( $_POST ))
{
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

/* convert dates to SQL format */

$date = new CDate( $obj->inventory_purchased );
$obj->inventory_purchased = $date->format( FMT_DATETIME_MYSQL );

$date = new CDate( $obj->inventory_delivered );
$obj->inventory_delivered = $date->format( FMT_DATETIME_MYSQL );

$date = new CDate( $obj->inventory_assign_from );
$obj->inventory_assign_from = $date->format( FMT_DATETIME_MYSQL );

$date = new CDate( $obj->inventory_assign_until );
$obj->inventory_assign_until = $date->format( FMT_DATETIME_MYSQL );

if ( isset( $_POST[ 'unlink_from_parent' ] ) && $_POST[ 'unlink_from_parent' ] )
{
	$obj->inventory_parent = 0;
}

if ( !$obj->store() ) $AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );

// go through children and re-assign them

function reassign( $oldobj, $newobj, $id = 0 )
{
	if ( $id == 0 ) $id = $newobj->inventory_id;
	
	$sql = "SELECT inventory_id FROM inventory WHERE inventory_parent=$id";
	if ( $children = db_loadList( $sql ) )
	{
		foreach ( $children as $child_id )
		{
			$child = new CInventory();
			if ( $child->load( $child_id[ 'inventory_id' ] ) )
			{
				if ( $child->inventory_user == $oldobj->inventory_user ) $child->inventory_user = $newobj->inventory_user;
				if ( $child->inventory_project == $oldobj->inventory_project ) $child->inventory_project = $newobj->inventory_project;
				$child->store();
				reassign( $oldobj, $newobj, $child->inventory_id );
			}
		}
	}
}

if ( $inventory_id )
{
	reassign( $oldobj, $obj );
}

if ( $inventory_id || $obj->inventory_parent )
{
	$AppUI->redirect();
}

?>
