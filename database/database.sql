-- =====================================================================
-- DEVELOPMENT OF A WEB-BASED SCHEDULING SYSTEM WITH CONSTRAINT ALGORITHM
-- Database: scheduling_system
-- Engine: MySQL / MariaDB (InnoDB)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS scheduling_system
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE scheduling_system;

-- ---------------------------------------------------------------------
-- Table: users
-- Stores admins and lecturers/staff who can be assigned to schedules
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(100) DEFAULT NULL,
    password    VARCHAR(255) NOT NULL,           -- hashed with password_hash()
    role        ENUM('admin','lecturer','staff') NOT NULL DEFAULT 'lecturer',
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: resources
-- Rooms / labs / halls / equipment that can be booked
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS resources (
    resource_id     INT AUTO_INCREMENT PRIMARY KEY,
    resource_name   VARCHAR(100) NOT NULL,
    resource_type   ENUM('classroom','laboratory','lecture_hall','equipment') NOT NULL,
    capacity        INT DEFAULT 0,
    status          ENUM('available','unavailable') NOT NULL DEFAULT 'available',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_resources_type (resource_type)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: timeslots
-- Discrete day/time blocks that schedules are assigned into
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS timeslots (
    slot_id     INT AUTO_INCREMENT PRIMARY KEY,
    day         ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
    start_time  TIME NOT NULL,
    end_time    TIME NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timeslots_day (day),
    CONSTRAINT chk_time_order CHECK (start_time < end_time)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: constraints_tbl
-- Catalogue of hard/soft constraints enforced or considered by the engine
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS constraints_tbl (
    constraint_id     INT AUTO_INCREMENT PRIMARY KEY,
    constraint_name   VARCHAR(100) NOT NULL,
    constraint_type   ENUM('hard','soft') NOT NULL DEFAULT 'hard',
    description        VARCHAR(255) DEFAULT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: schedules
-- The actual bookings: a user assigned to a resource in a timeslot
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS schedules (
    schedule_id   INT AUTO_INCREMENT PRIMARY KEY,
    course_title  VARCHAR(150) DEFAULT NULL,
    resource_id   INT NOT NULL,
    slot_id       INT NOT NULL,
    user_id       INT NOT NULL,
    status        ENUM('approved','rejected','pending') NOT NULL DEFAULT 'pending',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sched_resource FOREIGN KEY (resource_id) REFERENCES resources(resource_id) ON DELETE CASCADE,
    CONSTRAINT fk_sched_slot     FOREIGN KEY (slot_id)     REFERENCES timeslots(slot_id)     ON DELETE CASCADE,
    CONSTRAINT fk_sched_user     FOREIGN KEY (user_id)     REFERENCES users(user_id)         ON DELETE CASCADE,
    INDEX idx_sched_resource_slot (resource_id, slot_id),
    INDEX idx_sched_user_slot (user_id, slot_id),
    INDEX idx_sched_status (status)
) ENGINE=InnoDB;

-- =====================================================================
-- SAMPLE DATA
-- =====================================================================

-- Default admin password is: admin123  (hashed below with password_hash/PASSWORD_DEFAULT)
INSERT INTO users (username, full_name, email, password, role, status) VALUES
('admin',      'System Administrator', 'admin@school.edu',   '$2y$10$Vd3Q0y9m1c8bYQeQnQe1KOe2Zz3Zt1Xz9Zt1Xz9Zt1Xz9Zt1Xz9Zt', 'admin', 'active'),
('jsmith',     'John Smith',           'jsmith@school.edu',  '$2y$10$Vd3Q0y9m1c8bYQeQnQe1KOe2Zz3Zt1Xz9Zt1Xz9Zt1Xz9Zt1Xz9Zt', 'lecturer', 'active'),
('mgarcia',    'Maria Garcia',         'mgarcia@school.edu', '$2y$10$Vd3Q0y9m1c8bYQeQnQe1KOe2Zz3Zt1Xz9Zt1Xz9Zt1Xz9Zt1Xz9Zt', 'lecturer', 'active');
-- NOTE: The hashes above are placeholders. Run reset_admin_password.php (included)
-- or use the PHP snippet in the Installation Guide to generate real hashes for your XAMPP PHP version.

INSERT INTO resources (resource_name, resource_type, capacity, status) VALUES
('Room A101',        'classroom',    40, 'available'),
('Room A102',        'classroom',    40, 'available'),
('Computer Lab 1',   'laboratory',   30, 'available'),
('Main Lecture Hall','lecture_hall',150, 'available'),
('Projector Set 1',  'equipment',     1, 'available');

INSERT INTO timeslots (day, start_time, end_time) VALUES
('Monday',    '08:00:00', '10:00:00'),
('Monday',    '10:00:00', '12:00:00'),
('Tuesday',   '08:00:00', '10:00:00'),
('Wednesday', '12:00:00', '14:00:00'),
('Thursday',  '14:00:00', '16:00:00'),
('Friday',    '08:00:00', '10:00:00');

INSERT INTO constraints_tbl (constraint_name, constraint_type, description) VALUES
('No Room Clash',       'hard', 'A resource cannot be double-booked in the same time slot'),
('No Lecturer Clash',   'hard', 'A lecturer cannot be assigned to two schedules in the same time slot'),
('No Time Conflict',    'hard', 'Prevents overlapping schedule assignments for the same slot'),
('Resource Availability','hard','Resource must be marked available/active to be booked'),
('Preferred Time',      'soft', 'Lecturer preference for morning or afternoon slots'),
('Preferred Resource',  'soft', 'Lecturer preference for a specific room or lab');

INSERT INTO schedules (course_title, resource_id, slot_id, user_id, status) VALUES
('Intro to Programming', 3, 1, 2, 'approved'),
('Database Systems',     1, 3, 3, 'approved');
