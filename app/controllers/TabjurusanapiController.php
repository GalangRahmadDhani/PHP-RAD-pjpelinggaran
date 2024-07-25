<?php 
/**
 * Tabjurusan Page Controller
 * @category  Controller
 */
class TabjurusanapiController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "tabjurusan";
	}
	/**
     * List page records
     * @param $fieldname (filter record by a field) 
     * @param $fieldvalue (filter field value)
     * @return BaseView
     */
	function indexapi($fieldname = null , $fieldvalue = null){
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array("id", "nama");
	
		if(!empty($request->search)){
			$text = trim($request->search); 
			$search_condition = "(
				tabjurusan.id LIKE ? OR 
				tabjurusan.nama LIKE ?
			)";
			$search_params = array(
				"%$text%","%$text%"
			);
			$db->where($search_condition, $search_params);
		}
	
		if(!empty($request->orderby)){
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		}
		else{
			$db->orderBy("tabjurusan.id", ORDER_TYPE);
		}
	
		if($fieldname){
			$db->where($fieldname , $fieldvalue);
		}
	
		$db->where("tabjurusan.school_id", USER_SCHOOL_ID);
		$records = $db->get($tablename, null, $fields);
	
		if($db->getLastError()){
			return render_json([
				"status" => "error",
				"message" => $db->getLastError()
			]);
		}
	
		return render_json([
			"status" => "success",
			"data" => $records
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
			"nama", 
			"posted_by", 
			"school_id");
		if($value){
			$db->where($rec_id, urldecode($value)); //select record based on field name
		}
		else{
			$db->where("tabjurusan.id", $rec_id);; //select record based on primary key
		}
		$record = $db->getOne($tablename, $fields );
		if($record){
			$page_title = $this->view->page_title = "View  Tabjurusan";
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
		return $this->render_view("tabjurusan/view.php", $record);
	}
	/**
     * Insert new record to the database table
	 * @param $formdata array() from $_POST
     * @return BaseView
     */
	function addapi($formdata = null){
		if($formdata){
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$request = $this->request;
			//fillable fields
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
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if($rec_id){
					return render_json([
						"status" => "success",
						"message" => "Record added successfully",
						"id" => $rec_id
					]);
				}
				else{
					return render_json([
						"status" => "error",
						"message" => "Failed to add record"
					]);
				}
			}
			else {
				return render_json([
					"status" => "error",
					"message" => "Validation failed",
					"errors" => $this->view->page_error
				]);
			}
		}
		return render_json([
			"status" => "error",
			"message" => "No form data provided"
		]);
	}
	/**
     * Update table record with formdata
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function editapi($rec_id = null, $formdata = null){
		$request = $this->request;
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		//editable fields
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
				$db->where("tabjurusan.id", $rec_id);
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount(); //number of affected rows. 0 = no record field updated
				if($bool && $numRows){
					return render_json([
						"status" => "success",
						"message" => "Record updated successfully"
					]);
				}
				else{
					if($db->getLastError()){
						return render_json([
							"status" => "error",
							"message" => $db->getLastError()
						]);
					}
					elseif(!$numRows){
						return render_json([
							"status" => "warning",
							"message" => "No record updated"
						]);
					}
				}
			}
			else {
				return render_json([
					"status" => "error",
					"message" => "Validation failed",
					"errors" => $this->view->page_error
				]);
			}
		}
		$db->where("tabjurusan.id", $rec_id);
		$data = $db->getOne($tablename, $fields);
		if(!$data){
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
				$db->where("tabjurusan.id", $rec_id);;
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
	function deleteapi($rec_id = null){
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$this->rec_id = $rec_id;
	
		// Form multiple delete, split record id separated by comma into array
		$arr_rec_id = array_map('trim', explode(",", $rec_id));
		$db->where("tabjurusan.id", $arr_rec_id, "in");
		$bool = $db->delete($tablename);
	
		if($bool){
			return render_json([
				"status" => "success",
				"message" => "Record deleted successfully"
			]);
		}
		elseif($db->getLastError()){
			$error_message = $db->getLastError();
			return render_json([
				"status" => "error",
				"message" => $error_message
			]);
		}
		else {
			return render_json([
				"status" => "error",
				"message" => "Failed to delete record"
			]);
		}
	}
}
