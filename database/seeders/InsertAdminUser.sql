-- Insert admin user (password is 'password')
-- Run after migrations. Columns match: 0001_01_01_000000_create_users_table + 2025_03_13_100000_add_legacy_columns_to_users_table
INSERT INTO `users` (
  `id`,
  `name`,
  `email`,
  `email_verified_at`,
  `password`,
  `remember_token`,
  `created_at`,
  `updated_at`,
  `last_name`,
  `phone_number`,
  `accesslevel`,
  `active`,
  `username`,
  `mycode`,
  `Tournament_id`
) VALUES (
  1,
  'David',
  'dachildress@gmail.com',
  NULL,
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  NULL,
  NOW(),
  NOW(),
  'Childress',
  '(434) 929-1744',
  '0',
  '1',
  'dchildress',
  'abc123',
  1
);
