INSERT INTO roles (name, description)
VALUES ('admin', 'Administrator with full access to all resources.'),
	   ('treasurer', 'The treasurer has access only to record and update payments'),
       ('student', 'Students can only view payments for the current week');

INSERT INTO roles (name, description)
VALUES ('treasurer', 'Treasurer responsible for managing financial transactions and records.'),
       ('student', 'Student with access to course materials and resources.');

INSERT INTO users (email, password, role_id)
VALUES ('omar@email.com', '$2y$12$4IWGjUCRV7MUoLNj8b/HfObwX1ZfT9D82Cgv.InH1r2IiCKJ6yqlO', 2);