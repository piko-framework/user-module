
CREATE TABLE IF NOT EXISTS "user" (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  username TEXT NOT NULL,
  email TEXT NOT NULL,
  password TEXT NOT NULL,
  auth_key TEXT,
  confirmed_at INTEGER,
  blocked_at INTEGER,
  registration_ip TEXT,
  created_at INTEGER,
  updated_at INTEGER,
  last_login_at INTEGER,
  timezone TEXT,
  profil TEXT,
  UNIQUE(email)
);

CREATE TABLE IF NOT EXISTS auth_role
(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(64) NOT NULL,
  description TEXT,
  UNIQUE(name)
);

CREATE TABLE IF NOT EXISTS auth_permission
(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(64) NOT NULL,
  UNIQUE(name)
);

CREATE TABLE IF NOT EXISTS auth_role_has_permission
(
  role_id INTEGER NOT NULL,
  permission_id INTEGER NOT NULL,
  primary key (role_id, permission_id),
  foreign key (role_id) references auth_role(id) on delete cascade on update cascade,
  foreign key (permission_id) references auth_permission(id) on delete cascade on update cascade
);

CREATE TABLE IF NOT EXISTS auth_assignment
(
  role_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  primary key (role_id, user_id),
  foreign key (role_id) references auth_role(id) on delete cascade on update cascade,
  foreign key (user_id) references "user" (id) on delete cascade on update cascade
);

