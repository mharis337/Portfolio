CREATE TABLE IF NOT EXISTS queue (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100),
    injury VARCHAR(100),
    severity INT,
    code VARCHAR(10),
    wait_time INT,
    place_in_line INT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);