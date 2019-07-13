<?php
require_once '../RequestHeader.php';
include_once '../torrent_utils.php';

$torrents = TorrentUtils::getTorrents();
if(isset($_GET['hash'])) {
	$torrent_hash = $_GET['hash'];
} else if($torrent_hash == null || strlen($torrent_hash) == 0) {
	echo "No torrent hash found.";
	exit;
}

$torrent = TorrentUtils::findTorrent($torrents, $torrent_hash);
if($torrent == null) {
	echo "Torrent with hash '" . $torrent_hash . "' not found.";
	exit;
}

$path = $torrent['path'];
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($path) . "\""); 
readfile($path);
