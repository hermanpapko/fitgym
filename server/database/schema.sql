-- Изменение типа поля 'avatar' на LONGBLOB для хранения аватаров
ALTER TABLE users MODIFY COLUMN avatar LONGBLOB; 