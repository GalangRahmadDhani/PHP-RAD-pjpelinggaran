<?php
/**
 * Page Access Control
 * @category  RBAC Helper
 */
defined('ROOT') or exit('No direct script access allowed');
class ACL
{
	
	/**
	 * Array of user roles and page access 
	 * Use "*" to grant all access right to particular user role
	 * @var array
	 */
	public static $role_pages = array();

	/**
	 * Current user role name
	 * @var string
	 */
	public static $user_role = null;

	/**
	 * pages to exclude from access validation check
	 * @var array
	 */
	public static $exclude_page_check = array("", "index", "home", "account", "info", "masterdetail");

	/**
	 * get the list of pages user role has access to
	 */
	public function __construct()
	{	
		if(!empty(USER_ROLE)){
			$db = new PDODb(DB_TYPE, DB_HOST , DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT, DB_CHARSET);
			$db->where("role_id", USER_ROLE);
			//concat PageName And PageAction as page path
			$fields = array("CONCAT(page_name, '/', action_name) AS page");
			
			$roles_permission = $db->get("role_permissions", null, $fields);
			self:: $role_pages = array_column($roles_permission, "page"); //list all pages for the user role
			
			//get user role name
			self::$user_role = $db->where("role_id", USER_ROLE)->getValue("roles", "role_name");
		}
	}

	/**
	 * Check page path against user role permissions
	 * if user has access return AUTHORIZED
	 * if user has NO access return FORBIDDEN
	 * if user has NO role return NOROLE_PERMISSION
	 * @return String
	 */
	public static function GetPageAccess($path)
	{
		$path = strtolower(trim($path, '/'));
		$arr_path = explode("/", $path);
		$page = strtolower($arr_path[0]);
		//If User is accessing exclude access check page
		if (in_array($page, self :: $exclude_page_check)) {
			return AUTHORIZED;
		}
		if (!empty(USER_ROLE)) {
			$action = (!empty($arr_path[1]) ? $arr_path[1] : "list");
			if ($action == "index") {
				$action = "list";
			}
			$path = "$page/$action";
			if(in_array($path, self :: $role_pages)){
				return AUTHORIZED;
			}
			return FORBIDDEN;
		} else {
			return NOROLE; //User Does Not Have Any Role.
		}
	}

	/**
	 * Check if user role has access to a page
	 * @return Bool
	 */
	public static function is_allowed($path)
	{
		return (self::GetPageAccess($path) == AUTHORIZED);
	}

}
