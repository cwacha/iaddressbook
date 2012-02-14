CREATE TABLE addressbook (
    id serial PRIMARY KEY,
    title varchar(255) NOT NULL,
    firstname varchar(255) NOT NULL,
    firstname2 varchar(255) NOT NULL,
    lastname varchar(255) NOT NULL,
    suffix varchar(255) NOT NULL,
    nickname varchar(255) NOT NULL,
    phoneticfirstname varchar(255) NOT NULL,
    phoneticlastname varchar(255) NOT NULL,
    jobtitle varchar(255) NOT NULL,
    department varchar(255) NOT NULL,
    organization varchar(255) NOT NULL,
    company smallint NOT NULL default 0,
    birthdate date NOT NULL default NULL,
    note text NOT NULL,
    addresses text NOT NULL,
    emails text NOT NULL,
    phones text NOT NULL,
    chathandles text NOT NULL,
    relatednames text NOT NULL,
    urls text NOT NULL,
    creationdate timestamp without time zone NOT NULL default NULL,
    modificationdate timestamp without time zone NOT NULL default NULL
);

CREATE TABLE addressbook_cat (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL,
    type int NOT NULL,
    query text NOT NULL
);

CREATE TABLE addressbook_catmap (
    id serial PRIMARY KEY,
    category_id int NOT NULL,
    person_id int NOT NULL
);

/*
CREATE TABLE addressbook_truth (
    id int unsigned NOT NULL auto_increment,
    syncpartner_id int unsigned NOT NULL,
    remote_id int unsigned NOT NULL,
    local_id int unsigned NOT NULL,
    mod_date datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY (id)
);
 
CREATE TABLE addressbook_sync (
    id int unsigned NOT NULL auto_increment,
    syncpartner_id int unsigned NOT NULL,
    remote_id int unsigned NOT NULL,
    sync_state int unsigned NOT NULL,
    mod_date datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY (id)
);

CREATE TABLE addressbook_syncactions (
    id int unsigned NOT NULL auto_increment,
    syncpartner_id int unsigned NOT NULL,
    syncaction int unsigned NOT NULL,
    remote_id int unsigned NOT NULL,
    local_id int unsigned NOT NULL,
    vcard_data text NOT NULL,
    PRIMARY KEY (id)
);
*/

