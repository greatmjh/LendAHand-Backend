CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- creates all the tables for the database (note that this doesn't create the database itself)
CREATE TABLE IF NOT EXISTS users(
	user_id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	email VARCHAR(250) NOT NULL UNIQUE,
	password TEXT NOT NULL,
	full_name VARCHAR(50) NOT NULL,
	bio VARCHAR(250) NOT NULL,
	phone_no VARCHAR(15) NOT NULL,
	latitude DECIMAL NOT NULL,
	longitude DECIMAL NOT NULL,
	total_donations INTEGER DEFAULT 0
);

-- refactoring code to edit an existing table to have a full name column instead -- only run this if you have already made a users table with values:
-- ALTER TABLE users ADD COLUMN full_name VARCHAR(50);
-- UPDATE users SET full_name = concat(f_name, ' ', l_name);
-- ALTER TABLE users ALTER COLUMN full_name SET NOT NULL;
-- ALTER TABLE USERS DROP COLUMN f_name;
-- ALTER TABLE USERS DROP COLUMN l_name;


CREATE TABLE IF NOT EXISTS item_tree(
	item_id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	item_name VARCHAR(50) NOT NULL,
	parent_id uuid,

	CONSTRAINT fk_parent_id 
		FOREIGN KEY(parent_id) 
		REFERENCES item_tree(item_id)
);


CREATE TABLE IF NOT EXISTS notifications(
	notification_id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	user_id uuid,
	content TEXT NOT NULL,
	read BOOLEAN DEFAULT FALSE NOT NULL,
	on_click TEXT NOT NULL,
	heading VARCHAR(25) NOT NULL,
	notif_time TIMESTAMP,

 	CONSTRAINT fk_user_id
		FOREIGN KEY(user_id)
		REFERENCES users(user_id)
);


CREATE TABLE IF NOT EXISTS items_donor(
	item_code uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	donor uuid NOT NULL,
	item_class uuid NOT NULL,
	item_name VARCHAR(50) NOT NULL,
	qty INTEGER DEFAULT 0 NOT NULL,

	CONSTRAINT fk_donor 
		FOREIGN KEY(donor) 
		REFERENCES users(user_id),

	CONSTRAINT fk_item_class
		FOREIGN KEY(item_class) 
		REFERENCES item_tree(item_id)
);


CREATE TABLE IF NOT EXISTS requests(
	request_id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	donee uuid NOT NULL,
	items_donor uuid NOT NULL,
	qty INTEGER NOT NULL,
	accepted BOOLEAN DEFAULT FALSE NOT NULL,

	CONSTRAINT fk_donee 
		FOREIGN KEY(donee) 
		REFERENCES users(user_id),

	CONSTRAINT fk_items_donor
		FOREIGN KEY(items_donor) 
		REFERENCES items_donor(item_code)
);


CREATE TABLE IF NOT EXISTS general_requests(
	donee uuid,
	items_class uuid,
	qty INTEGER NOT NULL,

	CONSTRAINT fk_donee 
		FOREIGN KEY(donee) 
		REFERENCES users(user_id),

	CONSTRAINT fk_items_class
		FOREIGN KEY(items_class) 
		REFERENCES item_tree(item_id),

	PRIMARY KEY(donee, items_class)
);

-- to handle deletion of general requests
CREATE OR REPLACE FUNCTION fn_drop_zero_genreq()
RETURNS TRIGGER LANGUAGE plpgsql
AS $$BEGIN
	IF (NEW.qty <= 0) THEN 
		DELETE FROM general_requests WHERE donee = NEW.donee AND items_class = NEW.items_class;
	END IF;
	RETURN NEW;
END$$;

CREATE TRIGGER trg_drop_zero_genreq
AFTER UPDATE ON general_requests
FOR EACH ROW
EXECUTE FUNCTION fn_drop_zero_genreq();

CREATE TABLE IF NOT EXISTS session_keys (
	session_key uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	user_id uuid NOT NULL,

	CONSTRAINT fk_user_id
		FOREIGN KEY (user_id)
		REFERENCES users(user_id)
		ON DELETE CASCADE
);

