<?php
/**
 * Index Page Controller
 * @category Controller
 */
class IndexapiController extends BaseController {
    private $noboxToken = "";
    private $noboxAccount = "";

    function __construct() {
        parent::__construct();
        $this->tablename = "tabuser";

        // Retrieve Nobox token from session if it exists
        if (isset($_SESSION['noboxToken'])) {
            $this->noboxToken = $_SESSION['noboxToken'];
        }
    }

    function index() {
        if (user_login_status() == true) {
            $this->redirect(HOME_PAGE);
        } else {
            $this->render_view("index/index.php");
        }
    }

    private function login_user($username, $password_text, $rememberme = false) {
        $db = $this->GetModel();
        $username = filter_var($username, FILTER_SANITIZE_STRING);
        $db->where("nama", $username)->orWhere("email", $username);
        $tablename = $this->tablename;
        $user = $db->getOne($tablename);

        if (!empty($user)) {
            $password_hash = $user['password'];
            $this->modeldata['password'] = $password_hash;

            if (password_verify($password_text, $password_hash)) {
                unset($user['password']); // Remove user password. No need to store it in the session
                set_session("user_data", $user); // Set active user data in a session

                if ($rememberme) {
                    $sessionkey = time() . random_str(20); // Generate a session key for the user
                    $db->where("id", $user['id']);
                    $res = $db->update($tablename, array("login_session_key" => hash_value($sessionkey)));

                    if (!empty($res)) {
                        set_cookie("login_session_key", $sessionkey); // Save user login_session_key in a cookie
                    }
                } else {
                    clear_cookie("login_session_key"); // Clear any previously set cookie
                }

                $redirect_url = get_session("login_redirect_url"); // Redirect to user active page
                if (!empty($redirect_url)) {
                    clear_session("login_redirect_url");
                    return $this->redirect($redirect_url);
                } else {
                    return $this->redirect(HOME_PAGE);
                }
            } else {
                return $this->login_fail("Username or password not correct");
            }
        } else {
            return $this->login_fail("Username or password not correct");
        }
    }

    private function login_fail($page_error = null) {
        $this->set_page_error($page_error);
        $this->render_view("index/login.php");
    }

    function loginapi($formdata = null) {
        header('Content-Type: application/json'); // Set the content type to JSON

        if ($formdata) {
            $username = trim($formdata['username']);
            $password = $formdata['password'];
            $rememberme = !empty($formdata['rememberme']) ? $formdata['rememberme'] : false;

            $db = $this->GetModel();
            $username = filter_var($username, FILTER_SANITIZE_STRING);
            $db->where("email", $username);
            $tablename = $this->tablename;
            $user = $db->getOne($tablename);

            if (!empty($user)) {
                $password_hash = $user['password'];
                if (password_verify($password, $password_hash)) {
                    unset($user['password']); // Remove user password. No need to store it in the session
                    set_session("user_data", $user); // Set active user data in a session

                    if ($rememberme) {
                        $sessionkey = time() . random_str(20); // Generate a session key for the user
                        $db->where("id", $user['id']);
                        $res = $db->update($tablename, array("login_session_key" => hash_value($sessionkey)));
                        if (!empty($res)) {
                            set_cookie("login_session_key", $sessionkey); // Save user login_session_key in a cookie
                        }
                    } else {
                        clear_cookie("login_session_key"); // Clear any previously set cookie
                    }

                    // Generate Nobox token
                    $noboxController = new NoboxController();
                    $this->noboxToken = $noboxController->generateTokenOnLogin();
                    
                    // Store Nobox token in session
                    $_SESSION['noboxToken'] = $this->noboxToken;

                    // Fetch Nobox account details
                    $this->getAccount($this->noboxToken);

                    // If login is successful, return JSON
                    return render_json([
                        'status' => 'success',
                        'message' => 'Login successful',
                        'user' => $user,
                        'token' => $this->noboxToken // Include the Nobox token in the response
                    ]);
                }
            }

            // If login fails, return JSON
            return render_json([
                'status' => 'error',
                'message' => 'Invalid username or password'
            ]);
        } else {
            // If request is invalid, return JSON
            return render_json([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);
        }
    }

    function logoutapi($arg = null) {
        Csrf::cross_check();

        // Revoke Nobox token
        $noboxController = new NoboxController();
        $noboxController->revokeTokenOnLogout();

        session_destroy();
        clear_cookie("login_session_key");

        return render_json([
            "status" => "success",
            "message" => "Logged out successfully and Nobox token revoked"
        ]);
    }

    public function getAccount($token) {
        // Initialize Nobox with the token
        $nobox = new Nobox($token);

        // Get the account list
        $tokenResponse = $nobox->getAccountList();

        // Return the response as JSON
        $this->renderJson($tokenResponse);
    }

    private function renderJson($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    public function sendMessage() {
        // Retrieve the token from session
        $token = $this->noboxToken;

        // Check if token is available
        if (!$token) {
            return $this->renderJson(['status' => 'error', 'message' => 'Token not found'], 400);
        }

        // Retrieve other request parameters
        $extId = isset($_POST['nohp']) ? $_POST['nohp'] : '';
        $channelId = '1';
        $accountIds = '549325967183941';
        $bodyType = '1';
        $body = isset($_POST['message']) ? $_POST['message'] : '';
        $attachment = '';

        // Initialize Nobox with the token
        $nobox = new Nobox($token);

        // Send the message
        $tokenResponse = $nobox->sendInboxMessageExt($extId, $channelId, $accountIds, $bodyType, $body, $attachment);

        // Return the response as JSON
        return $this->renderJson($tokenResponse);
    }
}

