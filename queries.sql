SELECT u.id, u.email, u.password, u.full_name FROM users u
INNER JOIN roles r ON r.id = u.role_id;