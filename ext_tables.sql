#
# Table structure for table 'tx_pxshopware_domain_model_article' -> fake table for EXT:solr indexing
#
CREATE TABLE tx_pxshopware_domain_model_article (
    title tinytext NOT NULL,

    KEY parent (pid),
    KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);

#
# Table structure for table 'tx_pxshopware_domain_model_category' -> fake table for EXT:solr indexing
#
CREATE TABLE tx_pxshopware_domain_model_category (
    title tinytext NOT NULL,

    KEY parent (pid),
    KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);
