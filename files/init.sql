CREATE DATABASE IF NOT EXISTS monitor;
CREATE USER 'dba'@'%' IDENTIFIED BY '123456';
GRANT ALL PRIVILEGES ON monitor.* TO 'dba'@'%';
--CREATE DATABASE IF NOT EXISTS test;
--USE dev;
--CREATE TABLE IF NOT EXISTS (...);
