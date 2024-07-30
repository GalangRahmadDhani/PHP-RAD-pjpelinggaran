<?php 
/**
 * Tabortu Page Controller
 * @category  Controller
 */
class TabortuapiController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "tabortu";
	}
	/**
     * List page records
     * @param $fieldname (filter record by a field) 
     * @param $fieldvalue (filter field value)
     * @return BaseView
     */
	function indexapi($fieldname = null, $fieldvalue = null) {
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array("id", "nama", "number");
	
		// Search table record
		if (!empty($request->search)) {
			$text = trim($request->search); 
			$search_condition = "(
				tabortu.id LIKE ? OR 
				tabortu.nama LIKE ? OR 
				tabortu.number LIKE ?
			)";
			$search_params = array(
				"%$text%", "%$text%", "%$text%"
			);
			$db->where($search_condition, $search_params);
		}
	
		// Ordering
		if (!empty($request->orderby)) {
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		} else {
			$db->orderBy("tabortu.id", ORDER_TYPE);
		}
	
		// Field filtering
		if ($fieldname) {
			$db->where($fieldname, $fieldvalue);
		}
	
		$db->where("tabguru.school_id", USER_SCHOOL_ID);
		$records = $db->get($tablename, null, $fields);
		$records_count = count($records);
	
		if ($db->getLastError()) {
			return render_json([
				'status' => 'error',
				'message' => 'Database error: ' . $db->getLastError(),
				'data' => []
			]);
		}
	
		return render_json([
			'status' => 'success',
			'data' => [
				'records' => $records,
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
	public function view($rec_id = null, $desc = null, $tgl = null, $jp = null) {
		$db = $this->GetModel();
		$rec_id = urldecode($rec_id);
		$tablename = $this->tablename;
		$fields = array("number"); // Only select number
	
		$db->where("tabortu.id", $rec_id); // select record based on primary key
		$record = $db->getOne($tablename, $fields);
	
		if ($record) {
			$number = $record['number']; // Extract the 'number' field
			// $longdesc = "$desc pada tanggal $tgl"; // Correctly format the description
			// Initialize IndexapiController and send the message
			$nobox = new IndexapiController();
			// $send = $nobox->sendMessage($number, $longdesc);
	
			return $number;
		} else {
			return render_json([
				'status' => 'error',
				'message' => $db->getLastError() ? $db->getLastError() : "No record found"
			]);
		}
	}
	
	
	
	/**
     * Insert new record to the database table
	 * @param $formdata array() from $_POST
     * @return BaseView
     */
	function addapi($formdata = null) {
		if (!$formdata) {
			return render_json([
				"status" => "error",
				"message" => "No form data provided"
			]);
		}
	
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = $this->fields = ["nama", "number", "school_id"];
		
		$postdata = $this->format_request_data($formdata);
		$postdata['school_id'] = USER_SCHOOL_ID;
	
		$this->rules_array = [
			'nama' => 'required',
			'number' => 'required'
		];
	
		$this->sanitize_array = [
			'nama' => 'sanitize_string',
			'number' => 'sanitize_string'
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
			return render_json([
				'status' => 'success',
				'message' => 'Record added successfully',
				'rec_id' => $rec_id
			]);
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
		$fields = $this->fields = ["id", "nama", "number"];
	
		if ($formdata) {
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = [
				'nama' => 'required',
				'number' => 'required',
			];
			$this->sanitize_array = [
				'nama' => 'sanitize_string',
				'number' => 'sanitize_string',
			];
			$modeldata = $this->modeldata = $this->validate_form($postdata);
	
			if (!$this->validated()) {
				return render_json([
					"status" => "error",
					"message" => "Validation failed",
					"errors" => $this->get_errors()
				]);
			}
	
			$db->where("tabortu.id", $rec_id);
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
	
		$db->where("tabortu.id", $rec_id);
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
		$fields = $this->fields = array("id","nama","number", "school_id");
		$page_error = null;
		if($formdata){
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'nama' => 'required',
				'number' => 'required',
				'school_id' => 'required'

			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'number' => 'sanitize_string',
				'school_id' => 'sanitize_string'
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("tabortu.id", $rec_id);;
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
		
		$db->where("tabortu.id", $arr_rec_id, "in");
		$bool = $db->delete($tablename);
	
		if ($bool) {
			return render_json([
				"status" => "success",
				"message" => "Record(s) deleted successfully",
				"deleted_records" => count($arr_rec_id)
			]);
		} elseif ($db->getLastError()) {
			return render_json([
				"status" => "error",
				"message" => "Database error: " . $db->getLastError()
			]);
		} else {
			return render_json([
				"status" => "warning",
				"message" => "No records were deleted"
			]);
		}
	}
}
