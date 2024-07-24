<?php 
/**
 * Tabguru Page Controller
 * @category  Controller
 */
class TabguruapiController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "tabguru";
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
		$fields = array(
			"tabguru.id", 
			"tabguru.nama", 
			"tabguru.school_id", 
			"tabuser.school_id AS tabuser_school_id"
		);
	
		// Search table record
		if (!empty($request->search)) {
			$text = trim($request->search); 
			$search_condition = "(
				tabguru.id LIKE ? OR 
				tabguru.nama LIKE ? OR 
				tabguru.school_id LIKE ? OR 
				tabuser.id LIKE ? OR 
				tabuser.nama LIKE ? OR 
				tabuser.email LIKE ? OR 
				tabuser.password LIKE ? OR 
				tabuser.image LIKE ? OR 
				tabuser.user_role_id LIKE ? OR 
				tabuser.school_id LIKE ? OR 
				tabguru.date_deleted LIKE ? OR 
				tabguru.is_deleted LIKE ?
			)";
			$search_params = array_fill(0, 12, "%$text%");
			$db->where($search_condition, $search_params);
		}
	
		$db->join("tabuser", "tabguru.school_id = tabuser.school_id", "INNER");
	
		if (!empty($request->orderby)) {
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		} else {
			$db->orderBy("tabguru.id", ORDER_TYPE);
		}
	
		if ($fieldname) {
			$db->where($fieldname, $fieldvalue); // Filter by a single field name
		}
	
		// Filter to show only records with the same school_id as the logged-in user
		$db->where("tabguru.school_id", USER_SCHOOL_ID);
		$records = $db->get($tablename, null, $fields);
	
		if ($db->getLastError()) {
			echo json_encode(['error' => $db->getLastError()]);
		} else {
			echo json_encode($records);
		}
		exit;
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
		$fields = array("tabguru.id", 
			"tabguru.nama", 
			"tabguru.posted_by", 
			"tabguru.school_id", 
			"tabuser.school_id AS tabuser_school_id", 
			"tabuser.id AS tabuser_id", 
			"tabuser.nama AS tabuser_nama", 
			"tabuser.email AS tabuser_email", 
			"tabuser.password AS tabuser_password", 
			"tabuser.image AS tabuser_image", 
			"tabuser.user_role_id AS tabuser_user_role_id", 
			"tabuser.school_id AS tabuser_school_id");
		if($value){
			$db->where($rec_id, urldecode($value)); //select record based on field name
		}
		else{
			$db->where("tabguru.id", $rec_id);; //select record based on primary key
		}
		$db->join("tabuser", "tabguru.school_id = tabuser.school_id", "INNER");  
		$record = $db->getOne($tablename, $fields );
		if($record){
			$page_title = $this->view->page_title = "View  Tabguru";
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
		return $this->render_view("tabguru/view.php", $record);
	}
	/**
     * Insert new record to the database table
	 * @param $formdata array() from $_POST
     * @return BaseView
     */
	function apiadd($formdata = null) {
		$response = array();
	
		if ($formdata) {
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$request = $this->request;
	
			// Fillable fields
			$fields = $this->fields = array("nama", "school_id");
			$postdata = $this->format_request_data($formdata);
		   
			// Ambil school_id dari session pengguna yang sedang login
			$postdata['school_id'] = USER_SCHOOL_ID;
	
			$this->rules_array = array(
				'nama' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
			);
			$this->filter_vals = true; // Set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
	
			if ($this->validated()) {
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if ($rec_id) {
					$response['status'] = 'success';
					$response['message'] = "Record added successfully";
					$response['id'] = $rec_id;
				} else {
					$response['status'] = 'error';
					$response['message'] = "Failed to add record";
					$response['db_error'] = $db->getLastError();
				}
			} else {
				$response['status'] = 'error';
				$response['message'] = "No form data received";
				// $response['errors'] = "No form data received";
			}
		} else {
			$response['status'] = 'error';
			$response['message'] = "No form data received";
		}
	
		echo json_encode($response);
		exit;
	}
	/**
     * Update table record with formdata
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function apiedit($rec_id = null, $formdata = null){
		$response = array();
	
		if(!$rec_id){
			$response['status'] = 'error';
			$response['message'] = "No record ID provided";
			echo json_encode($response);
			exit;
		}
	
		$request = $this->request;
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		$fields = $this->fields = array("id","nama");
	
		if($formdata){
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'nama' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
			);
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("tabguru.id", $rec_id);
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount();
				if($bool && $numRows){
					$response['status'] = 'success';
					$response['message'] = "Record updated successfully";
				}
				else{
					$response['status'] = 'error';
					if($db->getLastError()){
						$response['message'] = "Database error: " . $db->getLastError();
					}
					elseif(!$numRows){
						$response['message'] = "No record updated";
					}
				}
			}
			else {
				$response['status'] = 'error';
				$response['message'] = "Validation failed: Please check your input and try again";
			}
		}
		else {
			$db->where("tabguru.id", $rec_id);
			$data = $db->getOne($tablename, $fields);
			if(!$data){
				$response['status'] = 'error';
				$response['message'] = "Record not found";
			}
			else {
				$response['status'] = 'success';
				$response['data'] = $data;
			}
		}
	
		echo json_encode($response);
		exit;
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
		$fields = $this->fields = array("id","nama");
		$page_error = null;
		if($formdata){
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'nama' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("tabguru.id", $rec_id);;
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
	function apidelete($rec_id = null){
		$response = array();
	
		if(!$rec_id){
			$response['status'] = 'error';
			$response['message'] = "No record ID provided";
			echo json_encode($response);
			exit;
		}
	
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$this->rec_id = $rec_id;
	
		// Form multiple delete, split record id separated by comma into array
		$arr_rec_id = array_map('trim', explode(",", $rec_id));
		$db->where("tabguru.id", $arr_rec_id, "in");
		
		$bool = $db->delete($tablename);
		
		if($bool){
			$response['status'] = 'success';
			$response['message'] = "Record(s) deleted successfully";
			$response['deleted_count'] = $db->count;  // Jumlah record yang berhasil dihapus
		}
		elseif($db->getLastError()){
			$response['status'] = 'error';
			$response['message'] = "Database error: " . $db->getLastError();
		}
		else {
			$response['status'] = 'error';
			$response['message'] = "Failed to delete record(s)";
		}
	
		echo json_encode($response);
		exit;
	}
}
