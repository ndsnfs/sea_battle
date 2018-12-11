DELETE FROM battle_cells;
DELETE FROM battle_fields;
DELETE FROM battle_games;
DELETE FROM battle_players;

DROP TABLE battle_cells;
DROP TABLE battle_fields;
DROP TABLE battle_games;
DROP TABLE battle_players;

--CREATE SEQUENCE auto_inc_battle_players;

CREATE TABLE battle_players(
	id INT NOT NULL DEFAULT nextval('auto_inc_battle_players'),
	name VARCHAR(80) NOT NULL,
	PRIMARY KEY (id),
	UNIQUE (name)
);

--CREATE SEQUENCE auto_inc_battle_games;

CREATE TABLE battle_games(
	id INT NOT NULL DEFAULT nextval('auto_inc_battle_games'),
	number VARCHAR(80) NOT NULL,
	player_id INT NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (player_id) REFERENCES battle_players(id) ON DELETE CASCADE
);

--CREATE SEQUENCE auto_inc_battle_fields;

CREATE TABLE battle_fields(
	id INT NOT NULL DEFAULT nextval('auto_inc_battle_fields'),
	game_id INT NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (game_id) REFERENCES battle_games(id) ON DELETE CASCADE
);

CREATE TABLE battle_cells(
	field_id INT NOT NULL,
	coordinat VARCHAR(3) NOT NULL,
	state INT NOT NULL,
	PRIMARY KEY (field_id, coordinat),
	FOREIGN KEY (field_id) REFERENCES battle_fields(id) ON DELETE CASCADE
);