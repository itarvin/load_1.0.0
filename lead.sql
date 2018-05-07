ALTER TABLE member DROP INDEX qq;
ALTER TABLE admin add status enum('1','0') DEFAULT '0' comment '销售状态' after users;
ALTER TABLE logs add phone varchar(11) DEFAULT '' comment '手机号' after qq;
ALTER TABLE logs add weixin varchar(20) DEFAULT '' comment '微信号' after qq;



-- 集群共享session
-- drop table id exists session;
-- create table startmoon_session
-- (
-- 	session_id varchar(255) not null,
-- 	session_expire int(11) not null,
-- 	session_data blob,
-- 	unique key 'session_id' ('session_id')
-- )ENGINE=MyISAM DEFAULT CHARSET = utf8;


-- RBAC
-- 角色表
drop table if exists role;
create table roles
(
	id int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
	role_name varchar(50) NOT NULL COMMENT '角色名称',
	role_status enum('1','0') default '0' COMMENT '0,启用；1,禁用',
	role_desc varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
	PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色表';


drop table if exists privilege;
create table privilege
(
	id mediumint unsigned not null auto_increment comment 'Id',
	pri_name varchar(30) not null comment '权限名称',
	module_name varchar(30) not null default '' comment '模块名称',
	controller_name varchar(30) not null default '' comment '控制器名称',
	action_name varchar(30) not null default '' comment '方法名称',
	parent_id mediumint unsigned not null default '0' comment '上级权限Id',
	primary key (id)
)engine=InnoDB default charset=utf8 comment '权限表';



-- 管理员表已存在
drop table if exists role_pri;
create table role_pri
(
	pri_id mediumint unsigned not null comment '权限id',
	role_id mediumint unsigned not null comment '角色id',
	key pri_id(pri_id),
	key role_id(role_id)
)engine=InnoDB default charset=utf8 comment '角色权限';


drop table if exists admin_role;
create table admin_role
(
	admin_id mediumint unsigned not null comment '管理员id',
	role_id mediumint unsigned not null comment '角色id',
	key admin_id(admin_id),
	key role_id(role_id)
)engine=InnoDB default charset=utf8 comment '管理员角色';



-- 系统配置表
drop table if exists config;
create table config
(
	id mediumint unsigned not null auto_increment comment '配置id',
	title varchar(50) not null comment '配置标题',
	name varchar(50) not null comment '配置名称',
	content text comment '内容',
	sort_num tinyint unsigned not null default '0' comment '排序',
	tips varchar(255) not null comment '',
	field_type varchar(50) not null comment '字段属性',
	field_value varchar(255) not null comment '字段值',
	is_system enum('0','1') default '0' comment '是否系统预留字段',
	primary key(id),
	key title(title),
	key name(name)
)engine = InnoDB default charset = utf8 comment '网站配置表';
