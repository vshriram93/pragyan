<?php
/**
 * @package pragyan
 * @author Boopathi Rajaa
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

class form2 implements module {
	private $userId;
	private $moduleComponentId;
	private $action;
	private $pageId;
	
	/*
	 * @function getHTML gateway through which the cms interacts with the module
	 */
	public function getHTML($guid, $gmoduleComponentId, $gaction) {
		$this->userId = $guid;
		$this->moduleComponentId = $gmoduleComponentId;
		$this->action = $gaction;
		$this->pageId = getPageIdFromModuleComponentId("form2",$gmoduleComponentId);
		
		switch($this->action) {
			case "edit":
				return $this->actionEdit();
				break;
			default: 
				return $this->actionView();
		}
	}
	
	public function actionView() {
		return "adfadsf";
	}

	public function actionEdit() {
		global $urlRequestRoot;
		global $cmsFolder;
		global $moduleFolder;
		$inc_path = "$urlRequestRoot/$cmsFolder/$moduleFolder/form2";
		
		$html = "";
		
		$html .= "<table><tr><td>";
		
		$html .= "<input type='button' value='+' onclick='addNewFormField(\"cms-form2\")' />";
		
		
		$html .= "<div class=\"cms-form2-node\"><form class=\"cms-form2\"></form></div>";
		$html .= <<<Script
		<script type="text/javascript">
			if(window.attachEvent) {
				window.attachEvent("onload", function(){
					if(typeof jQuery === "undefined")
						alert("You must include jQuery to use this page");
				});
			}
			else {
				window.addEventListener("load", function() {
					if(typeof jQuery === "undefined")
						alert("You must include jQuery to use this page");
				}, false);
			}
			if(typeof window.setAttributes != "function") {
				window.setAttributes = function(o,attr) {
					for(i in attr)
						o.setAttribute(i,attr[i]);
				}
			}
		</script>
		<script type="text/javascript" src="{$inc_path}/form2.js" ></script>
Script;
		
		$html .= "</td></tr></table>";
		return $html;
	}
	
	public function createModule($compId) {
		return true;
	}
	public function deleteModule($moduleComponentId) {
		return true;
	}
	public function copyModule($mid, $newId){
		return true;
	}
}

class Element {
	
	private $type;
	private $html;
	
	public function __construct($t="text") {
		$this->type = $t;
		$this->html="";
	}

	function getHTML($new) {
		return $this->html;
	}
}
