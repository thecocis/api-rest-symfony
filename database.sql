CREATE DATABASE IF NOT EXISTS api_rest_symfony;
USE api_rest_symfony;

CREATE  TABLE users(
id              int(255) auto_increment not null,
name            varchar(50) not null,
surname         varchar(150),
email           varchar(255) not null,
password        varchar(255) not null,
entity          varchar(150),
charge          varchar(150),
avatar          varchar(255),
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