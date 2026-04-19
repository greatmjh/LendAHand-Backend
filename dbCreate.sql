
-- creates all the tables for the database (note that this doesn't create the database itself)
CREATE TABLE users(
	user_id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	email VARCHAR(250) NOT NULL UNIQUE,
	password TEXT NOT NULL,
	f_name VARCHAR(25) NOT NULL,
	l_name VARCHAR(25) NOT NULL,
	bio VARCHAR(250) NOT NULL,
	phone_no VARCHAR(15) NOT NULL,
	latitude DECIMAL(3,2) NOT NULL,
	longitude DECIMAL(3,2) NOT NULL
);

CREATE TABLE item_tree(
	item_id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	item_name VARCHAR(50) NOT NULL,
	parent_id uuid,

	CONSTRAINT fk_parent_id 
		FOREIGN KEY(parent_id) 
		REFERENCES item_tree(item_id)
);


CREATE TABLE notifications(
	notifcation_id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	user_id uuid,
	content TEXT NOT NULL,
	Read BOOLEAN DEFAULT FALSE NOT NULL,
	On_click TEXT NOT NULL,

 	CONSTRAINT fk_user_id
		FOREIGN KEY(user_id)
		REFERENCES users(user_id)
);


CREATE TABLE items_donor(
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


CREATE TABLE requests(
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


CREATE TABLE general_requests(
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

CREATE TABLE session_keys (
	session_key uuid PRIMARY KEY DEFAULT gen_random_uuid();
	user_id uuid;

	CONSTRAINT fk_user_id
		FOREIGN KEY (user_id)
		REFERENCES users(user_id)
);

