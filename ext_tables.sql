#
# Table structure for table 'tx_icslayarservice_sources'
#
CREATE TABLE tx_icslayarservice_sources (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name varchar(30) DEFAULT '' NOT NULL,
	source varchar(255) DEFAULT '' NOT NULL,
	page tinytext,
	title varchar(255) DEFAULT '' NOT NULL,
	line2_ts text,
	line3_ts text,
	line4_ts text,
	attribution_ts text,
	actions text,
	actions_label text,
	image varchar(255) DEFAULT '' NOT NULL,
	type varchar(255) DEFAULT '' NOT NULL,
	coordinates text,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY name (name)
);
