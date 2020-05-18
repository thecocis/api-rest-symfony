CREATE DATABASE IF NOT EXISTS api_rest_symfony;
USE api_rest_symfony;

CREATE TABLE participants(
id              int(255) auto_increment not null,
user_id         int(255) not null,
event_id        int(255) not null,
CONSTRAINT pk_participants PRIMARY KEY(id),
CONSTRAINT fk_participation_user FOREIGN KEY(user_id) REFERENCES users(id),
CONSTRAINT fk_participation_event FOREIGN KEY(event_id) REFERENCES events(id)
)ENGINE=InnoDb;

CREATE TABLE comments(
id              int(255) auto_increment not null,
user_id         int(255) not null,
from_id         int(255) not null,
body            int(255) not null,
created_at      datetime DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT pk_comment PRIMARY KEY(id),
CONSTRAINT fk_comment_user FOREIGN KEY(user_id) REFERENCES users(id),
CONSTRAINT fk_comment_from FOREIGN KEY(from_id) REFERENCES users(id)
)ENGINE=InnoDb;

CREATE  TABLE users(
id              int(255) auto_increment not null,
name            varchar(50) not null,
surname         varchar(150),
email           varchar(255) not null,
password        varchar(255) not null,
entity          varchar(150),
charge          varchar(150),
image          varchar(255),
biography       text,
created_at      datetime DEFAULT CURRENT_TIMESTAMP,
valoration      int(255),
prefix          int(255) not null,
telephone       int(255) not null,
num_valoration  int(255) not null,
role            varchar(20),
CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE events(
id              int(255) auto_increment not null,
user_id         int(255) not null,
url             varchar(255) not null,
created_at      datetime DEFAULT CURRENT_TIMESTAMP,
status          int(11),
title           varchar(255) not null,
description     text,
price           int(11),
date            date,
maxCapacity     int(11),
actualCapacity  int(11),
CONSTRAINT pk_events PRIMARY KEY(id),
CONSTRAINT fk_event_user FOREIGN KEY(user_id) REFERENCES users(id)
)ENGINE=InnoDb;

CREATE TABLE valorations(
id              int(255) auto_increment not null,
user_id         int(255) not null,
from_id         int(255) not null,
value           int(255) not null,
CONSTRAINT pk_valorations PRIMARY KEY(id),
CONSTRAINT fk_valoration_user FOREIGN KEY(user_id) REFERENCES users(id)
)ENGINE=InnoDb;

