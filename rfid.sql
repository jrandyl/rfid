CREATE TABLE `registrants` (
  `id` varchar(64) UNIQUE PRIMARY KEY NOT NULL DEFAULT (encode(sha256(random()::text::bytea), 'hex')),
  `rfid` varchar(64) UNIQUE NOT NULL,
  `status` int UNIQUE NOT NULL
);

CREATE TABLE `entries` (
  `id` varchar(64) UNIQUE PRIMARY KEY NOT NULL DEFAULT (encode(sha256(random()::text::bytea), 'hex')),
  `registrants_rfid` varchar(64) UNIQUE NOT NULL,
  `status` int UNIQUE NOT NULL,
  `created_at` timestamp NOT NULL
);

CREATE INDEX `registrants_index_0` ON `registrants` (`rfid`);

CREATE INDEX `registrants_index_1` ON `registrants` (`status`);

CREATE INDEX `entries_index_2` ON `entries` (`registrants_rfid`);

CREATE INDEX `entries_index_3` ON `entries` (`status`);

ALTER TABLE `entries` ADD FOREIGN KEY (`registrants_rfid`) REFERENCES `registrants` (`rfid`);
