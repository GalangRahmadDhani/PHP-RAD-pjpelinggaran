<?php 
/**
 * Account Page Controller
 * @category  Controller
 */
class AccountController extends SecureController{
	function __construct(){
		parent::__construct(); 
		$this->tablename = "tabuser";
	}
	/**
		* Index Action
		* @return null
		*/
	function index(){
		$db = $this->GetModel();
		$rec_id = $this->rec_id = USER_ID; //get current user id from session
		$db->where ("id", $rec_id);
		$tablename = $this->tablename;
		$fields = array("tabuser.id", 
			"tabuser.nama", 
			"tabuser.email", 
			"tabuser.user_role_id", 
			"tabuser.school_id", 
			"tabsekolah.nama AS tabsekolah_nama");
		$db->join("tabsekolah", "tabuser.school_id = tabsekolah.id", "INNER");
		$user = $db->getOne($tablename , $fields);
		if(!empty($user)){
			$page_title = $this->view->page_title = "My Account";
			$this->render_view("account/view.php", $user);
		}
		else{
			$this->set_page_error();
			$this->render_view("account/view.php");
		}
	}
	/**
     * Update user account record with formdata
	 * @param $formdata array() from $_POST
     * @return array
     */
	function edit($formdata = null){
		$request = $this->request;
		$db = $this->GetModel();
		$rec_id = $this->rec_id = USER_ID;
		$tablename = $this->tablename;
		 //editable fields
		$fields = $this->fields = array("id","nama","image","user_role_id","school_id");
		if($formdata){
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'nama' => 'required',
				'image' => 'required',
				'user_role_id' => 'required',
				'school_id' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'image' => 'sanitize_string',
				'user_role_id' => 'sanitize_string',
				'school_id' => 'sanitize_string',
			);
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			//Check if Duplicate Record Already Exit In The Database
			if(isset($modeldata['nama'])){
				$db->where("nama", $modeldata['nama'])->where("id", $rec_id, "!=");
				if($db->has($tablename)){
					$this->view->page_error[] = $modeldata['nama']." Already exist!";
				}
			} 
			if($this->validated()){
				$db->where("tabuser.id", $rec_id);;
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount(); //number of affected rows. 0 = no record field updated
				if($bool && $numRows){
					$this->set_flash_msg("Record updated successfully", "success");
					$db->where ("id", $rec_id);
					$user = $db->getOne($tablename , "*");
					set_session("user_data", $user);// update session with new user data
					return $this->redirect("account");
				}
				else{
					if($db->getLastError()){
						$this->set_page_error();
					}
					elseif(!$numRows){
						//not an error, but no record was updated
						$this->set_flash_msg("No record updated", "warning");
						return	$this->redirect("account");
					}
				}
			}
		}
		$db->where("tabuser.id", $rec_id);;
		$data = $db->getOne($tablename, $fields);
		$page_title = $this->view->page_title = "My Account";
		if(!$data){
			$this->set_page_error();
		}
		return $this->render_view("account/edit.php", $data);
	}
	/**
     * Change account email
     * @return BaseView
     */
	function change_email($formdata = null){
		if($formdata){
			$email = trim($formdata['email']);
			$db = $this->GetModel();
			$rec_id = $this->rec_id = USER_ID; //get current user id from session
			$tablename = $this->tablename;
			$db->where ("id", $rec_id);
			$result = $db->update($tablename, array('email' => $email ));
			if($result){
			}
			else{
				$this->set_page_error("Email not changed");
			}
		}
		return $this->render_view("account/change_email.php");
	}

	function getaccount(){
		$db = $this->GetModel();
		$rec_id = $this->rec_id = USER_ID; //get current user id from session
		$db->where ("id", $rec_id);
		$tablename = $this->tablename;
		$fields = array("id", 
			"nama", 
			"email");
		$user = $db->getOne($tablename , $fields);
		if(!empty($user)){
			$page_title = $this->view->page_title = "My Account";
			return render_json($user);
		}
		else{
			$this->set_page_error();
			$this->render_view("account/view.php");
		}
	}
}
