create database `onepace` /*!40100 default character set utf8 */;
use `onepace`;

create table `arcs` (
	`id` int(10) unsigned not null auto_increment,
	`title` varchar(45) not null,
	`chapters` varchar(45) not null default '',
	`episodes` varchar(45) not null default '',
	`nyaa_id` varchar(6) default null,
	`torrent_hash` varchar(40) default '',
	`resolution` varchar(10) default null,
	`completed` tinyint(1) not null,
	`hidden` tinyint(1) not null,
	`released` tinyint(1) not null default '0',
	primary key (`id`),
	unique key `id_unique` (`id`),
	unique key `nyaa_id_unique` (`nyaa_id`)
);

create table `episodes` (
	`id` int(10) unsigned not null auto_increment,
	`crc32` varchar(8) not null default '',
	`arc_id` int(10) unsigned default null,
	`resolution` varchar(10) not null default '',
	`chapters` varchar(45) not null default '',
	`episodes` varchar(45) not null default '',
	`torrent_hash` varchar(40) not null default '',
	`released_date` datetime default null,
	`title` varchar(45) not null default '',
	`hidden` tinyint(1) not null default 0,
	`part` int(11) default null,
	primary key (`id`),
	unique key `id` (`id`),
	key `arc_id_fk_idx` (`arc_id`),
	constraint `arc_id_fk` foreign key (`arc_id`) references `arcs` (`id`) on delete set null
);
