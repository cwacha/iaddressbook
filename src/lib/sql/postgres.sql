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
    modification_ts INTEGER NOT NULL default 0,
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
