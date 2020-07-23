create database `blog_data`;

create table `bl_users` (
	`id` tinyint(11) unsigned not null auto_increment PRIMARY KEY,
	`account` varchar(16) not null,
	`phone` char(11) not null,
    `password` varchar(60) not null,
	`create_time` int(11) not null,
    `update_time` int(11) not null,
	`permission` varchar(30) not null,
    `login_ip` int unsigned not null,
    `create_ip` int unsigned not null,
    `status` enum("0","1","2") not null,
    UNIQUE KEY `phone` (`phone`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table `bl_article` (
	`id` tinyint(11) unsigned not null auto_increment PRIMARY KEY,
    `title` varchar(50) not null,
	`pic_url` varchar(100) not null,
    `content` text not null,
	`read_num` tinyint not null,
    `like_num` tinyint not null,
    `tag` varchar(30) not null,
    `type` enum("1","2","3") not null,
    `create_time` int(11) not null,
    `update_time` int(11) not null,
    `uid` int(11) not null,
    `uploader` varchar(100) not null,
    `isdel` enum("0","1") not null,
    UNIQUE KEY `title` (`title`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
