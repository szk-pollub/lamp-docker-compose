CREATE DATABASE IF NOT EXISTS doge;
USE doge;
CREATE TABLE IF NOT EXISTS `visitors`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `visit_date` TIMESTAMP,
    PRIMARY KEY (`id`)
);