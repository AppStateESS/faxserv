BEGIN;

ALTER TABLE faxmaster_fax ADD COLUMN archived INTEGER NOT NULL default 0;
ALTER TABLE faxmaster_fax ADD COLUMN whichArchive varchar(255) default NULL;

COMMIT;
