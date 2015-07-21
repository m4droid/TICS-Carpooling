<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/Custom_REST_Controller.php';

class Users_Banned extends Custom_REST_Controller {
	
	function index_post() {
		$this->load->database();

		$this->session_start_from_headers();
		$session_data = $this->get_session_data();

		if (empty($session_data) || empty($session_data['user_id'])) {
			$this->json_output([array('type' => 'danger', 'text' => 'Faltan credenciales de acceso.')], 401);
			return;
		}

		$current_user = $this->get_user_from_db($session_data['user_id']);
		if ($current_user === null) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario no está en el sistema')], 400);
			return;
		} else if ($current_user['type'] != 'admin') {
			$this->json_output([array('type' => 'danger', 'text' => 'Solo los administradores pueden banear usuarios')], 403);
			return;
		}

		$user_id = $this->post('user_id');

		if ($user_id === null) {
			$this->json_output([array('type' => 'danger', 'text' => 'Falta ID del usuario')], 400);
			return;
		}

		$query = $this->db->query("SELECT id FROM user WHERE id = ?", array($user_id));
		if ($query->num_rows() == 0) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario no existe')], 400);
			return;	
		}

		$sql = "INSERT IGNORE INTO banned_user (user_id, datetime) VALUES (?, NOW())";
		$this->db->trans_start();
		$this->db->query($sql, array($user_id));
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->json_output([array('type' => 'danger', 'text' => 'No se pudo banear al usuario')], 500);
		}

		$this->json_output(array(), 201);
	}

	function index_delete() {
		$this->load->database();

		$this->session_start_from_headers();
		$session_data = $this->get_session_data();

		$user_id = $this->input->get('user_id');

		if (empty($session_data) || empty($session_data['user_id'])) {
			$this->json_output([array('type' => 'danger', 'text' => 'Faltan credenciales de acceso')], 401);
			return;
		}

		$current_user = $this->get_user_from_db($session_data['user_id']);
		if ($current_user === null) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario no está en el sistema')], 400);
			return;
		} else if ($current_user['type'] != 'admin') {
			$this->json_output([array('type' => 'danger', 'text' => 'Solo los administradores pueden desbanear usuarios')], 403);
			return;
		}

		if ($user_id === null) {
			$this->json_output([array('type' => 'danger', 'text' => 'Falta ID del usuario')], 400);
			return;
		}

		$query = $this->db->query("SELECT id FROM user WHERE id = ?", array($user_id));
		if ($query->num_rows() == 0) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario no existe')], 400);
			return;	
		}

		$sql = "DELETE IGNORE FROM banned_user WHERE user_id = ?";
		$this->db->trans_start();
		$this->db->query($sql, array($user_id));
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->json_output([array('type' => 'danger', 'text' => 'No se pudo desbanear al usuario')], 500);
		}

		$this->json_output(array(), 204);
	}

}
