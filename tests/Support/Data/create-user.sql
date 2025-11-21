
INSERT INTO `auth_role` (`id`, `name`) VALUES (1, 'admin');

-- password is userAdminTest
INSERT INTO `user` (`id`, `name`, `email`, `username`, `password`)
VALUES (1, 'Admin user', 'admin@test.com', 'admin', 'f6554b6bafcf788e85983c31cd7d02101d427dfb');

INSERT INTO `auth_assignment` (`role_id`, `user_id`) VALUES (1, 1);
