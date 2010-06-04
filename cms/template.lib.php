<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
function getPageTemplate($pageId)
{
 	
 	$query="SELECT `value` FROM `".MYSQL_DATABASE_PREFIX."global` WHERE `attribute`='allow_pagespecific_template'";
	$result=mysql_query($query);
	$row=mysql_fetch_row($result);
 	if($row[0]==0)
 		return DEF_TEMPLATE;

 	$query="SELECT `page_template` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=$pageId";
	$result=mysql_query($query);
	$row=mysql_fetch_row($result);
 	if($row[0]=="")
 		return DEF_TEMPLATE;
 	return $row[0];
}

function templateReplace(&$TITLE,&$MENUBAR,&$ACTIONBARMODULE,&$ACTIONBARPAGE,&$BREADCRUMB,&$INHERITEDINFO,&$CONTENT,&$DEBUGINFO,&$ERRORSTRING,&$WARNINGSTRING,&$INFOSTRING,&$STARTSCRIPTS) {
	global $cmsFolder;
	global $sourceFolder;
	global $templateFolder;
	global $moduleFolder;
	global $urlRequestRoot;
	global $TEMPLATEBROWSERPATH;
	global $TEMPLATECODEPATH;
	$TEMPLATEBROWSERPATH = "$urlRequestRoot/$cmsFolder/$templateFolder/".TEMPLATE;
	$TEMPLATECODEPATH = "$sourceFolder/$templateFolder/".TEMPLATE;
	include ($TEMPLATECODEPATH."/index.php");
}

function actualPath($templatePath) {
	$templateActualPath = $templatePath;
	$dirHandle = opendir($templatePath);
	$files = '';
	while($file = readdir($dirHandle)) {
		if($file == "index.php")
			return $templatePath;
		elseif(is_dir($templatePath . $file) && $file != '.' && $file != '..') {
			$return = actualPath($templatePath . $file . "/");
			if($return != NULL)
				return $return;
		}
	}
	return NULL;
}

function installTemplate($str) {
	global $sourceFolder;
	$len = strlen($str);
	$templateName = name($str,".");
	if(substr($str,$len-4,4)==".zip") {
		$zip = new ZipArchive();
		if ($zip->open($str) === TRUE) {
			$templatePath = $sourceFolder . "/uploads/templates/" . $templateName . "/";
			while(file_exists($templatePath))
				$templatePath = $sourceFolder . "/uploads/templates/". rand() . "/";
			$zip->extractTo($templatePath);
			$zip->close();
		} else
			return array("1", $str);
	} else
		return array("2", $str);
	
	$templateArray = "";
	$templates=getAvailableTemplates();
	foreach($templates as $template)
		$templateArray .= "'".$template."', ";
		
	$templateArray = rtrim($templateArray,", ");
	
	$templateActualPath = actualPath($templatePath);

	if($templateActualPath == NULL)
		return array("0", $str, $templatePath);
	
	$call = "";
	$issueExcess = "";
	$ignoreall = "";
	$issues = "";
	$issuetypes = reportIssues($templateActualPath,$issues);
	if($issues!="")
	{
	 $issues ="
	 <table name='issues_table'>
	 <tr><th>S.No.</th><th>Issue Details</th><th>Issue Type</th><th>Ignore ?</th></tr>
	 $issues
	 </table>
	 ";
	}
	
	if($issuetypes[0] == 1)
	{
	 //$issuetypes[0] is fatal and [1] is ignorable
	 	displayerror("Some fatal issues were found with the template. Please click on Cancel Installation button and fix the issues");
		$call = "2";
	}
	if($issuetypes[0] == 0 && $issuetypes[1] == 1) {
		displaywarning("Some issues were found with the template. You may chose to ignore them.");
		$ignoreall = "<input type=button value='Ignore All' onClick='igall();'>";
		$issueExcess = <<<EXTRA
<script type="text/javascript">

function igall() {
	var id = 0;
	while(document.getElementById('issue_' + id))
		ignore(id++);
}
</script>
EXTRA;
	}
	
	$RET = <<<RET
<script type="text/javascript">
function ignore(id) {
	if(document.getElementById('button_' + id)) {
		document.getElementById('issue_' + id).className = 'ignored';
		document.getElementById('button_' + id).value = 'Ignored !';
		document.getElementById('button_' + id).disabled = 'disabled';
	}
}
function validate() {
	var id = 0;
	while(document.getElementById('issue_' + id)) {
		if(document.getElementById('issue_' + id).className == 'issue') {
			alert("There are one or more issue(s) unresolved. Fix them and Submit.");
			return false;
		}
		id++;
	}
	var templates = new Array('common',{$templateArray});
	for(template in templates)
		if(document.getElementById('templatename').value == templates[template]) {
			alert("Template with that name already exist in server. Choose some other name.");
			return false;
		}
	return true;
}
function validate2() {
	alert("You have one or more required variable missing. So you can not submit the template. Hit cancel.");
	return false;
}
</script>

<fieldset>
<legend>Finalize Template</legend>
{$issues}
{$ignoreall}
{$issueExcess}
<form method=POST action='./+admin&subaction=template&subsubaction=finalize' onSubmit='return validate{$call}()'>
Template Name: <input type=text id='templatename' name='template' value='{$templateName}'><input type=submit value="Install Template"><br/><br/>
The following template names are already used :<b> 'common', {$templateArray}</b><br/>
<input type=hidden name='path' value='{$templateActualPath}'>
<input type=hidden name='del' value='{$templatePath}'>
<input type=hidden name='file' value='{$str}'>

</form>
<form method=POST action='./+admin&subaction=template&subsubaction=cancel' onSubmit='myconfirm()'>
<input type=hidden name='path' value='{$templatePath}'>
<input type=hidden name='file' value='{$str}'>
<input type=submit value="Cancel Installation">
</form>
</fieldset>
RET;

	return $RET;
}

/*
this is a custom function which i needed might not be of much significance
it returns the substring starting right next from the last '/' and ends just before the end character(2nd parameter) specified
*/
function name($path,$end) {
	$len = strlen($path);
	$start = strrpos($path,"/");
	$end = strpos($path,$end,$start);
	return substr($path,$start+1,$end-$start-1);
}

/*
checkTemplate(templatePath) is used to check for compatibility with the pragyan cms
you can redistribute the values in reqd and nreqd as per your requirement
if a variables in nreqd is missing in the template, it'll be notified during installation, but can be ignored
whereas variables in reqd cant be ignored.
This function returns
	0: if it doesn't find index.php in the passed path
	1: if it finds index.php in the specified path and it contains all variables specified in reqd and nreqd arrays.
	2 and above: if it finds index.php in the specified path and it miss n-1 variables in reqd and nreqd arrays.
*/
function addissue(&$issues,$str,$id)
{
	$issues.="<tr><td>$id</td><td>$str</td><td>Warning</td><td><input type=hidden id='issue_{$id}' class=issue><input type=button id='button_{$id}' value=Ignore onclick='ignore($id)'></td></tr>";
}
function addfatalissue(&$issues,$str,$id)
{
	$issues.="<tr><td>$id</td><td>$str</td><td><b>FATAL</b></td><td><input type=hidden id='issue_{$id}' class=issue>Can't Ignore !</td></tr>";
}


function reportIssues($templatePath,&$issues) {
	$content = file_get_contents($templatePath . "index.php");
	$reqd = array("\$CONTENT","\$ACTIONBARMODULE","\$ACTIONBARPAGE");
	$nreqd = array("\$STARTSCRIPTS","\$TITLE","\$BREADCRUMB","\$DEBUGINFO","\$MENUBAR","\$INHERITEDINFO","\$ERRORSTRING","\$WARNINGSTRING","\$INFOSTRING");
	$id = 0;
	$i = 0;
	$j = 0;
	foreach($reqd as $var)
		switch(mycount($content,$var)) {
			case 0:
				addfatalissue($issues,"$var is missing",$id);
				$i = 1;
				$id++;
				break;
			case 1:
				break;
			default:
				addissue($issues,"$var is more than once",$id);
				
				$id++;
		}
	foreach($nreqd as $var)
		switch(mycount($content,$var)) {
			case 0:
				addissue($issues,"$var is missing",$id);
				$j = 1;
				$id++;
				break;
			case 1:
				break;
			default:
				addissue($issues,"$var is more than once",$id);
				$j = 1;
				$id++;
		}
	return array($i,$j);		//returns 1 more than number of issues. see id getting incremented for every issue.
}

function mycount($content,$find) {
	$start = strpos($content,$find);
	if($start)
		if(strpos($content,$find,$start+1))
			return 2;	//to indicate the presence of 'find value' more than once
		else
			return 1;	//to indicate the presence of 'find value' once
	else
		return 0;		//to indicate the 'find value' is not found
}


function handleTemplateMgmt()
{
	global $sourceFolder;
	if(isset($_POST['btn_install']))
	{
		if(!file_exists($sourceFolder . "/uploads/templates/"))
			mkdir($sourceFolder . "/uploads/templates/");
		$str = $sourceFolder ."/uploads/templates/".$_FILES['file']['name'];
		$ext = extension($str);
		while(file_exists($str))
			$str = $sourceFolder . "/uploads/templates/" . rand() . $ext;
		move_uploaded_file($_FILES['file']['tmp_name'],$str);
		require_once("template.lib.php");
		$return = installTemplate($str);
		switch($return[0]) 
		{
			case "0":
				displayerror("index.php not found");
				delDir($return[2]);
				unlink($return[1]);
		
				break;
			case "1":
				displayerror("Error while opening archive");
				unlink($return[1]);
			
				break;
			case "2":
				displayinfo("Please upload a ZIP file");
				unlink($return[1]);
			
				break;
			default:
				return $return;
		}
		
	}
	else if(isset($_POST['btn_uninstall']))		
	{
		$query="SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "templates` WHERE `template_name` = '" . escape($_GET['deltemplate']) . "'";
		
		if($row = mysql_fetch_array(mysql_query($query)))
		{
			$query="DELETE FROM `" . MYSQL_DATABASE_PREFIX . "templates` WHERE `template_name` = '" . escape($_GET['deltemplate']) . "'";
			mysql_query($query);
		}
		$templateDir = $sourceFolder . "/templates/" . escape($_GET['deltemplate']) . "/";
		if(file_exists($templateDir))
			delDir($templateDir);
		displayinfo("Template ".safe_html($_GET['deltemplate'])." uninstalled!");
		return "";
	} 
	else if(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'finalize') 
	{		
	
		$templates=getAvailableTemplates();
		$flag=false;
		foreach ($templates as $template) 
			if($template==$_POST['template'])
			{
				$flag=true;
				break;
			}
		if($_POST['template']=="common" || $flag) 
		{
			displayerror("Template Installation failed : A folder by the template name already exists.");
			$templatePath=safe_html($_POST['del']);
			$str=safe_html($_POST['file']);
			$ret=<<<RET
			<form method=POST action='./+admin&subaction=canceltemplate'>
			Please click the following button to start a fresh installation : 
			<input type=hidden name='path' value='{$templatePath}'>
			<input type=hidden name='file' value='{$str}'>
			<input type=submit value="Fresh Installation">
			</form>
RET;
			return $ret;
			
		}
		rename(escape($_POST['path']), $sourceFolder . "/templates/" . escape($_POST['template']) . "/");
		delDir(escape($_POST['del']));
		unlink(escape($_POST['file']));
		mysql_query("INSERT INTO `" . MYSQL_DATABASE_PREFIX . "templates` VALUES('" . escape($_POST['template']) . "')");
		displayinfo("Template installation complete");
		return "";
		
	} 
	else if(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'cancel') 
	{
		delDir(escape($_POST['path']));
		unlink(escape($_POST['file']));
		return "";
	}
	
}
