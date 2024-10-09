-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Unique ID for each user (auto-incremented)
    username VARCHAR(50) NOT NULL UNIQUE,
    -- Unique username for each user
    email VARCHAR(100) NOT NULL UNIQUE,
    -- Unique email for each user
    password VARCHAR(255) NOT NULL,
    -- User's password (hashed)
    profile_picture VARCHAR(255) DEFAULT 'img/user/app/default_user.png',
    -- User profile picture
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Account creation time
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Last updated timestamp
);
-- Meetings Table
CREATE TABLE IF NOT EXISTS meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Unique ID for each meeting (auto-incremented)
    meeting_id VARCHAR(255) NOT NULL,
    -- Unique identifier for the meeting (e.g., 'ABC123')
    meeting_hosts JSON NOT NULL,
    -- JSON array of host user IDs (up to 32 hosts)
    meeting_title VARCHAR(255),
    -- Optional title for the meeting
    short_description TEXT,
    -- Short description of the meeting
    total_users INT DEFAULT 0,
    -- Track the number of participants in the meeting
    removed_users JSON,
    -- JSON array of banned/removed users' IDs
    preferences JSON DEFAULT '{"allow_audio": true,"allow_video": true,"allow_screenshare": true,"reactions_enabled": true}',
    -- JSON object for meeting preferences
    status ENUM('active', 'ended') DEFAULT 'active',
    -- Meeting status (active or ended)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Meeting creation time
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Last updated timestamp
    FOREIGN KEY (meeting_hosts) REFERENCES users(id) ON DELETE CASCADE -- Link to the users table, delete meeting if the host is deleted
);
-- Schedule Table
CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Unique ID for each schedule entry (auto-incremented)
    meeting_id VARCHAR(255) NOT NULL,
    -- Reference to the meeting (link to meetings table)
    scheduled_time DATETIME NOT NULL,
    -- Scheduled date and time for the meeting
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Schedule creation time
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Last updated timestamp
    FOREIGN KEY (meeting_id) REFERENCES meetings(meeting_id) ON DELETE CASCADE -- Link to the meetings table, delete schedule if the meeting is deleted
);