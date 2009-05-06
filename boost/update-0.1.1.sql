BEGIN;

ALTER TABLE faxmaster_fax ADD COLUMN firstName varchar(64);
ALTER TABLE faxmaster_fax ADD COLUMN lastName character(64);
ALTER TABLE faxmaster_fax ADD COLUMN bannerId character(9);

ALTER TABLE faxmaster_fax ADD COLUMN numPages integer default 0;

COMMIT;
