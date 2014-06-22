CREATE TABLE addressbooks (
    id INTEGER PRIMARY KEY ASC,
    userid TEXT,
    displayname TEXT,
    uri TEXT,
    description TEXT,
	ctag INTEGER
);

CREATE TABLE addressbook (
    id INTEGER PRIMARY KEY,
    uid TEXT NOT NULL,
    title TEXT NOT NULL,
    firstname TEXT NOT NULL,
    firstname2 TEXT NOT NULL,
    lastname TEXT NOT NULL,
    suffix TEXT NOT NULL,
    nickname TEXT NOT NULL,
    phoneticfirstname TEXT NOT NULL,
    phoneticlastname TEXT NOT NULL,
    jobtitle TEXT NOT NULL,
    department TEXT NOT NULL,
    organization TEXT NOT NULL,
    company INTEGER NOT NULL default 0,
    birthdate TEXT NOT NULL,
    note TEXT NOT NULL,
    addresses TEXT NOT NULL,
    emails TEXT NOT NULL,
    phones TEXT NOT NULL,
    chathandles TEXT NOT NULL,
    relatednames TEXT NOT NULL,
    urls TEXT NOT NULL,
    modification_ts INTEGER NOT NULL default 0,
    etag INTEGER NOT NULL default 1
);

CREATE TABLE addressbook_cat (
    id INTEGER PRIMARY KEY,
    uid TEXT NOT NULL,
    name TEXT NOT NULL,
    modification_ts INTEGER NOT NULL default 0,
    etag INTEGER NOT NULL default 1
);

CREATE TABLE addressbook_catmap (
    id INTEGER PRIMARY KEY,
    category_id INTEGER NOT NULL,
    person_id INTEGER NOT NULL
);
