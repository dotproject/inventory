<?php /* INVENTORY $Id: addedit.php,v 1.3 2003/11/08 03:33:04 dylan_cuthbert Exp $ */

global $m,$a,$ttl,$category_list,$brand_list;

include_once("{$AppUI->cfg['root_dir']}/modules/inventory/utility.php");

error_reporting( E_ALL );

$inventory_id = intval( dPgetParam( $_GET, "inventory_id", 0 ) );
$inventory_parent = intval( dPgetParam( $_GET, "inventory_parent", 0 ) );

$msg = '';
$obj = new CInventory();
$canDelete = $obj->canDelete( $msg, $inventory_id );

if (!$obj->load( $inventory_id ) && $inventory_id > 0) {
	$AppUI->setMsg( 'Inventory' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

// inherit info if parent specified

$parent_name = "";

if ( $inventory_parent )
{
	$parent = new Cinventory();
	if ( $parent->load( $inventory_parent ) )
	{
		$obj->inventory_user = $parent->inventory_user;
		$obj->inventory_company = $parent->inventory_company;
		$obj->inventory_department = $parent->inventory_department;
		$obj->inventory_project = $parent->inventory_project;
		$obj->inventory_category = $parent->inventory_category;
		$obj->inventory_brand = $parent->inventory_brand;
		$obj->inventory_purchased = $parent->inventory_purchased;
		$obj->inventory_rental_period = $parent->inventory_rental_period;
		$obj->inventory_asset_no = $parent->inventory_asset_no;
		$obj->inventory_costcode = $parent->inventory_costcode;
		$obj->inventory_parent = $inventory_parent;
		
		$parent_name = $parent->inventory_name;
	}
}
else
{
	$inventory_parent = $obj->inventory_parent;
}

// check permissions for this record
if ( $inventory_id ) {
	$canEdit = !getDenyEdit( $m, $inventory_id );
}
else
{
	$canEdit = ( !getDenyEdit( 'inventory' ) );
}

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}


$purchase_date = new CDate( $obj->inventory_purchased );


// load up accessible company list

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


// show title block

$title =  $AppUI->_((($inventory_id)?"Edit Inventory Item":"Add Inventory Item"));
$title .= (($parent_name)?(" (".$AppUI->_("Parent").": $parent_name)"):'');

$titleBlock = new CTitleBlock( $title, '../modules/inventory/images/48_my_computer.png', $m, "$m.$a" );

if (!getDenyEdit( $m )) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new inventory item').'">', '',
		'<form action="?m=inventory&a=addedit&" method="post">', '</form>'
	);
}

$titleBlock->addCrumb( "?m=inventory", "inventory list" );
if ($inventory_id) $titleBlock->addCrumb( "?m=inventory&a=view&inventory_id=$inventory_id", "view item" );
if ($inventory_parent)
{
	$titleBlock->addCrumb( "?m=inventory&a=view&inventory_id=$inventory_parent", "view parent" );
	$titleBlock->addCrumb( "?m=inventory&a=addedit&inventory_id=$inventory_parent", "edit parent" );
}
if ($inventory_id) $titleBlock->addCrumb( "?m=inventory&a=addedit&inventory_parent=$inventory_id", "add sub-item" );
if ($canDelete)	$titleBlock->addCrumbDelete( 'delete item', $canDelete, $msg );
$titleBlock->show();

?>

<link rel="stylesheet" type="text/css" media="all" href="<?php echo $AppUI->cfg['base_url'];?>/lib/calendar/calendar-dp.css" title="blue" />
<!-- import the calendar script -->
<script type="text/javascript" src="<?php echo $AppUI->cfg['base_url'];?>/lib/calendar/calendar.js"></script>
<!-- import the language module -->
<script type="text/javascript" src="<?php echo $AppUI->cfg['base_url'];?>/lib/calendar/lang/calendar-<?php echo $AppUI->user_locale; ?>.js"></script>

<script LANGUAGE="JavaScript">
<!--		

function delIt()
{
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Inventory Item').'?';?>" )) {
		document.frmDelete.submit();
	}
}


var isIE=document.all?true:false;
var isDOM=document.getElementById?true:false;
var isNS4=document.layers?true:false;
		
/* _w : which ID (1) or (2) */
/* _h : (h)ide or (s)how */
function toggleT(_w,_h) {
  if (isDOM)
  {
    if (_h=='s') document.getElementById(_w).style.visibility='visible';
    if (_h=='h') document.getElementById(_w).style.visibility='hidden';
  }
  else if (isIE) {
    if (_h=='s') eval("document.all."+_w+".style.visibility='visible';");
    if (_h=='h') eval("document.all."+_w+".style.visibility='hidden';");
  }
  else if(isNS4)
  {
    if (_h=='s') eval("document.layers['"+_w+"'].visibility='show';");
    if (_h=='h') eval("document.layers['"+_w+"'].visibility='hide';");
  }
}


function EnableDisable( _type )
{
	eval( "sel = document.forms['editFrm'].inventory_" + _type + ";" );
	eval( "newsel = document.forms['editFrm'].inventory_new" + _type + ";" );
	
    if ( sel.options[ sel.selectedIndex ].value == -1 )
	{
		toggleT( "new" + _type, "s" );
		newsel.disabled = false;
		newsel.focus();
    } else {
		toggleT( "new" + _type, "h" );
		newsel.disabled = true;
    }
    return true;
}


function submitIt()
{
	var form = document.editFrm;
	
   	form.submit();
}

function getElementById( _id )
{
	if (isDOM)
	{
	  return document.getElementById(_id);
	}
	else if (isIE)
	{
	  eval( "return document.all."+_id+" ;" );
	}
	else if(isNS4)
	{
	  eval("return document.layers['"+_id+" ;" );
	}
}

function emptyList( box )
{
	// Set each option to null thus removing it
	while ( box.options.length ) box.options[0] = null;
}

// This function assigns new drop down options to the given
// drop down box from the list of lists specified

function fillList( box, arr, _selected ) {
	// arr[0] holds the display text
	// arr[1] are the values
	
	if ( typeof _selected == "undefined" ) _selected = 0;
	
	box.selectedIndex=0;
	
	try
	{
		for ( i = 0; i < arr[0].length; i++ ) {

			// Create a new drop down option with the
			// display text and value from arr

			option = new Option( arr[0][i], arr[1][i] );

			// Add to the end of the existing options

			box.options[box.length] = option;
			
			if ( arr[1][i] == _selected)
			{
				box.selectedIndex=i;
			}
		}
	}
	catch( error )
	{
		option = new Option( <?php echo '"'.$AppUI->_( "Non Applicable" ).'"'; ?>, 0 );
		box.options[ box.length ] = option;
	}

}

// This function performs a drop down list option change by first
// emptying the existing option list and then assigning a new set

function changeList( box, lists, id, _selected )
{
	// Isolate the appropriate list by using the value
	// of the currently selected option
	
	if ( typeof selected == "undefined" ) selected = 0;

	list = lists[box.options[box.selectedIndex].value];

	// Next empty the slave list

	destination =  getElementById( id );
	emptyList( destination );

	// Then assign the new list values

	fillList( destination, list, _selected );
}




// create javascript variables to populate the department and users dialogs
// based on security settings and dynamic form selection

var dept_lists = new Array();

// load up list of departments per company
// and set them into javascript variables

<?php

$deptsql = "
SELECT dept_id, dept_name, dept_company
FROM departments
";

if (($sql_list = db_loadList( $deptsql, NULL )))
{
// create quick index table
	
	$dept_list = array();
	foreach ( $sql_list as $row )
	{
		$dept_list[ $row[ "dept_id" ] ] = $row;
	}
	
// create quick index table for departments' companies
	
	$dept_company_list = array();
	reset( $dept_list );
	foreach( $dept_list as $dept )
	{
		$dept_company_list[ $dept[ "dept_company" ] ][] = $dept[ "dept_id" ];
	}
	
// now go through each company in turn
	
	reset( $dept_company_list );
	foreach ( $dept_company_list as $key => $dept_company )
	{
		echo "dept_lists[ ".$key." ] = Array(); ";
		
		$nametext = "dept_lists[ ".$key." ][0] = new Array( ";
		$valuetext = "dept_lists[ ".$key." ][1] = new Array( ";
		
		$lastval = end($dept_company);
		reset( $dept_company );
		
// and go through each department within that company
		
		foreach( $dept_company as $dept_id )
		{
			$nametext .= '"'.$dept_list[ $dept_id ][ "dept_name" ].'"';
			$valuetext .= $dept_id;
			if ( $lastval != $dept_id )
			{
				$nametext .= ", ";
				$valuetext .= ", ";
			}
			
		}
		
		echo $nametext." );\n";
		echo $valuetext." );\n";
	}
}

?>

-->
</SCRIPT>


<TABLE BORDER="0" CELLPADDING="4" WIDTH="100%" CLASS="std">
<FORM NAME="frmDelete" ACTION="./index.php?m=inventory" METHOD="post">
	<INPUT TYPE="hidden" NAME="dosql" VALUE="do_inventory_aed" />
	<INPUT TYPE="hidden" NAME="del" VALUE="1" />
	<INPUT TYPE="hidden" NAME="inventory_id" VALUE="<?php echo $inventory_id;?>" />
	<DIV STYLE="text-align: right; padding-bottom: 8px; padding-top: 0px;" >
		<INPUT TYPE="checkbox" NAME="delete_children" VALUE="0" /> <?php echo $AppUI->_( "delete sub-tasks also" ); ?>
	</DIV>
</FORM>
<FORM NAME="editFrm" action="?m=inventory&inventory_id="<?php echo $inventory_id ?>" method="post" >
	<INPUT NAME="dosql" TYPE ="hidden" VALUE="do_inventory_aed" />
	<INPUT NAME="inventory_id" TYPE="hidden" VALUE="<?php echo $inventory_id; ?>" />
	<INPUT NAME="inventory_parent" TYPE="hidden" VALUE="<?php echo $obj->inventory_parent; ?>" />
<TR>
	<TD>
		<?php
			if ( $inventory_id )
			{
				echo "<span style='font-size: large;'>".$AppUI->_("Id").": ";
				echo $obj->getAssetNo();
				echo "</span><BR /><BR />";
			}
		?>
		<?php echo $AppUI->_( "Identification" ); ?>: <BR />
		<TABLE BORDER="1" CELLPADDING="4" WIDTH="100%">
		<TR><TD>
		<?php echo $AppUI->_( 'Item Name' ).": <BR />";?>
		<INPUT TYPE="TEXT" class="text" NAME="inventory_name" VALUE="<?php echo dPformSafe( $obj->inventory_name ); ?>" size="40" />
		<BR /><BR />
		<?php echo $AppUI->_( "Serial No" ); ?>: <BR />
		<INPUT TYPE="TEXT" class="text" NAME="inventory_serial" VALUE="<?php echo dPformSafe( $obj->inventory_serial ); ?>" size="40" />
		
		<BR /><BR />
		<?php echo $AppUI->_( "Asset No" )." (".$AppUI->_( "Leave blank to use default" ).")"; ?>: <BR />
		<INPUT TYPE="TEXT" class="text" NAME="inventory_asset_no" VALUE="<?php echo dPformSafe( $obj->inventory_asset_no ); ?>" size="40" />
		</TD></TR>
		</TABLE>
	</TD>
	<TD>
		<?php echo $AppUI->_( "Classification" ); ?>: <BR />
		<TABLE BORDER="1" CELLPADDING="4" WIDTH="100%">
			<TR>
				<TD>
					<?php echo $AppUI->_( 'Brand' );?>: <BR />
					<SELECT NAME="inventory_brand" ONCHANGE="javascript:EnableDisable( 'brand' );">
						<?php
							foreach ($brand_list as $brand)
							{
								echo "<OPTION VALUE='".$brand["inventory_brand_id"].(($brand['inventory_brand_id']==$obj->inventory_brand)?"' SELECTED>":"'>").$brand["inventory_brand_name"]."</OPTION>";
							}
						?>
						<OPTION VALUE='-1'>&lt;<?php echo $AppUI->_("New Brand"); ?>&gt;</OPTION>
					</SELECT>
					<SPAN ID="newbrand">
						->
						<INPUT TYPE="text" CLASS="text" NAME="inventory_newbrand" size="20" />
					</SPAN>
				<BR /><BR />
					<?php echo $AppUI->_( 'Category' );?>: <BR />
					<SELECT NAME="inventory_category" ONCHANGE="javascript:EnableDisable( 'category' );">
						<?php
							foreach ($category_list as $category)
							{
								echo "<OPTION VALUE='".$category["inventory_category_id"].(($category['inventory_category_id']==$obj->inventory_category)?"' SELECTED>":"'>").$category["inventory_category_name"]."</OPTION>";
							}
						?>
						<OPTION VALUE='-1'>&lt;<?php echo $AppUI->_( "New Category" ); ?>&gt;</OPTION>
					</SELECT>
					<SPAN ID="newcategory">
						->
						<INPUT TYPE="text" CLASS="text" NAME="inventory_newcategory" size="20" />
					</SPAN>
				</TD>
			</TR>
		</TABLE>
	</TD>
</TR>
<TR>
	<TD>
		<?php echo $AppUI->_( "Purchase Info" );?>: <BR />
		<TABLE BORDER="1" CELLPADDING="4" WIDTH="100%">
			<TR>
				<TD ROWSPAN="3" NOWRAP>
				<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
				<TR>
					<TD WIDTH="50%" NOWRAP><?php echo $AppUI->_( "Cost" ); ?>:<BR />
					<INPUT TYPE="TEXT" CLASS="text" NAME="inventory_cost" VALUE="<?php echo dPformSafe( $obj->inventory_cost ); ?>" SIZE="10" />
					</TD><TD WIDTH="50%" NOWRAP>
					<?php echo $AppUI->_( "Cost Code" ); ?>:<BR />
					<INPUT TYPE="TEXT" CLASS="text" NAME="inventory_costcode" VALUE="<?php echo dPformSafe( $obj->inventory_costcode ); ?>" SIZE="10" />
					</TD>
				</TR>
				</TABLE>
					
				<BR />
					<?php echo $AppUI->_( "Purchase Date" ); ?>:<BR />
					<INPUT TYPE="TEXT" CLASS="text" DISABLED ID="date1" NAME="inventory_purchased" VALUE="<?php echo $purchase_date->format( '%Y-%m-%d' ); ?>" />
					<a href="#" onClick="return showCalendar('date1', 'y-mm-dd');">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
					</a>
				</TD>
				<TD NOWRAP>
					<INPUT TYPE="RADIO" NAME="inventory_rental_period" VALUE="1"
						<?php if ( $obj->inventory_rental_period == "1" ) echo "CHECKED"; ?> ><?php echo $AppUI->_( "One-Off" ); ?>
				<BR /><BR />
					<INPUT TYPE="RADIO" NAME="inventory_rental_period" VALUE="M"
						<?php if ( $obj->inventory_rental_period == "M" ) echo "CHECKED"; ?> ><?php echo $AppUI->_( "Monthly" ); ?>
				<BR /><BR />
					<INPUT TYPE="RADIO" NAME="inventory_rental_period" VALUE="Y"
						<?php if ( $obj->inventory_rental_period == "Y" ) echo "CHECKED"; ?> ><?php echo $AppUI->_( "Yearly" ); ?>
				</TD>
			</TR>
		</TABLE>
	</TD>
	<TD>
		<?php echo $AppUI->_( "Owner" );?>:<BR />
		<TABLE BORDER="1" CELLPADDING="4" WIDTH="100%" >
			<TR>
				<TD>
					<?php echo $AppUI->_( "Company" ); ?>: <BR />
					<SELECT NAME="inventory_company" ID="companylist"
						CLASS="text" <?php if ( $inventory_parent ) echo "DISABLED "; ?>
						ONCHANGE="javascript:changeList( this, dept_lists, 'departmentlist' )">
					<?php
						foreach( $company_list as $row )
						{
							$compname = $row["company_name"];
							echo "<OPTION VALUE='".$row["company_id"].(($row[ "company_id" ] == $obj->inventory_company ) ? "' SELECTED>" : "'>" ).$compname;
						}
					?>
					</SELECT>
				</TD>
				<TD>
					<?php echo $AppUI->_( "Department" ); ?>: <BR />
					<SELECT NAME="inventory_department" ID="departmentlist"
						CLASS="text" <?php if ( $inventory_parent ) echo "DISABLED "; ?> >
					</SELECT>
				</TD>
			</TR>
		</TABLE>
		<BR />
		<?php echo $AppUI->_( "Assigned to" );?>:<BR />
		<TABLE BORDER="1" CELLPADDING="4" WIDTH="100%" >
			<TR>
				<TD>
					<?php echo $AppUI->_( "Project" ); ?>: <BR />
					<SELECT NAME="inventory_project" CLASS="text">
					<?php
						$usersql = "
						SELECT project_id, project_name
						FROM projects
						";
						
						$project_list = array();
						
						if (($rows = db_loadList( $usersql, NULL )))
						{
							foreach ($rows as $row)
							{
								if ( !getDenyRead( "projects", $row[ 'project_id' ] ) )
								{
									$project_list[ $row[ "project_id" ] ] = $row;
									echo "<OPTION VALUE='".$row["project_id"].(($row[ "project_id" ] == $obj->inventory_project ) ? "' SELECTED>":"'>").$row["project_name"];
								}
							}
						}
					?>
					</SELECT>
				</TD>
				<TD>
					<?php echo $AppUI->_( "User" ); ?>: <BR />
					<SELECT NAME="inventory_user" CLASS="text">
					<?php
						$usersql = "
						SELECT user_id, user_username, user_first_name, user_last_name
						FROM users
						";
						
						if (($rows = db_loadList( $usersql, NULL )))
						{
							foreach ($rows as $row)
							{
								$username = $row["user_first_name"]." ".$row["user_last_name"];
								echo "<OPTION VALUE='".$row["user_id"].(($row[ "user_id" ] == $obj->inventory_user ) ? "' SELECTED>" : "'>" ).$username;
							}
						}
				
					?>
					</SELECT>
				</TD>
			</TR>
		</TABLE>
	</TD>
</TR>
<TR>
	<TD COLSPAN="2">
		<HR />
		<?php echo $AppUI->_( 'Notes' ); ?>: <BR /> 
		<TEXTAREA CLASS="textarea" NAME="inventory_description" wrap="virtual" ROWS="10" COLS="60"><?php echo $obj->inventory_description; ?></TEXTAREA>
	</TD>
</TR>
<TR>
	<TD COLSPAN="2" ALIGN="right">
		<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_((($inventory_id)?'update':'add'));?>" onClick="submitIt();" />
	</TD>
</TR>
</FORM>
	
</TABLE>


<BR /><BR />

<?php
if ( $inventory_id )
{
	echo "<STRONG>".$AppUI->_("Sub-items").":</STRONG><BR />";
	$_GET[ 'children' ] = $inventory_id;
	include_once("{$AppUI->cfg['root_dir']}/modules/inventory/vw_idx_items.php");
	echo '<DIV style="text-align: right; padding: 4px; font-size: 14px;">';
		
	echo $AppUI->_( "Grand Total" );
	echo ":&nbsp;&nbsp;&nbsp;";
	echo $obj->inventory_cost + $obj->calcChildrenTotal( );
		
	echo '</DIV>';
}

?>




<SCRIPT LANGUAGE="JavaScript">
<!--
EnableDisable( 'category' );
EnableDisable( 'brand' );
<?php
	if ( $inventory_id )
	{
		echo "changeList( getElementById( 'companylist' ), dept_lists, 'departmentlist', "
			.$obj->inventory_department," );";
	}
?>

-->
</SCRIPT>

