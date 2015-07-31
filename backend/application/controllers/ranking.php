<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/Custom_REST_Controller.php';

class Ranking extends Custom_REST_Controller {

	function index_get() {
		$this->load->database();

		$top3 = [];

		// Total seats
		$query = $this->db->query("SELECT COUNT(*) FROM journey");
		if ($query->num_rows() == 0) {
			$this->json_output($top3);
			return;
		}

		// Top 10 users
		$query = $this->db->query("
			SELECT u.id, u.avatar, u.about, SUM(j.seats / ts.total_seats) + (COUNT(*) / tj.total_journeys) AS ranking
			FROM user AS u
				LEFT JOIN journey AS j ON j.user_id = u.id
				LEFT JOIN journey_passenger AS p ON p.journey_id = j.id
				JOIN (SELECT COUNT(*) AS total_journeys FROM journey) AS tj
				JOIN (SELECT SUM(seats) AS total_seats FROM journey) AS ts
			WHERE u.type != 'admin'
			GROUP BY u.id
			ORDER BY ranking DESC
			LIMIT 3
		");

		$rank = 1;
		foreach ($query->result_array() as $row) {
			$row['rank'] = $rank++;
			$top3[] = $row;
		}

		$this->json_output($top3);
	}

}
