<?php 
/**
 * Tabpelanggaran Page Controller
 * @category  Controller
 */
class TabpelanggaranapiController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "tabpelanggaran";
	}
	/**
     * List page records
     * @param $fieldname (filter record by a field) 
     * @param $fieldvalue (filter field value)
     * @return BaseView
     */
	function indexapi($id = null) {
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array(
			"tabpelanggaran.id", 
			"tabpelanggaran.siswa_id", 
			"tabpelanggaran.jpelanggaran_id", 
			"tabpelanggaran.tgl", 
			"tabpelanggaran.deskripsi",
			"tabortu.number AS number"
		);
	
		// If ID is provided, fetch only that record
		if ($id !== null) {
			$db->where("tabpelanggaran.id", $id);
		} else {
			// Search table record
			if (!empty($request->search)) {
				$text = trim($request->search); 
				$search_condition = "(
					tabpelanggaran.id LIKE ? OR 
					tabpelanggaran.siswa_id LIKE ? OR 
					tabpelanggaran.jpelanggaran_id LIKE ? OR 
					tabpelanggaran.tgl LIKE ? OR 
					tabpelanggaran.deskripsi LIKE ?
				)";
				$search_params = array(
					"%$text%","%$text%","%$text%","%$text%","%$text%"
				);
				$db->where($search_condition, $search_params);
			}
	
			// Ordering
			if (!empty($request->orderby)) {
				$orderby = $request->orderby;
				$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
				$db->orderBy($orderby, $ordertype);
			} else {
				$db->orderBy("tabpelanggaran.id", ORDER_TYPE);
			}
		}
	
		// Modify the query to include joins
		$db->join("tabsiswa", "tabpelanggaran.siswa_id = tabsiswa.id", "LEFT");
		$db->join("tabortu", "tabsiswa.ortu_id = tabortu.id", "LEFT");
	
		// Add school filter
		$db->where("tabpelanggaran.school_id", USER_SCHOOL_ID);
	
		// Fetch records
		$records = $db->get($tablename, null, $fields);
		$records_count = count($records);
	
		if ($db->getLastError()) {
			return render_json([
				'status' => 'error',
				'message' => 'Database error: ' . $db->getLastError(),
				'data' => []
			]);
		}
	
		// If ID was provided but no record found
		if ($id !== null && $records_count === 0) {
			return render_json([
				'status' => 'error',
				'message' => 'Record not found',
				'data' => []
			]);
		}
	
		return render_json([
			'status' => 'success',
			'data' => [
				'records' => $id !== null ? $records[0] : $records,
				'record_count' => $records_count
			]
		]);
	}
	
	/**
     * View record detail 
	 * @param $rec_id (select record by table primary key) 
     * @param $value value (select record by value of field name(rec_id))
     * @return BaseView
     */
	function view($rec_id = null, $value = null){
		$request = $this->request;
		$db = $this->GetModel();
		$rec_id = $this->rec_id = urldecode($rec_id);
		$tablename = $this->tablename;
		$fields = array("id", 
			"siswa_id", 
			"jpelanggaran_id", 
			"tgl", 
			"deskripsi", 
			"posted_by", 
			"school_id");
		if($value){
			$db->where($rec_id, urldecode($value)); //select record based on field name
		}
		else{
			$db->where("tabpelanggaran.id", $rec_id);; //select record based on primary key
		}
		$record = $db->getOne($tablename, $fields );
		if($record){
			$page_title = $this->view->page_title = "View  Tabpelanggaran";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "portrait";
		}
		else{
			if($db->getLastError()){
				$this->set_page_error();
			}
			else{
				$this->set_page_error("No record found");
			}
		}
		return $this->render_view("tabpelanggaran/view.php", $record);
	}
	/**
     * Insert new record to the database table
	 * @param $formdata array() from $_POST
     * @return BaseView
     */
	public function addapi($formdata = null) {
		if (!$formdata) {
			return render_json([
				"status" => "error",
				"message" => "No form data provided"
			]);
		}
	
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = $this->fields = ["siswa_id", "jpelanggaran_id", "tgl", "deskripsi", "school_id", "ortu_number"];
	
		$postdata = $this->format_request_data($formdata);
		$postdata['school_id'] = USER_SCHOOL_ID;
	
		$ortu_id = $this->getOrtuId($postdata['siswa_id']);
		$postdata['ortu_number'] = $this->getOrtuNumber($ortu_id);
	
		$this->rules_array = [
			'siswa_id' => 'required',
			'jpelanggaran_id' => 'required',
			'tgl' => 'required',
			'deskripsi' => 'required',
			'school_id' => 'required',
			'ortu_number' => 'required'
		];
	
		$this->sanitize_array = [
			'siswa_id' => 'sanitize_string',
			'jpelanggaran_id' => 'sanitize_string',
			'tgl' => 'sanitize_string',
			'deskripsi' => 'sanitize_string',
			'school_id' => 'sanitize_string',
			'ortu_number' => 'sanitize_string'
		];
	
		$this->filter_vals = true;
		$modeldata = $this->modeldata = $this->validate_form($postdata);
	
		if (!$this->validated()) {
			return render_json([
				"status" => "error",
				"message" => "Validation failed",
				"errors" => $this->get_errors()
			]);
		}
	
		$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
	
		if ($rec_id) {
			$jpelanggaran_id = $modeldata['jpelanggaran_id'];
	
			// Get the name of the jenis pelanggaran
			$jenispelanggaranController = new TabjenispelanggaranapiController();
			$jenispelanggaranResult = $jenispelanggaranController->view($jpelanggaran_id);
	
			if ($jenispelanggaranResult['status'] === 'success') {
				$jenisPelanggaranNama = $jenispelanggaranResult['record']['nama'];
	
				return render_json([
					'status' => 'success',
					'message' => 'Record added successfully',
					'rec_id' => $rec_id,
				]);
			} else {
				return render_json([
					"status" => "error",
					"message" => "Failed to get jenis pelanggaran"
				]);
			}
		} else {
			return render_json([
				"status" => "error",
				"message" => "Failed to insert record",
				"db_error" => $db->getLastError()
			]);
		}
	}
	
	/**
     * Update table record with formdata
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function editapi($rec_id = null, $formdata = null) {
		if (!$rec_id) {
			return render_json([
				"status" => "error",
				"message" => "No record ID provided"
			]);
		}
	
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		$fields = $this->fields = ["id", "siswa_id", "jpelanggaran_id", "tgl", "deskripsi"];
	
		if ($formdata) {
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = [
				// 'siswa_id' => 'required',
				'jpelanggaran_id' => 'required',
				'tgl' => 'required',
				'deskripsi' => 'required',
			];
			$this->sanitize_array = [
				// 'siswa_id' => 'sanitize_string',
				'jpelanggaran_id' => 'sanitize_string',
				'tgl' => 'sanitize_string',
				'deskripsi' => 'sanitize_string',
			];
			$modeldata = $this->modeldata = $this->validate_form($postdata);
	
			if (!$this->validated()) {
				return render_json([
					"status" => "error",
					"message" => "Validation failed",
					"errors" => "Terjadi kesalahan, periksa kembali field nya",
				]);
			}
	
			$db->where("tabpelanggaran.id", $rec_id);
			$bool = $db->update($tablename, $modeldata);
			$numRows = $db->getRowCount();
	
			if ($bool && $numRows) {
				return render_json([
					"status" => "success",
					"message" => "Record updated successfully",
					"num_rows" => $numRows
				]);
			} else {
				if ($db->getLastError()) {
					return render_json([
						"status" => "error",
						"message" => "Database error: " . $db->getLastError()
					]);
				} elseif (!$numRows) {
					return render_json([
						"status" => "warning",
						"message" => "No record updated"
					]);
				}
			}
		}
	
		$db->where("tabpelanggaran.id", $rec_id);
		$data = $db->getOne($tablename, $fields);
	
		if (!$data) {
			return render_json([
				"status" => "error",
				"message" => "Record not found"
			]);
		}
	
		return render_json([
			"status" => "success",
			"data" => $data
		]);
	}
	/**
     * Update single field
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function editfield($rec_id = null, $formdata = null){
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		//editable fields
		$fields = $this->fields = array("id","siswa_id","jpelanggaran_id","tgl","deskripsi");
		$page_error = null;
		if($formdata){
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'siswa_id' => 'required',
				'jpelanggaran_id' => 'required',
				'tgl' => 'required',
				'deskripsi' => 'required',
			);
			$this->sanitize_array = array(
				'siswa_id' => 'sanitize_string',
				'jpelanggaran_id' => 'sanitize_string',
				'tgl' => 'sanitize_string',
				'deskripsi' => 'sanitize_string',
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("tabpelanggaran.id", $rec_id);;
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount();
				if($bool && $numRows){
					return render_json(
						array(
							'num_rows' =>$numRows,
							'rec_id' =>$rec_id,
						)
					);
				}
				else{
					if($db->getLastError()){
						$page_error = $db->getLastError();
					}
					elseif(!$numRows){
						$page_error = "No record updated";
					}
					render_error($page_error);
				}
			}
			else{
				render_error($this->view->page_error);
			}
		}
		return null;
	}
	/**
     * Delete record from the database
	 * Support multi delete by separating record id by comma.
     * @return BaseView
     */
	function deleteapi($rec_id = null) {
		Csrf::cross_check();
	
		if (!$rec_id) {
			return render_json([
				"status" => "error",
				"message" => "No record ID provided"
			]);
		}
	
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$this->rec_id = $rec_id;
	
		// Form multiple delete, split record id separated by comma into array
		$arr_rec_id = array_map('trim', explode(",", $rec_id));
		
		// Check if records exist before deleting
		$db->where("id", $arr_rec_id, "in");
		$existing_records = $db->get($tablename, null, "id");
		$existing_count = count($existing_records);
	
		if ($existing_count == 0) {
			return render_json([
				"status" => "warning",
				"message" => "No matching records found to delete"
			]);
		}
	
		// Perform deletion
		$db->where("id", $arr_rec_id, "in");
		$bool = $db->delete($tablename);
		
		if ($bool) {
			// Verify deletion by checking if records still exist
			$db->where("id", $arr_rec_id, "in");
			$remaining_records = $db->get($tablename, null, "id");
			$deleted_count = $existing_count - count($remaining_records);
	
			if ($deleted_count > 0) {
				return render_json([
					"status" => "success",
					"message" => "Record(s) deleted successfully",
					"deleted_records" => $deleted_count
				]);
			} else {
				return render_json([
					"status" => "error",
					"message" => "Failed to delete records"
				]);
			}
		} else {
			return render_json([
				"status" => "error",
				"message" => "Database error: " . $db->getLastError()
			]);
		}
	}

	public function getOrtuId($id){
		$siswa = new TabsiswaapiController();
		$ortu_id = $siswa->view($id);
		return $ortu_id;
	}

	public function getOrtuNumber($id){
		$ortu = new TabortuapiController();
		$number = $ortu->view($id);
		return $number;
	}

	public function sendmessage() {
		$request = $this->request;
		$db = $this->GetModel();
		$rec_id = $_POST['rec_id']; // Mengakses 'rec_id' dari $_POST
		$tablename = $this->tablename;
		$fields = array(
			"id", 
			"siswa_id", 
			"jpelanggaran_id", 
			"tgl", 
			"deskripsi", 
			"posted_by", 
			"school_id",
			"ortu_number"
		);
	
		// Menggunakan rec_id untuk mendapatkan record
		$db->where("tabpelanggaran.id", $rec_id);
		$record = $db->getOne($tablename, $fields);
	
		if ($record) {
			$page_title = $this->view->page_title = "View Tabpelanggaran";
			$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
			$this->view->report_title = $page_title;
			$this->view->report_layout = "report_layout.php";
			$this->view->report_paper_size = "A4";
			$this->view->report_orientation = "portrait";
			
			return $this->render_view("tabpelanggaran/view.php", $record);
		} else {
			if ($db->getLastError()) {
				$this->set_page_error($db->getLastError());
			} else {
				$this->set_page_error("No record found");
			}
		}
	
		return render_json($record);
	}
	
}
