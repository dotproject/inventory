<?php /* INVENTORY $Id: index.php,v 1.1.1.1 2003/11/07 02:10:40 dylan_cuthbert Exp $ */

error_reporting( E_ALL );

include_once("{$AppUI->cfg['root_dir']}/modules/inventory/utility.php");

$AppUI->savePlace();


// check permissions for this module
$canRead = !getDenyRead( $m );
$canEdit = !getDenyEdit( $m );


if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}


// setup the title block
$titleBlock = new CTitleBlock( 'Inventory', "../modules/inventory/images/48_my_computer.png", $m, "$m.$a" );

// retrieve any state parameters

if (isset( $_POST['f1'] ))
{
	$AppUI->setState( 'InventoryIdxFilterCompany', $_POST['f1'] );
	$_POST[ 'f2' ] = "choose";
	$_POST[ 'f3' ] = "na";
}
$f1 = $AppUI->getState( 'InventoryIdxFilterCompany' ) ? $AppUI->getState( 'InventoryIdxFilterCompany' ) : '0';

if (isset( $_POST['f2'] )) {
	$AppUI->setState( 'InventoryIdxFilterType', $_POST['f2'] );
	$AppUI->setState( 'InventoryIdxFilterIndex', $_POST['f3'] );
}
$f2 = $AppUI->getState( 'InventoryIdxFilterType' ) ? $AppUI->getState( 'InventoryIdxFilterType' ) : 'choose';
$f3 = $AppUI->getState( 'InventoryIdxFilterIndex' ) ? $AppUI->getState( 'InventoryIdxFilterIndex' ) : 'na';

global $specify_company;
$specify_company = $f1;

// set up filters list

global $company_list;
load_company_list();

$filters = array();
reset( $company_list );
$filters[ '0' ] = $AppUI->_( "All Companies" );

foreach( $company_list as $company )
{
	$filters[ $company[ 'company_id' ] ] = $company[ 'company_name' ];
}

$filters2 = array( "choose" => "Choose...", "department" => "Department", "project" => "Project", "user" => "User" );
if ( $f2 != "choose" ) $filters3 = array( "choose" => "Choose..." );
else $filters3 = array( "na" => "NA" );

$titleBlock->addCrumbRight(
	'<TD><form action="?m=inventory" method="post" name="itemFilter1">'.$AppUI->_('Filter') . ':'
		. arraySelect( $filters, 'f1', 'size=1 class=text id="itemList1" onChange="document.itemFilter1.submit();"', $f1, true )
		. '</form></TD>'
		. '<TD><form action="?m=inventory" method="post" id="itemFilter2" name="itemFilter2">'
		. arraySelect( $filters2, 'f2', 'size=1 class=text style="width: 100px" id="itemList2" onChange="'."javascript:changeListByType( 'itemList1', 'itemList2', 'itemList3', 0, '".$AppUI->_( "Choose..." )."' );".'"', $f2, true )
		. arraySelect( $filters3, 'f3', 'size=1 class=text style="width: 100px" id="itemList3" onChange="document.itemFilter2.submit();"', $f3, true )
		. "</form></TD>"
	
	, 'ALIGN="right"'
	, '<table BORDER="0" CELLSPACING="0" CELLPADDING="0" ALIGN="right"><TR>'
	, '</TR></table>'
);




if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new inventory item').'">', '',
		'<form action="?m=inventory&a=addedit&" method="post">', '</form>'
	);
}

$titleBlock->show();

include_once("{$AppUI->cfg['root_dir']}/modules/inventory/javalists.php");
include("{$AppUI->cfg['root_dir']}/modules/inventory/inventory.php");

?>


<script LANGUAGE="JavaScript">
<!--

<?php

if ( $f3 != 'na' )
{
	echo 'changeListByType( "itemList1", "itemList2", "itemList3", '
		.(( $f3 && $f3 != "choose" ) ? $f3 : "0").", '".$AppUI->_( "Choose..." )."' );";
}

?>

-->
</SCRIPT>

