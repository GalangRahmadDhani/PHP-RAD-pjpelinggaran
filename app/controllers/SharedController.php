<?php 

/**
 * SharedController Controller
 * @category  Controller / Model
 */
class SharedController extends BaseController{
	
	/**
     * tabuser_user_role_id_option_list Model Action
     * @return array
     */
	function tabuser_user_role_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT role_id AS value, role_name AS label FROM roles";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabuser_school_id_option_list Model Action
     * @return array
     */
	function tabuser_school_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT id AS value,nama AS label FROM tabsekolah ORDER BY id ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabuser_nama_value_exist Model Action
     * @return array
     */
	function tabuser_nama_value_exist($val){
		$db = $this->GetModel();
		$db->where("nama", $val);
		$exist = $db->has("tabuser");
		return $exist;
	}

	/**
     * tabuser_email_value_exist Model Action
     * @return array
     */
	function tabuser_email_value_exist($val){
		$db = $this->GetModel();
		$db->where("email", $val);
		$exist = $db->has("tabuser");
		return $exist;
	}

}
