<?php

function nuUpdateData(){

	$d		= $_POST['nuSTATE']['data'];
	$ID		= $_POST['nuSTATE']['record_id'];
	$DEL		= $_POST['nuSTATE']['deleteAll'];	
	$fid		= $_POST['nuSTATE']['form_id'];
	$s		= "SELECT * FROM zzzzsys_form WHERE zzzzsys_form_id = '$fid'";
	$t		= nuRunQuery($s);
	$FORM	= db_fetch_object($t);

	if($DEL == 'Yes'){
		$before	= nuReplaceHashVariables(trim($FORM->sfo_before_delete_php));
		$after	= nuReplaceHashVariables(trim($FORM->sfo_after_delete_php));
	}else{
		$before	= nuReplaceHashVariables(trim($FORM->sfo_before_save_php));
		$after	= nuReplaceHashVariables(trim($FORM->sfo_after_save_php));
	}

	eval($before);

	if(count($_POST['nuErrors']) > 0){return;}

	for($i = 0 ; $i < count($d) ; $i++){
		
		if($d[$i]['pk'] == '-1'){
			
			$d[$i]['pk']	= nuID();
			
			if($d[$i]['fk'] == ''){				//-- main Edit For record
				$ID	= $d[$i]['pk'];
			}
			
		}
	}
	
	for($i = 0 ; $i < count($d) ; $i++){
		if($d[$i]['fk'] == '-1'){
			$d[$i]['fk']	= $d[0]['pk'];
		}
	}

	for($i = 0 ; $i < count($d) ; $i++){

		$pk		= $d[$i]['pk'];
		$t		= nuRunQuery("SELECT * FROM zzzzsys_form WHERE zzzzsys_form_id = ? ", array($d[$i]['fm']));
		$r		= db_fetch_object($t);
		$del		= $d[$i]['d'];

		if($del == 'Yes' or $DEL == 'Yes'){
			nuDeleteRow($r, $pk);
		}else{
			nuInsertRow($r, $pk);
			nuUpdateRow($r, $pk, $d[$i], $ID);
		}
		
	}

	eval($after);

	return $ID;
	
}

function nuDeleteRow($r, $p){

	nuRunQuery("DELETE FROM `$r->sfo_table` WHERE `$r->sfo_primary_key` = ? ", array($p));
	
}

function nuInsertRow($r, $p){
	
	$T	= nuRunQuery("SELECT COUNT(*) FROM `$r->sfo_table` WHERE `$r->sfo_primary_key` = ? ", array($p));
	$R	= db_fetch_row($T);
	
	if($R[0] == 0){
		nuRunQuery("INSERT INTO `$r->sfo_table` (`$r->sfo_primary_key`) VALUES (?) ", array($p));
	}

}

function nuUpdateRow($r, $p, $row, $FK){
	
	if($row['fk'] != ''){		//-- (if == it's not the parent record add foreign key)
	
		$row['f'][]	= $row['ff'];
		$row['v'][]	= $FK;
		
	}
	
	$set		= array();
	
	for($i = 0 ; $i < count($row['f']) ; $i++){
		
		$set[] 	= $row['f'][$i] . ' = "' . nuFormatValue($row, $i) . '"';
		
	}
	
	$s	= "UPDATE `$r->sfo_table` SET " . implode(', ', $set) . " WHERE `$r->sfo_primary_key` = ? ";

	nuRunQuery($s, array($p));
	
}

function nuFormatValue($row, $i){
	
	$form_id	= $row['fm'];
	$field	= $row['f'][$i];
	
	$s		= "SELECT * FROM zzzzsys_object WHERE sob_all_zzzzsys_form_id = ? AND sob_all_id = ? ";
	$t		= nuRunQuery($s, array($form_id, $field));
	$r		= db_fetch_object($t);
	
	if($r->sob_all_type == 'select' and $r->sob_select_multiple == '1' and $row['v'][$i] != ''){
		return implode('#nuSep#', $row['v'][$i]);
	}else{
		return $row['v'][$i];
	}

}

function nuReplaceHashVariables($s){

	$a		= $_POST['nuHash'];

	foreach ($a as $k => $v) {
		$s	= str_replace ('#' . $k . '#', $v, $s);
	}

	return $s;

}


function nuErrorMessage($m){

	$_POST['nuErrors'][]	= $m;

}



?>