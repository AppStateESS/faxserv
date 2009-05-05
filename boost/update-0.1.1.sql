BEGIN;

ALTER TABLE faxmaster_fax ADD COLUMN firstName varchar(64);
ALTER TABLE faxmaster_fax ADD COLUMN lastName character(64);
ALTER TABLE faxmaster_fax ADD COLUMN bannerId character(9);

ALTER TABLE faxmaster_fax ADD COLUMN num_pages integer;

COMMIT;
