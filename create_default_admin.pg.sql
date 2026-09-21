-- Run this once in Supabase SQL Editor after database.pg.sql.
-- It is safe to run again: it does not create duplicate accounts.
INSERT INTO public.users (name, email, password_hash, role, address, phone, profile_picture)
SELECT 'Site Admin', 'asadbinjafor@gmail.com',
       '$2y$10$wa2k1wEjstjfm.3BvkxdUuFh8mOrGhAMvxQuHUmTz5TVkDB2Ebx6S',
       'admin', 'Dhaka', '00000000000', ''
WHERE NOT EXISTS (
    SELECT 1 FROM public.users
    WHERE LOWER(email) = LOWER('asadbinjafor@gmail.com')
);

UPDATE public.users
SET role = 'admin',
    password_hash = '$2y$10$wa2k1wEjstjfm.3BvkxdUuFh8mOrGhAMvxQuHUmTz5TVkDB2Ebx6S'
WHERE LOWER(email) = LOWER('asadbinjafor@gmail.com');

SELECT id, name, email, role
FROM public.users
WHERE LOWER(email) = LOWER('asadbinjafor@gmail.com');
