#
# Table structure for table 'tx_pxshopware_domain_model_item'
#
CREATE TABLE tx_pxshopware_domain_model_item (

    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    cache_identifier varchar(2000) DEFAULT '' NOT NULL,
    last_update int(11) DEFAULT '0' NOT NULL,
    result mediumtext NOT NULL,

    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    INDEX cache_identifier_idx (cache_identifier(20))
);