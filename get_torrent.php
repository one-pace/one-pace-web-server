<?php
require_once 'RequestHeader.php';
include_once 'torrent_utils.php';

$torrents = TorrentUtils::getTorrents();
if(isset($_GET['torrent_hash'])) {
	$torrent_hash = $_GET['torrent_hash'];
} else if($torrent_hash == null || strlen($torrent_hash) == 0) {
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
	$path = $torrent['path'];
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"" . basename($path) . "\""); 
	readfile($path);
}
