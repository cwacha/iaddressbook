CREATE TABLE addressbooks (
    id int unsigned NOT NULL auto_increment,
    userid varchar(255) NOT NULL,
    displayname varchar(255) NOT NULL,
    uri varchar(255) NOT NULL,
    description varchar(255) NOT NULL,
	ctag int NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE addressbook (
    id int unsigned NOT NULL auto_increment,
    uid varchar(255) NOT NULL,
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
    company tinyint(1) NOT NULL default 0,
    birthdate varchar(255) NOT NULL,
    note text NOT NULL,
    addresses text NOT NULL,
    emails text NOT NULL,
    phones text NOT NULL,
    chathandles text NOT NULL,
    relatednames text NOT NULL,
    urls text NOT NULL,
    modification_ts INTEGER NOT NULL default 0,
    etag int NOT NULL default 1,
    PRIMARY KEY (id)
) COLLATE utf8_general_ci;

CREATE TABLE addressbook_cat (
    id int unsigned NOT NULL auto_increment,
    uid varchar(255) NOT NULL,
    name varchar(255) NOT NULL,
    modification_ts int NOT NULL default 0,
    etag int NOT NULL default 1,
    PRIMARY KEY (id)
) COLLATE utf8_general_ci;

CREATE TABLE addressbook_catmap (
    id int unsigned NOT NULL auto_increment,
    category_id int unsigned NOT NULL,
    person_id int unsigned NOT NULL,
    PRIMARY KEY (id)
) COLLATE utf8_general_ci;
