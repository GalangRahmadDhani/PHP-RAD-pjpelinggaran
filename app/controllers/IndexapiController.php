<?php 
/**
 * Index Page Controller
 * @category  Controller
 */
class IndexapiController extends BaseController{
	function __construct(){
		parent::__construct(); 
		$this->tablename = "tabuser";
	}
	/**
     * Index Action 
     * @return null
     */
	function index(){
		if(user_login_status() == true){
			$this->redirect(HOME_PAGE);
		}
		else{
			$this->render_view("index/index.php");
		}
	}
	private function login_user($username , $password_text, $rememberme = false){
		$db = $this->GetModel();
		$username = filter_var($username, FILTER_SANITIZE_STRING);
		$db->where("nama", $username)->orWhere("email", $username);
		$tablename = $this->tablename;
		$user = $db->getOne($tablename);
		if(!empty($user)){
			//Verify User Password Text With DB Password Hash Value.
			//Uses PHP password_verify() function with default options
			$password_hash = $user['password'];
			$this->modeldata['password'] = $password_hash; //update the modeldata with the password hash
			if(password_verify($password_text,$password_hash)){
        		unset($user['password']); //Remove user password. No need to store it in the session
				set_session("user_data", $user); // Set active user data in a sessions
				//if Remeber Me, Set Cookie
				if($rememberme == true){
					$sessionkey = time().random_str(20); // Generate a session key for the user
					//Update user session info in database with the session key
					$db->where("id", $user['id']);
					$res = $db->update($tablename, array("login_session_key" => hash_value($sessionkey)));
					if(!empty($res)){
						set_cookie("login_session_key", $sessionkey); // save user login_session_key in a Cookie
					}
				}
				else{
					clear_cookie("login_session_key");// Clear any previous set cookie
				}
				$redirect_url = get_session("login_redirect_url");// Redirect to user active page
				if(!empty($redirect_url)){
					clear_session("login_redirect_url");
					return $this->redirect($redirect_url);
				}
				else{
					return $this->redirect(HOME_PAGE);
				}
			}
			else{
				//password is not correct
				return $this->login_fail("Username or password not correct");
			}
		}
		else{
			//user is not registered
			return $this->login_fail("Username or password not correct");
		}
	}
	/**
     * Display login page with custom message when login fails
     * @return BaseView
     */
	private function login_fail($page_error = null){
		$this->set_page_error($page_error);
		$this->render_view("index/login.php");
	}
	/**
     * Login Action
     * If Not $_POST Request, Display Login Form View
     * @return View
     */
	function loginapi($formdata = null){
		header('Content-Type: application/json'); // Set the content type to JSON
	
		if ($formdata) {
			$username = trim($formdata['username']);
			$password = $formdata['password'];
			$rememberme = !empty($formdata['rememberme']) ? $formdata['rememberme'] : false;
	
			// Logika login menggunakan login_user
			$db = $this->GetModel();
			$username = filter_var($username, FILTER_SANITIZE_STRING);
			$db->where("email", $username);
			$tablename = $this->tablename;
			$user = $db->getOne($tablename);
	
			if (!empty($user)) {
				$password_hash = $user['password'];
				if (password_verify($password, $password_hash)) {
					unset($user['password']); // Remove user password. No need to store it in the session
					set_session("user_data", $user); // Set active user data in a sessions
	
					// Handle "Remember Me" functionality
					if ($rememberme) {
						$sessionkey = time().random_str(20); // Generate a session key for the user
						$db->where("id", $user['id']);
						$res = $db->update($tablename, array("login_session_key" => hash_value($sessionkey)));
						if (!empty($res)) {
							set_cookie("login_session_key", $sessionkey); // save user login_session_key in a Cookie
						}
					} else {
						clear_cookie("login_session_key"); // Clear any previous set cookie
					}
	
					// Jika login berhasil, return JSON
					return render_json([
						'status' => 'success',
						'message' => 'Login successful',
						'user' => $user
					]);
				}
			}
	
			// Jika login gagal, return JSON
			return render_json([
				'status' => 'error',
				'message' => 'Invalid username or password'
			]);
		} else {
			// Jika request tidak valid, return JSON
			return render_json([
				'status' => 'error',
				'message' => 'Invalid request'
			]);
		}
	}
	
	/**
     * Logout Action
     * Destroy All Sessions And Cookies
     * @return View
     */
	function logoutapi($arg = null) {
		Csrf::cross_check();
		session_destroy();
		clear_cookie("login_session_key");
	
		return render_json([
			"status" => "success",
			"message" => "Logged out successfully"
		]);
	}
	
}
