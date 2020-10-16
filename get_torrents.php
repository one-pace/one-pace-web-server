<?php
require_once 'RequestHeader.php';
require_once 'db_context.php';
include_once 'string_utils.php';
include_once 'secure_indexer.php';
include_once 'torrent_utils.php';

$context = new db_context();
$context->connect();
$arcs = $context->list_arcs();
$episodes = $context->list_episodes();
$context->disconnect();
$data = [];
$ftp_torrents = TorrentUtils::getTorrents();
$torrents = [];

foreach($arcs as $arc) {
	if ($arc['released'] !== 1) {
		continue;
	}
	$torrent = TorrentUtils::findTorrent($ftp_torrents, $arc['torrent_hash']);
	if ($torrent != null) {
		$torrents[] = $torrent;
	}
}
foreach($episodes as $episode) {
	$is_released = isset($episode["released_date"]) && strtotime($episode["released_date"]) <= time();
	$existing_torrent = array_search($episode['torrent_hash'], array_column($torrents, 'hash'));
	if (!$is_released || $existing_torrent != null) {
		continue;
	}
	$torrent = TorrentUtils::findTorrent($ftp_torrents, $episode['torrent_hash']);
	if ($torrent != null) {
		$torrents[] = $torrent;
	}
}

foreach ($torrents as $torrent) {
	$data['torrents'][] = [
		'age_days' => $torrent['age_days'],
		'hash' => $torrent['hash'],
		'trackers' => $torrent['trackers'],
		'magnet' => $torrent['magnet'],
		'torrent_name' => $torrent['torrent_name'],
		'display_name' => $torrent['display_name'],
		'size_raw' => $torrent['size_raw'],
		'size' => $torrent['size'],
		'created' => $torrent['created'],
		'created_raw' => $torrent['created_raw']
	];
}

function usorttorrentnames($a, $b) {
	return strnatcmp($a['display_name'], $b['display_name']);
}
usort($data['torrents'], "usorttorrentnames");

echo json_encode($data);
?>
