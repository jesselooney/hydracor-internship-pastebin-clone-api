USE my_app;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE pastes (
    id CHAR(36) PRIMARY KEY,
    user_id INT NOT NULL,
    text TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);