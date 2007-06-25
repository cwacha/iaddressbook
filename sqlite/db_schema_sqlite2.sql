CREATE TABLE addressbook (
    id INTEGER PRIMARY KEY,
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
    company INTEGER NOT NULL default 0,
    birthdate date NOT NULL default '0000-00-00',
    image mediumblob NOT NULL,
    note text NOT NULL,
    addresses text NOT NULL,
    emails text NOT NULL,
    phones text NOT NULL,
    chathandles text NOT NULL,
    relatednames text NOT NULL,
    urls text NOT NULL,
    creationdate datetime NOT NULL default '0000-00-00 00:00:00',
    modificationdate datetime NOT NULL default '0000-00-00 00:00:00'
);

CREATE TABLE addressbook_cat (
    id INTEGER PRIMARY KEY,
    name varchar(255) NOT NULL,
    type int unsigned NOT NULL,
    query text NOT NULL
);

CREATE TABLE addressbook_catmap (
    id INTEGER PRIMARY KEY,
    category_id int unsigned NOT NULL,
    person_id int unsigned NOT NULL
);

/*
CREATE TABLE addressbook_truth (
    id INTEGER PRIMARY KEY,
    syncpartner_id int unsigned NOT NULL,
    remote_id varchar(255) NOT NULL,
    local_id int unsigned NOT NULL,
    mod_date datetime NOT NULL default '0000-00-00 00:00:00'
);
 
CREATE TABLE addressbook_sync (
    id INTEGER PRIMARY KEY,
    syncpartner_id int unsigned NOT NULL,
    remote_id varchar(255) NOT NULL,
    sync_state int unsigned NOT NULL,
    mod_date datetime NOT NULL default '0000-00-00 00:00:00'
);

CREATE TABLE addressbook_syncactions (
    id INTEGER PRIMARY KEY,
    syncpartner_id int unsigned NOT NULL,
    side int unsigned NOT NULL,
    syncaction int unsigned NOT NULL,
    remote_id varchar(255) NOT NULL,
    local_id int unsigned NOT NULL,
    vcard_data text NOT NULL
);
*/