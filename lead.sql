alter table admin add status enum('1','0') default '0' comment '销售状态' after users;
alter table logs add phone varchar(11) default '' comment '手机号' after qq;
alter table logs add weixin varchar(20) default '' comment '微信号' after qq;
