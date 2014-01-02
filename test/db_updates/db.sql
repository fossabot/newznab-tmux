ALTER TABLE  `predb`
  ADD `hash` VARCHAR( 32 ) NULL,
  ADD INDEX (`hash`);
ALTER TABLE  `releases`
  ADD `dehashstatus` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `haspreview`,
  ADD `nfostatus` TINYINT NOT NULL DEFAULT 0 after `dehashstatus`,
  ADD `reqidstatus` TINYINT(1) NOT NULL DEFAULT '0' AFTER `dehashstatus`,
  ADD `bitwise` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `reqidstatus`,
  ADD INDEX `ix_releases_nfostatus` (`nfostatus` ASC) USING HASH,
  ADD INDEX `ix_releases_reqidstatus` (`reqidstatus` ASC) USING HASH,
  ADD INDEX `ix_releases_status` (`ID`, `nfostatus`, `bitwise`, `passwordstatus`, `dehashstatus`, `reqidstatus`, `musicinfoID`, `consoleinfoID`, `bookinfoID`, `haspreview`, `categoryID`);

DELIMITER $$
CREATE TRIGGER check_insert BEFORE INSERT ON releases FOR EACH ROW BEGIN IF NEW.searchname REGEXP '[a-fA-F0-9]{32}' OR NEW.name REGEXP '[a-fA-F0-9]{32}' THEN SET NEW.bitwise = ((NEW.bitwise & ~512)|512);ELSEIF NEW.name REGEXP '^\\[[[:digit:]]+\\]' THEN SET NEW.bitwise = ((NEW.bitwise & ~1024)|1024); END IF; END; $$
CREATE TRIGGER check_update BEFORE UPDATE ON releases FOR EACH ROW BEGIN IF NEW.searchname REGEXP '[a-fA-F0-9]{32}' OR NEW.name REGEXP '[a-fA-F0-9]{32}' THEN SET NEW.bitwise = ((NEW.bitwise & ~512)|512);ELSEIF NEW.name REGEXP '^\\[[[:digit:]]+\\]' THEN SET NEW.bitwise = ((NEW.bitwise & ~1024)|1024); END IF; END; $$
DELIMITER ;

UPDATE releases set bitwise = 0;
UPDATE releases SET bitwise = ((bitwise & ~512)|512) WHERE searchname REGEXP '[a-fA-F0-9]{32}' OR name REGEXP '[a-fA-F0-9]{32}';
UPDATE releases SET bitwise = ((bitwise & ~1024)|1024) WHERE name REGEXP '^\\[[[:digit:]]+\\]';
		




