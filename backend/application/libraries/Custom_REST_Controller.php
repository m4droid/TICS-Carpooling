<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

define("SESSION_USER_ID", "session_user_id");

abstract class Custom_REST_Controller extends REST_Controller {
	
	public function __construct($config = 'rest') {
		parent::__construct($config);

		$this->output->set_header('Content-Type: application/json; charset=utf-8');
		$this->output->set_header('Access-Control-Allow-Origin: *');
	}

	function session_start_from_headers() {
		$request_headers = apache_request_headers();
		if (isset($request_headers['Authorization'])) {
			$match = null;
			preg_match("/Token token=([a-z0-9]{26})/i", $request_headers['Authorization'], $match);
			if ($match !== null && count($match) == 2) {
				session_id($match[1]);
			}
		}
		session_start();
	}

	function get_session_data() {
		if (empty(session_id())) return array();
		return array(
			"id" => session_id(),
			"user_id" => (isset($_SESSION) && isset($_SESSION[SESSION_USER_ID])) ? $_SESSION[SESSION_USER_ID] : null
		);
	}

	function json_output($data, $http_code = 200) {
		$this->output->set_status_header($http_code);
		$this->output->set_output(json_encode($data));
	}

	function index_options() {
		$this->output->set_header("Access-Control-Allow-Headers: Authorization, X-Requested-With, accept, content-type, If-Modified-Since");
		$this->output->set_header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
		$this->output->set_status_header(200);
	}

	function is_user_in_db($id) {
		$query = $this->db->query("SELECT id FROM user WHERE id = ?", array($id));
		return $query->num_rows() > 0;
	}

	function get_user_from_db($id) {
		if (empty($id)) return null;
		$query = $this->db->query("SELECT * FROM user WHERE id = ?", array($id));
		if ($query->num_rows() == 0) return null;
		$user = $query->row_array();
		unset($user['password']);
		return $user;
	}

	function is_user_banned($user_id) {
		if (empty($user_id)) return false;
		$query = $this->db->query("SELECT user_id FROM banned_user WHERE user_id = ?", array($user_id));
		return $query->num_rows() > 0;
	}

	function is_email_in_db($email) {
		$query = $this->db->query("SELECT email FROM user WHERE email = ?", array($email));
		return $query->num_rows() > 0;
	}

	function set_user_password($user_id, $password) {		
		$sql = "UPDATE user SET password = ? WHERE id = ?";
		$this->db->query($sql, array($this->get_salted_password($password), $user_id));
	}

	function get_user_from_db_with_raw_password($id, $password) {
		$query = $this->db->query("SELECT * FROM user WHERE id = ? AND password = ?", array($id, $this->get_salted_password($password)));
		if ($query->num_rows() == 0) return null;
		$user = $query->row_array();
		unset($user['password']);
		return $user;
	}

	private function get_salted_password($password) {
		$salt = $this->config->item('salt');
		return sha1($salt . $password . $salt);
	}

}
