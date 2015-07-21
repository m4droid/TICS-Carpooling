<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/Custom_REST_Controller.php';

class Journeys extends Custom_REST_Controller {

	private static $ALLOWED_MUSIC_STYLES = [
		'rock',
		'metal',
		'pop',
		'electronic',
		'hiphop',
		'funk'
	];

	function index_get() {
		$this->load->database();

		$journeys = [];
		$query = $this->db->query("SELECT id, user_id, route, TIME_FORMAT(time, '%H:%i') AS time, seats, creation_datetime
									FROM journey ORDER BY creation_datetime ASC");
		if ($query->num_rows() == 0) {
			$this->json_output($journeys);
			return;	
		}

		$journey_days_sql = 'SELECT day FROM journey_day WHERE journey_id = ?';
		$journey_music_styles_sql = 'SELECT music_style_id FROM journey_music_style WHERE journey_id = ?';
		$journey_passengers_sql = 'SELECT user_id FROM journey_passenger WHERE journey_id = ?';
		$journey_likes_sql = 'SELECT user_id FROM journey_like WHERE journey_id = ?';

		foreach ($query->result_array() as $row) {
			$row['days_of_week'] = [];
			$row['music_styles'] = [];
			$row['passengers'] = [];
			$row['likes'] = [];

			$query = $this->db->query($journey_days_sql, $row['id']);
			foreach ($query->result_array() as $day_row) {
				$row['days_of_week'][] = intval($day_row['day']);
			}

			$query = $this->db->query($journey_music_styles_sql, $row['id']);
			foreach ($query->result_array() as $music_style_row) {
				$row['music_styles'][] = $music_style_row['music_style_id'];
			}

			$query = $this->db->query($journey_passengers_sql, $row['id']);
			foreach ($query->result_array() as $passenger_row) {
				$row['passengers'][] = $passenger_row['user_id'];
			}

			$query = $this->db->query($journey_likes_sql, $row['id']);
			foreach ($query->result_array() as $like_row) {
				$row['likes'][] = $like_row['user_id'];
			}

			$journeys[] = $row;
		}
		
		$this->json_output($journeys);
	}
	
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
			$this->json_output([array('type' => 'danger', 'text' => 'Los administradores no pueden insertar nuevos recorridos')], 403);
			return;
		}

		if ($this->is_user_banned($user['id'])) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario fue baneado del sistema')], 400);
			return;
		}

		if ($this->post('journey') === null) {
			return_missing_data_request();
			return;
		}

		$required_keys = [
			'route',
			'time',
			'seats',
			'days_of_week',
			'music_styles'
		];

		$valid = true;

		foreach ($required_keys as $key) {
			if ( ! isset($this->post('journey')[$key])) {
				$valid = false;
				break;
			}
		}

		$sanitized_journey = array(
			'user_id' => $session_data['user_id'],
			'route' => $this->post('journey')['route'],
			'time' => $this->post('journey')['time'],
			'seats' => $this->post('journey')['seats'],
			'days_of_week' => [],
			'music_styles' => []
		);

		foreach ($this->post('journey')['days_of_week'] as $day => $checked) {
			if ( ! (1 <= $day && $day <= 7)) {
				$this->json_output([array('type' => 'danger', 'text' => 'Día fuera de rango')], 400);
				return;
			}

			if ($checked) $sanitized_journey['days_of_week'][] = $day;
		}

		foreach ($this->post('journey')['music_styles'] as $style => $checked) {
			if ( ! in_array($style, $this::$ALLOWED_MUSIC_STYLES)) {
				$this->json_output([array('type' => 'danger', 'text' => 'Estilo musical inválido')], 400);
				return;
			}

			if ($checked) $sanitized_journey['music_styles'][] = $style;
		}

		if (count($sanitized_journey['days_of_week']) == 0) $valid = false;
		if (count($sanitized_journey['music_styles']) == 0) $valid = false;

		if ( ! $valid) {
			return_missing_data_request();
			return;
		}

		$this->db->trans_start();

		$sql = "INSERT INTO journey (user_id, route, time, seats, creation_datetime) VALUES (?, ?, ?, ?, NOW())";
		$this->db->query(
			$sql,
			array(
				$sanitized_journey['user_id'],
				$sanitized_journey['route'],
				$sanitized_journey['time'],
				$sanitized_journey['seats']
			)
		);

		$journey_id = $this->db->insert_id();

		$sql = "INSERT INTO journey_day (journey_id, day) VALUES (?, ?)";
		foreach ($sanitized_journey['days_of_week'] as $day) {
			$this->db->query($sql, array($journey_id, $day));
		}

		$sql = "INSERT INTO journey_music_style (journey_id, music_style_id) VALUES (?, ?)";
		foreach ($sanitized_journey['music_styles'] as $style) {
			$this->db->query($sql, array($journey_id, $style));
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->json_output([array('type' => 'danger', 'text' => 'Error al insertar nuevo recorrido.')], 500);
			return;
		}

		$this->json_output($sanitized_journey, 201);
	}

	private function return_missing_data_request() {
		$this->json_output([
			array(
				'type' => 'danger',
				'text' => 'Falta información requerida.'
			)
		], 400);
	}

	function index_delete() {
		$this->load->database();

		$this->session_start_from_headers();
		$session_data = $this->get_session_data();

		if (empty($session_data) || empty($session_data['user_id'])) {
			$this->json_output([array('type' => 'danger', 'text' => 'Faltan credenciales de acceso')], 401);
			return;
		}

		$user = $this->get_user_from_db($session_data['user_id']);
		if ($user === null) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario no está en el sistema')], 400);
			return;
		} else if ($user['type'] != 'admin') {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario no tiene permisos de administrador')], 403);
			return;
		}

		if ($this->is_user_banned($user['id'])) {
			$this->json_output([array('type' => 'danger', 'text' => 'El usuario fue baneado del sistema')], 400);
			return;
		}

		$journey_id = $this->input->get('id');

		$query = $this->db->query("SELECT id FROM journey WHERE id = ?", array($journey_id));
		if ($query->num_rows() == 0) {
			$this->json_output([array('type' => 'danger', 'text' => 'El recorrido no existe')], 400);
			return;
		}

		$sql = "DELETE IGNORE FROM journey WHERE id = ?";
		$this->db->trans_start();
		$this->db->query($sql, array($journey_id));
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->json_output([array('type' => 'danger', 'text' => 'No se pudo eliminar el recorrido')], 500);
		}

		$this->output->set_status_header(204);
	}

}
