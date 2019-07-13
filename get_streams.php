<?php
require_once 'RequestHeader.php';
require_once 'db_context.php';
include_once 'string_utils.php';
include_once 'secure_indexer.php';
$context = new db_context();
$context->connect();
$arcs = $context->list_arcs();
$episodes = $context->list_episodes();
$context->disconnect();
$data = [];
foreach($arcs as $arc) {
	$data['arcs'][] = [
		'id' => $arc['id'],
		'title' => $arc['title'],
		'chapters' => $arc['chapters'],
		'resolution' => $arc['resolution'],
		"released" => $arc['released'] == 1,
		"episodes" => $arc['episodes'],
		'torrent_hash' => $arc['torrent_hash']
	];
}
foreach($episodes as $episode) {
	$is_released = isset($episode["released_date"]) && strtotime($episode["released_date"]) <= time();

	// Set the episode object
	$releasedDate = $is_released ? date("F j, Y", strtotime($episode["released_date"])) : "";
	$data['episodes'][] = [
		'id' => $episode['id'],
		'crc32' => $is_released ? $episode['crc32'] : "",
		'resolution' => $episode['resolution'],
		'title' => $episode['title'],
		'chapters' => $episode['chapters'],
		"episodes" => $episode["episodes"],
		"released_date" => $releasedDate,
		"isReleased" => $is_released,
		'part' => $episode['part'],
		'arcId' => $episode['arc_id'],
		'torrent_hash' => $episode['torrent_hash']
	];
}
function usortchapters($a, $b) {
	return strnatcmp($a['chapters'], $b['chapters']);
}
usort($data['arcs'], "usortchapters");
usort($data['episodes'], 'usortchapters');
for($i = 0; $i < sizeof($data['arcs']); $i++) {
	$arc = $data['arcs'][$i];
	if($arc['chapters'] == null) {
		unset($data['arcs'][$i]);
		$data['arcs'][] = $arc;
		$data['arcs'] = array_values($data['arcs']);
	}
}
echo json_encode($data);
?>
