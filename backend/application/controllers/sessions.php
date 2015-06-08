<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/Custom_REST_Controller.php';

class Sessions extends Custom_REST_Controller {

	function index_get() {
		$this->session_start_from_headers();
		$this->json_output($this->get_session_data());
	}
	
	function index_post() {
		$this->load->database();

		if (empty($this->post('username')) || empty($this->post('password'))) {
			$this->json_output(array('message' => 'Faltan parámetros para realizar la autenticación'), 400);
			return;
		}

		$user = $this->get_user_from_db_with_raw_password($this->post('username'), $this->post('password'));

		if ($user === null) {
			$this->json_output([
				array(
					'type' => 'danger',
					'text' => 'Usuario/contraseña invalidos'
				)
			], 403);
			return;
		}

		session_start();
		$_SESSION[SESSION_USER_ID] = $user['id'];
		$session_data = $this->get_session_data();

		$sql = "UPDATE user SET last_login_datetime = NOW() WHERE id = ?";

		$this->db->query($sql, array($user['id']));

		$this->json_output($session_data, 201);
	}

	function index_delete() {
		$this->session_start_from_headers();

		session_unset();
		session_destroy();
		$this->output->set_status_header(204);
	}

}
