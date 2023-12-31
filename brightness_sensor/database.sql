CREATE DATABASE mqtt_esp32;

CREATE TABLE sensor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    datetime TIMESTAMP NOT NULL,
    brightness FLOAT NOT NULL
);

CREATE TABLE relay (
    id INT AUTO_INCREMENT PRIMARY KEY,
    datetime TIMESTAMP NOT NULL,
    state CHAR NOT NULL
);