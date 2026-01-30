CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  email TEXT,
  password TEXT
);

INSERT INTO users (name,email,password)
VALUES ('Admin','admin@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

CREATE TABLE polls (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  question TEXT,
  status TEXT
);

CREATE TABLE options (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  poll_id INTEGER,
  option_text TEXT
);

CREATE TABLE votes (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  poll_id INTEGER,
  option_id INTEGER,
  ip_address TEXT,
  voted_at TEXT
);

CREATE TABLE vote_history (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  poll_id INTEGER,
  option_id INTEGER,
  ip_address TEXT,
  action TEXT,
  timestamp TEXT
);

INSERT INTO polls VALUES (1,'Who is best cricketer?','active');
INSERT INTO options VALUES
(1,1,'Virat Kohli'),
(2,1,'Rohit Sharma'),
(3,1,'MS Dhoni');
