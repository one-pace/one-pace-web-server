<?php
require_once 'RequestHeader.php';
require_once 'db_context.php';
include_once 'torrent_utils.php';
$torrents = TorrentUtils::getTorrents();
if(isset($_GET['episode'])) {
	$context = new db_context();
	$context->connect();
	$episode = $context->read_episode($_GET['episode']);
	$context->disconnect();
	if($episode == null) {
		echo "Episode not found";
		exit;
	} else {
		$torrent_hash = $episode["torrent_hash"];
	}
} else if(isset($_GET['torrent_hash'])) {
	$torrent_hash = $_GET['torrent_hash'];
}

if($torrent_hash == null || strlen($torrent_hash) == 0) {
	echo "No torrent hash found.";
	exit;
}

$torrent = TorrentUtils::findTorrent($torrents, $torrent_hash);
if($torrent == null) {
	echo "Torrent with hash '" . $torrent_hash . "' not found.";
	exit;
}

if(isset($_GET['json']) && $_GET['json'] == "true") {
	echo json_encode($torrent);
} else if (isset($_GET['magnet']) && $_GET['magnet'] == "true" && $torrent['magnet'] != null) {
	header("Location: " . $torrent['magnet']);
} else {
	header("Location: /torrents/" . $torrent["torrent_name"]);
}
