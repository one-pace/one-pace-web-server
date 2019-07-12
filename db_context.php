<?php
include_once 'config.php';
include_once 'utils.php';
include_once 'logger.php';

class db_context {
	private $connection;
	/* Help methods */
	function connect() {
		$socket = null;
		if(!DEBUG) {
			$socket = DB_SOCKET;
		}
		$this->connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT, $socket);
		if (mysqli_connect_errno()) {
			log_error('Failed to connect to MySQL: ' . mysqli_connect_error());
		}
		$this->connection->set_charset(DB_CHARSET);
	}
	function disconnect() {
		mysqli_close($this->connection);
	}
	function get_param_type($value) {
		if(is_int($value)) {
			return "i";
		} else if (is_double($value)) {
			return "d";
		} else if (is_string($value)) {
			return "s";
		} else if (is_bool($value)) {
			return "b";
		} else if ($value == null) {
			return "s";
		}
	}
	function bind_param($stmt, $params) {
		$types = "";
		$param_keys = [];
		foreach($params as $key => $value) {
			$param_keys[] = $value;
			$types .= $this->get_param_type($value);
		}
		$stmt->bind_param($types, ...$param_keys);
	}
	function get_result($stmt) {
		if(!$stmt->execute()) {
			log_error($stmt->error);
		}
		if(!$result = $stmt->get_result()) {
			log_error($stmt->error);
		}
		$stmt->close();
		$rows = [];
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}
	function get_single($stmt) {
		$rows = $this->get_result($stmt);
		if(sizeof($rows) > 0){
			return $rows[0];
		} else {
			return null;
		}
	}
	function execute($stmt) {
		if(!$stmt->execute()) {
			log_error($stmt->error);
		}
		$stmt->close();
	}
	function prepare($sql) {
		$connection = $this->connection;
		$stmt = $connection->prepare($sql);
		if($stmt == null || $stmt === false) {
			log_error('Wrong SQL: ' . $sql . ' Error: ' . $connection->errno . ' ' . $connection->error, E_USER_ERROR);
			exit;
		}
		return $stmt;
	}
	function prepare_and_bind($sql, $params = null) {
		$stmt = $this->prepare($sql);
		if($params != null) {
			$this->bind_param($stmt, $params);
		}
		return $stmt;
	}
	function prepare_and_get_result($sql, $params = null) {
		$stmt = $this->prepare_and_bind($sql, $params);
		return $this->get_result($stmt);
	}
	function prepare_and_get_single($sql, $params = null) {
		$stmt = $this->prepare_and_bind($sql, $params);
		return $this->get_single($stmt);
	}
	function prepare_and_execute($sql, $params = null) {
		$stmt = $this->prepare_and_bind($sql, $params);
		$this->execute($stmt);
	}
	/* Operations */
	function create($table, $params) {
		if(sizeof($params) == 0) {
			return false;
		}
		$sql = "insert into ".$table." (";
		foreach($params as $key => $value) {
			if($notfirst) {
				$sql .= ", ";
			} else {
				$notfirst = true;
			}
			$sql .= "`".$key."`";
		}
		$sql .= ") values(";
		$notfirst = false;
		foreach($params as $key => $value) {
			if($notfirst) {
				$sql .= ", ";
			} else {
				$notfirst = true;
			}
			$sql .= "?";
		}
		$sql .= ");";
		$this->prepare_and_execute($sql, $params);
		return true;
	}
	function read($table, $id) {
		return $this->prepare_and_get_single("select * from ".$table." where id = ?", ["id" => $id]);
	}
	function update($table, $id, $params) {
		if(sizeof($params) == 0) {
			return false;
		}
		$sql = "update ".$table." set ";
		foreach($params as $key => $value) {
			if($notfirst) {
				$sql .= ", ";
			} else {
				$notfirst = true;
			}
			$sql .= "`".$key."` = ?";
		}
		$sql .= " where id = ?;";
		$params["id"] = $id;
		$this->prepare_and_execute($sql, $params);
		return true;
	}
	function delete($table, $id) {
		$this->prepare_and_execute("delete from ".$table." where id = ?;", ["id" => $id]);
		return true;
	}

	/* Episode attachments */
	function create_episode_attachment($params) {
		return $this->create("episodeattachments", $params);
	}
	function read_episode_attachment($id) {
		return $this->read("episodeattachments", $id);
	}
	function delete_episode_attachment($id) {
		return $this->delete("episodeattachments", $id);
	}

	/* Users */
	function update_user($id, $params) {
		return $this->update("users", $id, $params);
	}
	/* Episodes */
	function create_episode($params) {
		return $this->create("episodes", $params);
	}
	function read_episode($id) {
		return $this->read("episodes", $id);
	}
	function update_episode($id, $params) {
		return $this->update("episodes", $id, $params);
	}
	function delete_episode($id) {
		return $this->delete("episodes", $id);
	}
	function list_episodes() {
		return $this->prepare_and_get_result("select * from episodes where hidden = false;");
	}
	function list_progress_episodes() {
		$episodes = $this->prepare_and_get_result("
			select
				*,
				((episodes.hidden is null or episodes.hidden = false) and (released_date is null or released_date > now())) as in_progress
			from episodes
			where hidden = false
			group by id
			order by abs(chapters)
			;
		");
		$arcs = $this->prepare_and_get_result("select * from arcs where hidden = false order by abs(chapters) asc;");
		$data = [];
		foreach($arcs as $arc) {
			$data['arcs'][] = [
				'id' => $arc['id'],
				'released' => $arc['released'],
				'title' => $arc['title'],
				'chapters' => $arc['chapters'],
				'episodes' => $arc['episodes'],
			];
		}
		foreach($episodes as $episode) {
			$data['episodes'][] = [
				'id' => $episode['id'],
				"crc32" => $episode["crc32"],
				"arc_id" => $episode['arc_id'],
				'part' => $episode['part'],
				'title' => $episode['title'],
				'chapters' => $episode['chapters'],
				"episodes" => $episode["episodes"],
				"resolution" => $episode["resolution"],
				"torrent_hash" => $episode["torrent_hash"],
				"in_progress" => $episode['in_progress'],
				"released_date" => $episode['released_date'] == null ? '' : $episode['released_date'],
			];
		}
		return $data;
	}

	/* Users */
	function create_user($params) {
		return $this->create("users", $params);
	}

	/* Arcs */
	function list_arcs() {
		return $this->prepare_and_get_result("select * from arcs where hidden = false;");
	}

	/* Issues */
	function create_issue($params) {
		return $this->create("issues", $params);
	}
	function read_issue($id) {
		return $this->read("issues", $id);
	}
	function update_issue($id, $params) {
		return $this->update("issues", $id, $params);
	}
	function delete_issue($id) {
		return $this->delete("issues", $id);
	}
	function list_issues($user, $episode_id) {
		$rows = $this->prepare_and_get_result(
			"select issues.*, episodes.id as episode_id from issues
			left join episodes on episodes.id = issues.episode_id
			where episodes.id = ?".
			($user == null || $user['role'] <= 1 ? " and episodes.hidden = false" : "")
			."
			order by completed, createddate
			;", ["episode_id" => $episode_id]
		);
		$attachments = $this->prepare_and_get_result(
			"select episodeattachments.* from episodeattachments
			left join episodes on episodeattachments.episode_id = episodes.id
			where episodes.id = ?".
			($user == null || $user['role'] <= 1 ? " and episodes.hidden = false" : "")
			."
			order by uploadeddate
			;", ["episode_id" => $episode_id]
		);
		$data = ["issues" => [], "episodeattachments" => []];
		foreach($rows as $row) {
			$data["issues"][] = [
				"id" => $row["id"],
				"episode_id" => $row["episode_id"],
				"description" => $row["description"],
				"createdby" => $row["createdby"],
				"createddate" => $row["createddate"],
				"completed" => $row['completed'] == 1
			];
		}
		foreach($attachments as $row) {
			$data["episodeattachments"][] = [
				"id" => $row['id'],
				"episode_id" => $row['episode_id'],
				"name" => $row["name"],
				"type" => $row["type"],
				"size" => $row["size"],
				"uploadeddate" => $row['uploadeddate'],
				"uploadedby" => $row['uploadedby'],
				"url" => '/episodeattachments/' . $row['episode_id'] . "_" . $row["uploadeddate"] . "_" . $row['name']
			];
		}
		return $data;
	}
}
