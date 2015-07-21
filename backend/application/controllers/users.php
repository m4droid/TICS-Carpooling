<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/Custom_REST_Controller.php';

class Users extends Custom_REST_Controller {

	function index_get() {
		$this->load->database();

		$this->session_start_from_headers();
		$session_data = $this->get_session_data();
		$current_user = $this->get_user_from_db($session_data['user_id']);

		$user_id = $this->get('id');

		if (empty($user_id)) {
			$users = [];
			$banned_users = [];

			if ( ! empty($current_user) && $current_user['type'] == 'admin') {
				$query = $this->db->query("SELECT * FROM user ORDER BY id");
				foreach ($query->result_array() as $row) {
					unset($row['password']);
					$users[] = $row;
				}

				$query = $this->db->query("SELECT user_id as id, datetime FROM banned_user ORDER BY user_id");
				foreach ($query->result_array() as $row) {
					$banned_users[] = $row;
				}
			}

			$this->json_output(array('users' => $users, 'banned_users' => $banned_users));
			return;
		}

		$user = $this->get_user_from_db($user_id);
		if ($user === null) {
			$this->json_output(array(), 404);
			return;
		}
		$this->json_output($user);
	}
	
	function index_post() {
		$this->load->database();
		$this->load->helper('email');

		$raw_user_data = $this->post();

		$validations = array(
			'id' => array(
				'empty_text' => 'Debes ingresar un nombre de usuario.',
				'error_text' => 'Nombre de usuario inválido',
				'regex' => '/[a-z]{3,32}/i',
			),
			'email' => array(
				'empty_text' => 'Debes ingresar una dirección de email',
				'error_text' => 'Email inválido',
				'check_func' => function ($value) {
					return valid_email($value);
				}
			),
			'password' => array(
				'empty_text' => 'Debes ingresar una password.',
				'error_text' => 'Password inválida',
				'regex' => '/[\w]{8,16}/i',
			),
			'phone_number' => array(
				'empty_text' => 'Debes ingresar un número telefónico.',
				'error_text' => 'Número de teléfono inválido',
				'regex' => '/\+[0-9]{11,}/i'
			),
			'first_name' => array(
				'empty_text' => 'Debes ingresar tu nombre.',
				'error_text' => 'Nombre inválido',
				'regex' => '/[a-z]{3,32}/i'
			),
			'last_name' => array(
				'empty_text' => 'Debes ingresar tu apellido.',
				'error_text' => 'Apellido inválido',
				'regex' => '/[a-z]{3,32}/i'
			),
			'sex' => array(
				'empty_text' => 'Debes indicar tu sexo.',
				'error_text' => 'Sexo inválido',
				'regex' => '/[mf]/i'
			),
			'dob' => array(
				'empty_text' => 'Debes ingresar tu fecha de nacimiento.',
				'error_text' => 'Fecha de nacimiento inválida',
				'regex' => '/[0-9]{4,}-[0-9]{2}\-[0-9]{2}/i',
				'check_func' => function ($value) {
					$date_parts = explode('-', $value);
					return checkdate($date_parts[1], $date_parts[2], $date_parts[0]);
				}
			),
			'avatar' => array(
				'empty_text' => 'Debes ingresar un avatar.',
				'error_text' => 'Avatar inválido',
				'regex' => '/^data:image\/(.*);base64\,(.*)$/',
			),
			'about' => array(
				'empty_text' => 'Debes ingresar un texto sobre ti',
				'error_text' => 'Texto acerca de ti inválido.',
				'regex' => '/[\w]+/',
			)
		);

		$raw_data_errors = $this->is_raw_data_valid($raw_user_data, $validations);
		if (count($raw_data_errors) > 0) {
			$this->json_output($raw_data_errors, 400);
			return;
		}

		if ($this->is_user_in_db($raw_user_data['id'])) {
			$this->json_output([
				array(
					'type' => 'danger',
					'text' => 'El nombre de usuario elegido ya existe en el sistema.'
				)
			], 400);
			return;
		}

		if ($this->is_email_in_db($raw_user_data['email'])) {
			$this->json_output([
				array(
					'type' => 'danger',
					'text' => 'El email ingresado ya existe en el sistema.'
				)
			], 400);
			return;
		}

		$avatar_filename = $this->get_avatar_filename($raw_user_data);
		if ($avatar_filename === null) {
			$this->json_output([
				array(
					'type' => 'danger',
					'text' => 'Avatar inválido'
				)
			], 400);
		}

		if (empty($raw_user_data['password'] || strlen($raw_user_data['password']) < 8)) {
			$this->json_output([
				array(
					'type' => 'danger',
					'text' => 'La contraseña debe tener al menos 8 caracteres.'
				)
			], 400);
			return;
		}

		$sql = "INSERT INTO user (id, email, password, phone_number, first_name, last_name, sex, dob, avatar, about, register_datetime)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

		$this->db->query(
			$sql,
			array(
				$raw_user_data['id'],
				$raw_user_data['email'],
				$raw_user_data['password'],
				$raw_user_data['phone_number'],
				$raw_user_data['first_name'],
				$raw_user_data['last_name'],
				$raw_user_data['sex'],
				$raw_user_data['dob'],
				$avatar_filename,
				$raw_user_data['about']
			)
		);

		$this->set_user_password($raw_user_data['id'], $raw_user_data['password']);

		$raw_user_data['avatar'] = $avatar_filename;
		unset($raw_user_data['password']);

		$this->json_output($raw_user_data, 201);
	}

	function index_put() {
		$this->load->database();
		$this->load->helper('email');

		$this->session_start_from_headers();
		$session_data = $this->get_session_data();

		$raw_user_data = $this->put();
		$user = $this->get_user_from_db($raw_user_data['id']);

		if ($session_data === null || $user === null || $session_data['user_id'] != $user['id']) {
			$this->json_output([
				array(
					'type' => 'danger',
					'text' => 'El perfil es editable sólo por el usuario.'
				)
			], 400);
			return;
		}

		$validations = array(
			'id' => array(
				'empty_text' => 'Debes ingresar un nombre de usuario.',
				'error_text' => 'Nombre de usuario inválido',
				'regex' => '/[a-z]{3,32}/i',
			),
			'email' => array(
				'empty_text' => 'Debes ingresar una dirección de email',
				'error_text' => 'Email inválido',
				'check_func' => function ($value) {
					return valid_email($value);
				}
			),
			'phone_number' => array(
				'empty_text' => 'Debes ingresar un número telefónico.',
				'error_text' => 'Número de teléfono inválido',
				'regex' => '/\+[0-9]{11,}/i'
			),
			'first_name' => array(
				'empty_text' => 'Debes ingresar tu nombre.',
				'error_text' => 'Nombre inválido',
				'regex' => '/[a-z]{3,32}/i'
			),
			'last_name' => array(
				'empty_text' => 'Debes ingresar tu apellido.',
				'error_text' => 'Apellido inválido',
				'regex' => '/[a-z]{3,32}/i'
			),
			'sex' => array(
				'empty_text' => 'Debes indicar tu sexo.',
				'error_text' => 'Sexo inválido',
				'regex' => '/[mf]/i'
			),
			'dob' => array(
				'empty_text' => 'Debes ingresar tu fecha de nacimiento.',
				'error_text' => 'Fecha de nacimiento inválida',
				'regex' => '/[0-9]{4,}-[0-9]{2}\-[0-9]{2}/i',
				'check_func' => function ($value) {
					$date_parts = explode('-', $value);
					return checkdate($date_parts[1], $date_parts[2], $date_parts[0]);
				}
			),
			'avatar' => array(
				'empty_text' => 'Debes ingresar un avatar.',
			),
			'about' => array(
				'empty_text' => 'Debes ingresar un texto sobre ti',
				'error_text' => 'Texto acerca de ti inválido.',
				'regex' => '/[\w]+/',
			)
		);

		$raw_data_errors = $this->is_raw_data_valid($raw_user_data, $validations);
		if (count($raw_data_errors) > 0) {
			$this->json_output($raw_data_errors, 400);
			return;
		}

		if ($user['email'] != $raw_user_data['email'] && $this->is_email_in_db($raw_user_data['email'])) {
			$this->json_output([
				array(
					'type' => 'danger',
					'text' => 'El email ingresado ya existe en el sistema.'
				)
			], 400);
			return;
		}

		$avatar_filename = $this->get_avatar_filename($raw_user_data);
		if ($avatar_filename === null) {
			$this->json_output([
				array(
					'type' => 'danger',
					'text' => 'Avatar inválido'
				)
			], 400);
			return;
		}

		if ( ! empty($raw_user_data['password'])) {
			if (strlen($raw_user_data['password']) < 8) {
				$this->json_output([
					array(
						'type' => 'danger',
						'text' => 'La contraseña debe tener al menos 8 caracteres.'
					)
				], 400);
			} else {
				$this->set_user_password($user['id'], $raw_user_data['password']);
			}
		}

		$sql = "UPDATE user SET email = ?, phone_number = ?, first_name = ?, last_name = ?, sex = ?, dob = ?, avatar = ?, about = ?
				WHERE id = ?";

		$this->db->query(
			$sql,
			array(
				$raw_user_data['email'],
				$raw_user_data['phone_number'],
				$raw_user_data['first_name'],
				$raw_user_data['last_name'],
				$raw_user_data['sex'],
				$raw_user_data['dob'],
				$avatar_filename,
				$raw_user_data['about'],
				$user['id']
			)
		);

		$raw_user_data['avatar'] = $avatar_filename;
		unset($raw_user_data['password']);

		$this->json_output($raw_user_data, 201);
	}

	private function is_raw_data_valid($raw_user_data, $validations) {
		if ($raw_user_data === null) {
			return [array('type' => 'danger', 'text' => 'Sin datos de usuario')];
		}

		$errors = [];

		foreach ($validations as $key => $data) {
			$valid = isset($raw_user_data[$key]);
			if ( ! $valid) {
				$errors[] = array(
					'type' => 'danger',
					'text' => $data['empty_text']
				);
				continue;
			}

			if ($valid && isset($data['regex'])) {
				$valid = $valid && preg_match($data['regex'], $raw_user_data[$key]);
			}

			if ($valid && isset($data['check_func'])) {
				$valid = $valid && $data['check_func']($raw_user_data[$key]);
			}

			if ( ! $valid) {
				$errors[] = array(
					'type' => 'danger',
					'text' => $data['error_text']
				);
			}
		}

		return $errors;
	}

	private function get_avatar_filename($raw_user_data) {
		$matches = [];
		preg_match('/([a-z0-9]{3,32})\.([\w]{3,})$/i', $raw_user_data['avatar'], $matches);
		if (count($matches) == 3 && $matches[1] == $raw_user_data['id']) {
			return sprintf('%s.%s', $matches[1], $matches[2]);
		}

		if (isset($raw_user_data['avatar'])) {
			$matches = [];
			preg_match('/^data:image\/(.*);base64\,(.*)$/', $raw_user_data['avatar'], $matches);
			if (count($matches) == 3) {
				$avatar_filename = sprintf('%s.%s', $raw_user_data['id'], $matches[1]);
				file_put_contents(sprintf('avatars/%s', $avatar_filename), base64_decode($matches[2]));
				return $avatar_filename;
			}

			return null;
		}
	}

}
