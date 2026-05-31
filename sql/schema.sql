-- ProSensia Internship Portal — MySQL schema + seed data
-- Import via phpMyAdmin or: mysql -u root -p prosensia < schema.sql

CREATE DATABASE IF NOT EXISTS prosensia DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prosensia;

DROP TABLE IF EXISTS chat_messages;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS certificate_requests;
DROP TABLE IF EXISTS form_c;
DROP TABLE IF EXISTS assignments;
DROP TABLE IF EXISTS task_checkpoints;
DROP TABLE IF EXISTS daily_tasks;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS materials;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin','management','mentor','intern') NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE profiles (
  user_id INT PRIMARY KEY,
  phone VARCHAR(40), city VARCHAR(80),
  university VARCHAR(160), degree VARCHAR(120), graduation_year VARCHAR(8),
  skills TEXT, github VARCHAR(200), linkedin VARCHAR(200), portfolio VARCHAR(200),
  bio TEXT, avatar_color VARCHAR(20) DEFAULT '#facc15',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  track VARCHAR(120), batch VARCHAR(60),
  start_date DATE, payment_plan ENUM('monthly','full') DEFAULT 'monthly',
  agreed TINYINT(1) DEFAULT 0,
  status ENUM('draft','submitted','approved') DEFAULT 'draft',
  submitted_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE daily_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  est_minutes INT DEFAULT 30,
  cadence ENUM('single','multi_day') DEFAULT 'single',
  duration_days INT DEFAULT 1,
  task_date DATE NOT NULL,
  due_date DATE,
  assigned_by INT, assigned_to INT NULL,
  status ENUM('pending','in_progress','done') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE task_checkpoints (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_id INT NOT NULL,
  day_no INT NOT NULL,
  label VARCHAR(200) NOT NULL,
  done TINYINT(1) DEFAULT 0,
  FOREIGN KEY (task_id) REFERENCES daily_tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  week INT, due_date DATE,
  description TEXT,
  github_url VARCHAR(300),
  status ENUM('not_started','submitted','approved','needs_revision') DEFAULT 'not_started',
  submitted_at TIMESTAMP NULL,
  grade INT NULL,
  feedback TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE form_c (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  supervisor_name VARCHAR(160), supervisor_email VARCHAR(160),
  organization VARCHAR(160), start_date DATE, end_date DATE,
  total_hours VARCHAR(20),
  responsibilities TEXT, skills_learned TEXT, challenges TEXT, feedback TEXT,
  rating INT DEFAULT 0,
  status ENUM('draft','submitted','approved','rejected') DEFAULT 'draft',
  reviewer_note TEXT,
  submitted_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE certificate_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  track VARCHAR(120), batch VARCHAR(60),
  status ENUM('pending','issued','rejected') DEFAULT 'pending',
  serial VARCHAR(60), final_grade VARCHAR(20), mentor_rating INT,
  reviewer_note TEXT,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  issued_at TIMESTAMP NULL, issued_by INT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT,
  created_by INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE team_members (
  team_id INT NOT NULL, user_id INT NOT NULL,
  PRIMARY KEY(team_id, user_id),
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE chat_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  channel_key VARCHAR(120) NOT NULL,
  from_id INT NOT NULL,
  text TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (from_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(channel_key)
) ENGINE=InnoDB;

CREATE TABLE materials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  kind ENUM('pdf','video','link') DEFAULT 'link',
  url VARCHAR(400) NOT NULL,
  module VARCHAR(120),
  meta VARCHAR(80),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================
-- SEED DATA
-- Passwords (plain → bcrypt): all use password = "password123"
-- bcrypt of "password123":
-- =========================
INSERT INTO users (id,name,email,password,role) VALUES
 (1,'Aisha Khan','admin@prosensia.com','$2b$10$RmiZPAMqDKLLL0hohWMzl.Ub1pL0c9ZUCQxscrkOugohB0AcxqQcS','super_admin'),
 (2,'Bilal Ahmed','manager@prosensia.com','$2b$10$RmiZPAMqDKLLL0hohWMzl.Ub1pL0c9ZUCQxscrkOugohB0AcxqQcS','management'),
 (3,'Sara Iqbal','mentor@prosensia.com','$2b$10$RmiZPAMqDKLLL0hohWMzl.Ub1pL0c9ZUCQxscrkOugohB0AcxqQcS','mentor'),
 (4,'Hamza Raza','intern@prosensia.com','$2b$10$RmiZPAMqDKLLL0hohWMzl.Ub1pL0c9ZUCQxscrkOugohB0AcxqQcS','intern'),
 (5,'Ali Hassan','ali@prosensia.com','$2b$10$RmiZPAMqDKLLL0hohWMzl.Ub1pL0c9ZUCQxscrkOugohB0AcxqQcS','intern');

INSERT INTO profiles (user_id,phone,city,university,degree,graduation_year,skills,github,linkedin,bio) VALUES
 (4,'+92 300 1234567','Lahore','FAST NUCES','BS Computer Science','2026','React, TypeScript, Node.js, MySQL','https://github.com/hamzaraza','https://linkedin.com/in/hamzaraza','Aspiring full-stack engineer.'),
 (5,'+92 311 9876543','Karachi','NUST','BS Software Engineering','2026','PHP, Laravel, MySQL','https://github.com/alihassan','https://linkedin.com/in/alihassan','Backend-focused developer.');

INSERT INTO enrollments (user_id,track,batch,start_date,payment_plan,agreed,status,submitted_at) VALUES
 (4,'Full-Stack Web Development','Batch 3 (Summer 2026)',DATE_SUB(CURDATE(),INTERVAL 21 DAY),'monthly',1,'approved',NOW()),
 (5,'Full-Stack Web Development','Batch 3 (Summer 2026)',DATE_SUB(CURDATE(),INTERVAL 21 DAY),'full',1,'approved',NOW());

INSERT INTO daily_tasks (title,description,est_minutes,cadence,task_date,due_date,assigned_by,assigned_to,status) VALUES
 ('Daily standup video (2 min)','Record a short Loom about yesterday/today.',10,'single',CURDATE(),CURDATE(),2,NULL,'pending'),
 ('Watch: REST API design patterns','24 min lecture in Module 4.',30,'single',CURDATE(),CURDATE(),3,4,'done'),
 ('Build CRUD API — 5-day sprint','Express + MySQL CRUD with auth.',60,'multi_day',DATE_SUB(CURDATE(),INTERVAL 1 DAY),DATE_ADD(CURDATE(),INTERVAL 4 DAY),3,NULL,'in_progress'),
 ('Submit Task 04 — GitHub repo','Push CRUD API and link the assignment.',90,'single',CURDATE(),CURDATE(),3,4,'in_progress');

INSERT INTO task_checkpoints (task_id,day_no,label,done) VALUES
 (3,1,'Scaffold project + DB schema',1),
 (3,2,'Auth endpoints + JWT',1),
 (3,3,'CRUD endpoints + validation',0),
 (3,4,'Tests + Postman collection',0),
 (3,5,'Deploy + README',0);

INSERT INTO assignments (user_id,title,week,due_date,description,github_url,status,grade,feedback) VALUES
 (4,'Static Portfolio Site',1,DATE_SUB(CURDATE(),INTERVAL 14 DAY),'Responsive portfolio.','https://github.com/hamzaraza/portfolio','approved',92,'Great structure.'),
 (4,'Todo App with LocalStorage',2,DATE_SUB(CURDATE(),INTERVAL 7 DAY),'React + TS CRUD.','https://github.com/hamzaraza/todo-ts','approved',88,'Good tests.'),
 (4,'REST API with Express + MySQL',3,DATE_SUB(CURDATE(),INTERVAL 1 DAY),'CRUD endpoints, JWT auth, Zod.','https://github.com/hamzaraza/rest-mysql','needs_revision',72,'Add validation on PATCH route.'),
 (4,'Auth + RBAC Frontend',4,DATE_ADD(CURDATE(),INTERVAL 4 DAY),'Login + role dashboards.',NULL,'not_started',NULL,NULL),
 (4,'Capstone Spec & Wireframes',5,DATE_ADD(CURDATE(),INTERVAL 11 DAY),'1-page spec + Figma.',NULL,'not_started',NULL,NULL);

INSERT INTO teams (id,name,description,created_by) VALUES
 (1,'Capstone — Team A','E-commerce capstone group',3),
 (2,'Batch 3 — All Interns','Cohort-wide channel for Batch 3',2);

INSERT INTO team_members (team_id,user_id) VALUES
 (1,3),(1,4),(1,2),(2,2),(2,3),(2,4),(2,5);

INSERT INTO chat_messages (channel_key,from_id,text) VALUES
 ('channel:announcements',1,'Welcome to Batch 3! 🎉 Onboarding starts Monday at 5 PM.'),
 ('channel:announcements',1,'Form C templates are now available under Enrollment → Form C.'),
 ('team:1',3,'Standup tomorrow 5 PM. Bring blockers.'),
 ('team:1',4,'Pushed the auth module to main.'),
 ('dm:3|4',3,'Reviewed your latest commit — solid work.');

INSERT INTO materials (title,kind,url,module,meta) VALUES
 ('HTML & CSS Fundamentals','pdf','#','Module 1','4.2 MB'),
 ('Intro to JavaScript','video','#','Module 2','42 min'),
 ('MDN Web Docs','link','https://developer.mozilla.org','Reference','external'),
 ('React Crash Course','video','#','Module 3','1h 12m'),
 ('SQL Cheat Sheet','pdf','#','Module 4','1.1 MB');
