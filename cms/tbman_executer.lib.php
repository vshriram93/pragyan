<?php
class tbman_executer {
	private $pv; //postvariables
	private $actions;
	private $query = "";
	private $externalquery = "";
	private $fields;
	//  private $result;// to generate "WHERE ...." string
	public $formaction;
	public $extra_where;

	function tbman_executer($postvariables, $extra = "") //or simply the querystring the first time
	{
		if(is_string($postvariables))
			$this->externalquery = $postvariables;
		else if(is_array($postvariables)) 	{
			$this->pv = $postvariables;
			$this->actions = explode("|", $this->pv['buttonpressed']);
			$this->fields = explode("|", $this->pv['fields']);
			$this->externalquery = $this->pv['querystring'];
		} else {
			$this->pv = $postvariables;
		}
		//to generate the "WHERE..." string
//		if ($this->pv['querystring'] == "")
//			$this->pv['querystring'] = "SHOW TABLES";
//		@ $result = mysql_query($this->pv['querystring']);
//		if (!$result) {
//			echo ("Error line 26: " . mysql_error());
//		} else
//			$this->result = $result;
	}

	function execute() {
		if (isset ($this->pv['tablename'])) {
			$this->make_query();
			$fields = explode(";", $this->query);
			foreach ($fields as $tok) {
				if ($tok == "")
					continue;
				@ $result = mysql_query($tok);
				if (!$result)
					return ("Error line 42 (tbman_executer.lib.php): " . mysql_error());
			}
		}
		require_once ("tbman_renderer.lib.php");
		echo $this->pv['querystring'];
		$rendertable = new tbman($this->externalquery);
		$rendertable->formaction = $this->formaction;
		return $rendertable->make_table();
	}

	function make_query() {
		$pv = $this->pv;
		$actions = $this->actions;
		$j = 1;
		if ($actions[0] == "updatebutton") {
			$i = 0;
			for (; $i < $pv['noOfRows']; $i++) {
				if ($actions[$j] == $i) {
					$j++;
					$this->update($i);
				}
			}
			while (isset ($actions[$j])) {
				$this->addrow($i);
				$j++;
			}
		}
		elseif ($this->actions[0] == "deletebutton") {
			for ($i = 0; $i < $pv['noOfRows']; $i++) {
				if ($actions[$j] == $i) {
					$j++;
					$this->delete($i);
				}
			}
		}
	}
	function delete($i) {
		$str = " DELETE FROM " . $this->pv['tablename'] . $this->get_wherestring($i);
		$this->query .= $str . ";";
	}
	function update($i) //also for addrow
	{
		$pv = $this->pv;
		$str = " UPDATE " . $pv['tablename'] . " SET ";
		foreach ($this->fields as $field) {
			$str .= "`" . $field . "` = '" . $pv[$field . $i] . "' ,";
		}
		$str = substr($str, 0, -1);
		$str .= $this->get_wherestring($i);
		$this->query .= $str . ";";
	}
	function get_wherestring($i) {
		mysql_data_seek($this->result, $i);
		$row = mysql_fetch_assoc($this->result);
		$str = " WHERE ";
		//if(isset($this->extra_where)) { $str .= $this->extra_where." AND "; }		
		foreach ($row as $field => $value) {
			$str .= "`" . $field . "` = '" . $value . "' AND ";
		}
		$str .= " 1";
		return $str;
	}
	function addrow($i) {
		$pv = $this->pv;
		$str = " INSERT INTO " . $pv['tablename'] . " (";
		$s = 1;
		$ss = sizeof($this->fields);
		foreach ($this->fields as $field) {
			$str .= " `" . $field . "` ";
			if ($s < $ss) {
				$str .= ", ";
				$s++;
			}
		}

		$str .= " ) VALUES ( ";
		foreach ($this->fields as $field) {
			$str .= " '" . $pv[$field . "addRow"] . "' ,";
		}
		$str = substr($str, 0, -1);
		$str .= " ) ";
		$this->query .= $str . ";";
	}
}