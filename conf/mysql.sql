CREATE TABLE addressbook (
    id int unsigned NOT NULL auto_increment,
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
    birthdate date NOT NULL default '0000-00-00',
    note text NOT NULL,
    addresses text NOT NULL,
    emails text NOT NULL,
    phones text NOT NULL,
    chathandles text NOT NULL,
    relatednames text NOT NULL,
    urls text NOT NULL,
    creationdate datetime NOT NULL default '0000-00-00 00:00:00',
    modificationdate datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) COLLATE utf8_general_ci;

CREATE TABLE addressbook_cat (
    id int unsigned NOT NULL auto_increment,
    name varchar(255) NOT NULL,
    type int unsigned NOT NULL,
    query text NOT NULL,
    PRIMARY KEY (id)
) COLLATE utf8_general_ci;

CREATE TABLE addressbook_catmap (
    id int unsigned NOT NULL auto_increment,
    category_id int unsigned NOT NULL,
    person_id int unsigned NOT NULL,
    PRIMARY KEY (id)
) COLLATE utf8_general_ci;


