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
		$sqltext = "SELECT id AS value , id AS label FROM tabsiswa ORDER BY label ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabpelanggaran_jpelanggaran_id_option_list Model Action
     * @return array
     */
	function tabpelanggaran_jpelanggaran_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT id AS value , id AS label FROM tabjenispelanggaran ORDER BY label ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabsiswa_kelas_id_option_list Model Action
     * @return array
     */
	function tabsiswa_kelas_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT id AS value,nama AS label FROM tabkelas ORDER BY id ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabsiswa_jurusan_id_option_list Model Action
     * @return array
     */
	function tabsiswa_jurusan_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT DISTINCT id AS value , id AS label FROM tabjurusan ORDER BY label ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabsiswa_ortu_id_option_list Model Action
     * @return array
     */
	function tabsiswa_ortu_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT DISTINCT id AS value , id AS label FROM tabortu ORDER BY label ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * tabsiswa_guru_id_option_list Model Action
     * @return array
     */
	function tabsiswa_guru_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT DISTINCT id AS value , id AS label FROM tabguru ORDER BY label ASC";
		$queryparams = null;
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

}
