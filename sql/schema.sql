-- Location Master Database Schema
-- Optimized indexes for high-traffic read APIs

CREATE DATABASE IF NOT EXISTS location_master
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE location_master;

CREATE TABLE IF NOT EXISTS states (
    state_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    state_name VARCHAR(100) NOT NULL,
    PRIMARY KEY (state_id),
    UNIQUE KEY uk_states_name (state_name),
    KEY idx_states_name (state_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cities (
    city_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    state_id INT UNSIGNED NOT NULL,
    city_name VARCHAR(120) NOT NULL,
    PRIMARY KEY (city_id),
    KEY idx_cities_state_id (state_id),
    KEY idx_cities_name (city_name),
    KEY idx_cities_state_name (state_id, city_name),
    CONSTRAINT fk_cities_state
        FOREIGN KEY (state_id) REFERENCES states (state_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS areas (
    area_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    city_id INT UNSIGNED NOT NULL,
    area_name VARCHAR(150) NOT NULL,
    pincode CHAR(6) NOT NULL,
    PRIMARY KEY (area_id),
    KEY idx_areas_city_id (city_id),
    KEY idx_areas_pincode (pincode),
    KEY idx_areas_name (area_name),
    KEY idx_areas_city_pincode (city_id, pincode),
    CONSTRAINT fk_areas_city
        FOREIGN KEY (city_id) REFERENCES cities (city_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for quick testing
INSERT INTO states (state_id, state_name) VALUES
(1, 'Uttar Pradesh')
ON DUPLICATE KEY UPDATE state_name = VALUES(state_name);

INSERT INTO cities (city_id, state_id, city_name) VALUES
(10, 1, 'Varanasi')
ON DUPLICATE KEY UPDATE city_name = VALUES(city_name);

INSERT INTO areas (area_id, city_id, area_name, pincode) VALUES
(100, 10, 'Sigra', '221005'),
(101, 10, 'Lanka', '221005')
ON DUPLICATE KEY UPDATE area_name = VALUES(area_name), pincode = VALUES(pincode);
