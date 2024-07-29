<?php 

/**
 * SharedController Controller
 * @category  Controller / Model
 */
class SharedController extends BaseController{
	
	/**
     * tabpelanggaran_siswa_id_option_list Model Action
     * @return array
     */
	function tabpelanggaran_siswa_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT id AS value , nama AS label FROM tabsiswa WHERE school_id = ? ORDER BY label ASC";
		$queryparams = [USER_SCHOOL_ID];
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabpelanggaran_jpelanggaran_id_option_list Model Action
     * @return array
     */
	function tabpelanggaran_jpelanggaran_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT id AS value , nama AS label FROM tabjenispelanggaran WHERE school_id = ? ORDER BY label ASC";
		$queryparams = [USER_SCHOOL_ID];
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabsiswa_kelas_id_option_list Model Action
     * @return array
     */
	function tabsiswa_kelas_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT id AS value, nama AS label FROM tabkelas WHERE school_id = ? ORDER BY id ASC";
		$queryparams = [USER_SCHOOL_ID];
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabsiswa_jurusan_id_option_list Model Action
     * @return array
     */
	function tabsiswa_jurusan_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT id AS value, nama AS label FROM tabjurusan WHERE school_id = ? ORDER BY id ASC";
		$queryparams = [USER_SCHOOL_ID];
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabsiswa_ortu_id_option_list Model Action
     * @return array
     */
	function tabsiswa_ortu_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT id AS value , id AS label FROM tabortu WHERE school_id = ? ORDER BY label ASC";
		$queryparams = [USER_SCHOOL_ID];
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabsiswa_guru_id_option_list Model Action
     * @return array
     */
	function tabsiswa_guru_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT id AS value , id AS label FROM tabguru WHERE school_id = ? ORDER BY label ASC";
		$queryparams = [USER_SCHOOL_ID];
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabuser_user_role_id_option_list Model Action
     * @return array
     */
	function tabuser_user_role_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT role_id AS value,role_name AS label FROM roles ORDER BY role_id ASC";
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

	/**
     * getcount_lakilaki Model Action
     * @return Value
     */
	function getcount_lakilaki(){
		$db = $this->GetModel();
		$sqltext = "SELECT COUNT(*) AS jumlah_laki FROM tabsiswa WHERE jenkel = 1;";
		$queryparams = null;
		$val = $db->rawQueryValue($sqltext, $queryparams);
		
		if(is_array($val)){
			return $val[0];
		}
		return $val;
	}

	/**
     * getcount_perempuan Model Action
     * @return Value
     */
	function getcount_perempuan(){
		$db = $this->GetModel();
		$sqltext = "SELECT COUNT(*) AS jumlah_perempuan FROM tabsiswa WHERE jenkel = 2;";
		$queryparams = null;
		$val = $db->rawQueryValue($sqltext, $queryparams);
		
		if(is_array($val)){
			return $val[0];
		}
		return $val;
	}

}