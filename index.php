<?php /* INVENTORY $Id: index.php,v 1.31 2003/11/02 02:16:15 dylan_cuthbert Exp $ */
$AppUI->savePlace();


// check permissions for this module
$canRead = !getDenyRead( $m );
$canEdit = !getDenyEdit( $m );


if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}


// setup the title block
$titleBlock = new CTitleBlock( 'Inventory', "../modules/inventory/images/48_my_computer.png", $m, "$m.$a" );


if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new inventory item').'">', '',
		'<form action="?m=inventory&a=addedit&" method="post">', '</form>'
	);
}

$titleBlock->show();


include("{$AppUI->cfg['root_dir']}/modules/inventory/inventory.php");

?>

