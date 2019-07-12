<?php
require_once 'packages/PHP-Torrent-Scraper-master/httptscraper.php';
require_once 'packages/PHP-Torrent-Scraper-master/udptscraper.php';
require_once 'bencodemodel.php';
require_once 'string_utils.php';

class TorrentUtils {
    static function getTorrents() {
        $torrents = [];
        foreach(glob($_SERVER['DOCUMENT_ROOT'] . '/torrents/*.torrent') as $file) {
            $torrent = get_torrent($file);
            if($torrent != null) {
                $torrents[] = $torrent;
            }
        }
        return $torrents;
    }
    static function findTorrent($torrents, $hash) {
        foreach($torrents as $torrent) {
            if(strtolower($torrent['hash']) == strtolower($hash)) {
                return $torrent;
            }
        }
        return null;
    }
    static function scrape_http($torrent) {
        $httptscraper = new httptscraper();
        return $httptscraper->scrape($torrent["trackers"][0], $torrent['hash'])[0];
    }
}

function get_torrent($path) {
    $file_name = basename($path);
    $bencode = new BencodeModel();
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    if($extension == 'torrent') {
        $data = $bencode->decode_file($path);
        $trackers = array();
        if(isset($data["announce-list"]) && is_array($data["announce-list"])) {
            foreach($data['announce-list'][0] as $announce) {
                $trackers[] = $announce;
            }
        }
        $info = $data['info'];
        $dn = $info['name'];
        $size = 0;
        if(isset($info['files'])) {
            foreach($info['files'] as $file_info) {
                $size += $file_info['length'];
            }
        } else {
            $size = $info['length'];
        }
        $hash = sha1($bencode->encode($info));
        $magnet_url = "magnet:?xt=urn:btih:$hash";
        foreach($trackers as $tracker) {
            $magnet_url .= "&tr=$tracker";
        }
        $created_raw = $data['creation date'];
        $created = date('Y-m-d H:i:s', $created_raw);
        $interval = (new DateTime())->diff(new DateTime($created));
        return [
            'age_days' => $interval->days,
            'hash' => $hash,
            'trackers' => $trackers,
            'magnet' => $magnet_url,
            'torrent_name' => $file_name,
            'display_name' => $dn,
            'size_raw' => (int)$size,
            'size' => size_beautify($size),
            'created' => $created,
            'created_raw' => (int)$created_raw
        ];
    } else {
        return null;
    }
}
