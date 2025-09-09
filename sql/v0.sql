-- Tabla de participantes
CREATE TABLE IF NOT EXISTS participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  dorsal INT NOT NULL UNIQUE,
  status ENUM('active','abandoned','finished') DEFAULT 'active'
);

-- Tabla de controles
CREATE TABLE IF NOT EXISTS controls (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  km_point DECIMAL(5,2) NOT NULL,                     -- punto kilométrico
  status ENUM('no-preparado','abierto','cerrado') DEFAULT 'no-preparado',
  open_time TIME DEFAULT NULL,                        -- hora de apertura
  close_time TIME DEFAULT NULL,                       -- hora de cierre
  responsable VARCHAR(100) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  latitude DECIMAL(9,6) DEFAULT NULL,
  longitude DECIMAL(9,6) DEFAULT NULL
);

-- Tabla de check-ins
CREATE TABLE IF NOT EXISTS checkins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL,
  control_id INT NOT NULL,
  ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (participant_id) REFERENCES participants(id),
  FOREIGN KEY (control_id) REFERENCES controls(id)
);

-- Datos iniciales de prueba
INSERT INTO participants (name, dorsal) VALUES
('Dev Ana', 101),
('Dev Juan', 102),
('Dev Marta', 103);

INSERT INTO controls (name, description, km_point, status, open_time, close_time, responsable, phone, latitude, longitude) VALUES
('Salida', 'Punto de salida de la prueba', 0.00, 'abierto', '08:00:00', '10:00:00', 'Javi Ramos', '+34660887081', 41.227345, 1.537890),
('Avituallamiento 1', 'Primer avituallamiento con agua y fruta', 20.00, 'no-preparado', NULL, NULL, 'Josep Massana', '+34600112233', 41.250123, 1.560456),
('Avituallamiento 2', 'Segundo avituallamiento', 40.00, 'no-preparado', NULL, NULL, 'Rubén Fernández', '+34600998877', 41.275678, 1.580234),
('Meta', 'Llegada y entrega de obsequios', 74.00, 'no-preparado', NULL, NULL, 'Àngels Rebollo', '+34600665544', 41.300345, 1.600678);
