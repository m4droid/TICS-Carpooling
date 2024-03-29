<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/Custom_REST_Controller.php';

class Journeys_Likes extends Custom_REST_Controller {
	
	function index_post() {
		$this->load->database();

		$this->session_start_from_headers();
		$session_data = $this->get_session_data();

		if (empty($session_data) || empty($session_data['user_id'])) {
			$this->json_output([array('type' => 'danger', 'text' => 'Faltan credenciales de acceso.')], 401);
			return;
		}

		$user = $this->get_user_from_db($session_data['user_id']);
		if ($user === null) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario no está en el sistema')], 400);
			return;
		} else if ($user['type'] == 'admin') {
			$this->json_output([array('type' => 'danger', 'text' => 'Los administradores no pueden dar likes a los recorridos')], 403);
			return;
		}

		if ($this->is_user_banned($user['id'])) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario fue baneado del sistema')], 400);
			return;
		}

		$journey_id = $this->post('journey_id');

		if ($journey_id === null) {
			$this->json_output([array('type' => 'danger', 'text' => 'Falta ID del recorrido')], 400);
			return;
		}

		$query = $this->db->query("SELECT id FROM journey WHERE id = ?", array($journey_id));
		if ($query->num_rows() == 0) {
			$this->json_output([array('type' => 'danger', 'text' => 'El recorrido no existe')], 400);
			return;	
		}

		$sql = "INSERT IGNORE INTO journey_like (journey_id, user_id, datetime) VALUES (?, ?, NOW())";
		$this->db->trans_start();
		$this->db->query($sql, array($journey_id, $user['id']));
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->json_output([array('type' => 'danger', 'text' => 'No se pudo generar el like')], 500);
		}

		$this->json_output(array(), 201);
	}

	function index_delete() {
		$this->load->database();

		$this->session_start_from_headers();
		$session_data = $this->get_session_data();

		$journey_id = $this->input->get('journey_id');

		if (empty($session_data) || empty($session_data['user_id'])) {
			$this->json_output([array('type' => 'danger', 'text' => 'Faltan credenciales de acceso')], 401);
			return;
		}

		$user = $this->get_user_from_db($session_data['user_id']);
		if ($user === null) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario no está en el sistema')], 400);
			return;
		} else if ($user['type'] == 'admin') {
			$this->json_output([array('type' => 'danger', 'text' => 'Los administradores no pueden borrar los likes de los recorridos')], 403);
			return;
		}

		if ($this->is_user_banned($user['id'])) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario fue baneado del sistema')], 400);
			return;
		}

		if ($journey_id === null) {
			$this->json_output([array('type' => 'danger', 'text' => 'Falta ID del recorrido.')], 400);
			return;
		}

		$query = $this->db->query("SELECT id FROM journey WHERE id = ?", array($journey_id));
		if ($query->num_rows() == 0) {
			$this->json_output([array('type' => 'danger', 'text' => 'El recorrido no existe')], 400);
			return;	
		}

		$sql = "DELETE IGNORE FROM journey_like WHERE journey_id = ? AND user_id = ?";
		$this->db->trans_start();
		$this->db->query($sql, array($journey_id, $user['id']));
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->json_output([array('type' => 'danger', 'text' => 'No se pudo eliminar el like')], 500);
		}

		$this->json_output(array(), 204);
	}

}
