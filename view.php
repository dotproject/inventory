<?php /* INVENTORY $Id: view.php,v 1.00 2003/11/03 15:36:24 dylan_cuthbert Exp $ */

global $item_list, $item_list_parents;

include_once("{$AppUI->cfg['root_dir']}/modules/inventory/utility.php");

error_reporting( E_ALL );


$df = $AppUI->getPref('SHDATEFORMAT');

require_once( $AppUI->getModuleClass( "admin" ) );
require_once( $AppUI->getModuleClass( "projects" ) );
require_once( $AppUI->getModuleClass( "companies" ) );
require_once( $AppUI->getModuleClass( "departments" ) );

$inventory_id = intval( dPgetParam( $_GET, "inventory_id", 0 ) );

// check permissions for this record
$canRead = !getDenyRead( $m, $inventory_id );
$canEdit = !getDenyEdit( $m, $inventory_id );

if (!$canRead) $AppUI->redirect( "m=public&a=access_denied" );

$msg = '';
$obj = new CInventory();
$canDelete = $obj->canDelete( $msg, $inventory_id );

if ( !$obj->load( $inventory_id ) )
{
	$AppUI->setMsg( "inventory" );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}


$purchase_date = new CDate( $obj->inventory_purchased );


// set up title block:

$titleBlock = new CTitleBlock( $AppUI->_("View Inventory Item"), '../modules/inventory/images/48_my_computer.png', $m, "$m.$a" );

if (!getDenyEdit( $m )) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new inventory item').'">', '',
		'<form action="?m=inventory&a=addedit&" method="post">', '</form>'
	);
}

$titleBlock->addCrumb( "?m=inventory", "inventory list" );
if ( isset( $obj->inventory_parent ) && $obj->inventory_parent ) $titleBlock->addCrumb( "?m=inventory&a=view&inventory_id=$obj->inventory_parent", "view parent" );
if ( $canEdit )
{
	$titleBlock->addCrumb( "?m=inventory&a=addedit&inventory_id=".$inventory_id, "edit this item" );
	$titleBlock->addCrumbDelete( 'delete item', $canDelete, $msg );
	$titleBlock->addCrumb( "?m=inventory&a=addedit&inventory_parent=$inventory_id", "add sub-item" );
	
}
$titleBlock->show();


?>

<script LANGUAGE="JavaScript">

function delIt()
{
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Inventory Item').'?';?>" )) {
		document.frmDelete.submit();
	}
}

</SCRIPT>

<TABLE BORDER="0" CELLPADDING="4" CELLSPACING="0" WIDTH="100%" CLASS="std" >
<FORM NAME="frmDelete" ACTION="./index.php?m=inventory" METHOD="post">
	<INPUT TYPE="hidden" NAME="dosql" VALUE="do_inventory_aed" />
	<INPUT TYPE="hidden" NAME="del" VALUE="1" />
	<INPUT TYPE="hidden" NAME="inventory_id" VALUE="<?php echo $inventory_id;?>" />
</FORM>

<TR VALIGN="top">
	<TD WIDTH="50%">
		<TABLE WIDTH="100%" CELLSPACING="1" CELLPADDING="2">
		<TR>
			<TD NOWRAP COLSPAN="2">
				<STRONG><?php echo $AppUI->_('Details');?></STRONG>
			</TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Id');?>:</TD>
			<TD CLASS="hilite">
			<?php echo $obj->getAssetNo(); ?>
			</TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Name');?>:</TD>
			<TD CLASS="hilite"> <?php echo $obj->inventory_name; ?></TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Serial');?>:</TD>
			<TD CLASS="hilite"> <?php echo $obj->inventory_serial; ?></TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Brand');?>:</TD>
			<TD CLASS="hilite"> <?php echo get_brand_name( $brand_list, $obj->inventory_brand ); ?></TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Category');?>:</TD>
			<TD CLASS="hilite"> <?php echo get_category_name( $category_list, $obj->inventory_category ); ?></TD>
		</TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR>
			<TD NOWRAP COLSPAN="2">
				<STRONG><?php echo $AppUI->_('Purchase Info');?></STRONG>
			</TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Purchase Date');?>:</TD>
			<TD CLASS="hilite"> <?php echo $purchase_date->format( $df ); ?></TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Price');?>:</TD>
			<TD CLASS="hilite"> <?php echo $obj->inventory_cost; ?></TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Purchase Type');?>:</TD>
			<TD CLASS="hilite">
			<?php
				if ( $obj->inventory_rental_period == "1" ) echo $AppUI->_( 'One-off' );
				else if ( $obj->inventory_rental_period == "M" ) echo $AppUI->_( 'Monthly' );
				else if ( $obj->inventory_rental_period == "Y" ) echo $AppUI->_( 'Yearly' );
			?>
			</TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Cost Code');?>:</TD>
			<TD CLASS="hilite"> <?php echo $obj->inventory_costcode; ?></TD>
		</TR>
		</TABLE>
	</TD>
	<TD WIDTH="50%">
		<TABLE WIDTH="100%" CELLSPACING="1" CELLPADDING="2">
		<TR>
			<TD NOWRAP COLSPAN="2"><STRONG><?php echo $AppUI->_('Owner');?></STRONG></TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Company');?>:</TD>
			<TD CLASS="hilite"> <?php $comp = new CCompany; if ($comp->load( $obj->inventory_company ) ) echo $comp->company_name; ?></TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Department');?>:</TD>
			<TD CLASS="hilite"> <?php $dept = new CDepartment; if ( $dept->load( $obj->inventory_department ) ) echo $dept->dept_name; ?></TD>
		</TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR>
			<TD NOWRAP COLSPAN="2"><STRONG><?php echo $AppUI->_('Assigned To');?></STRONG></TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('User');?>:</TD>
			<TD CLASS="hilite"> <?php $user = new CUser; if ( $user->load( $obj->inventory_user ) ) echo $user->user_first_name." ".$user->user_last_name; ?></TD>
		</TR>
		<TR>
			<TD ALIGN="right" NOWRAP><?php echo $AppUI->_('Project');?>:</TD>
			<TD CLASS="hilite"> <?php $project = new CProject; if ($project->load( $obj->inventory_project )) echo $project->project_name; ?></TD>
		</TR>
		<TR><TD>&nbsp;</TD></TR>
		<TR>
			<TD NOWRAP COLSPAN="2"><STRONG><?php echo $AppUI->_('Notes');?></STRONG></TD>
		</TR>
		<TR>
			<TD CLASS="hilite" COLSPAN="2"> <?php echo str_replace( "\n", "<BR />",$obj->inventory_description ); ?>&nbsp;</TD>
		</TR>
		
		</TABLE>
	</TD>
</TR>
</TABLE>

<BR /><BR />

<?php
			
echo "<STRONG>".$AppUI->_("Sub-items").":</STRONG><BR />";
$_GET[ 'children' ] = $inventory_id;
include_once("{$AppUI->cfg['root_dir']}/modules/inventory/vw_idx_items.php");

?>

<DIV style="text-align: right; padding: 4px; font-size: 14px;">
	
	<?php echo $AppUI->_( "Grand Total" ); ?>:&nbsp;&nbsp;&nbsp;
	<?php echo $obj->inventory_cost + $obj->calcChildrenTotal( ); ?>
</DIV>