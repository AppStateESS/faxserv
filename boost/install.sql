-- @author Jeremy Booker <jbooker at tux dot appstate dot edu>

CREATE TABLE faxmaster_fax (
    id INT NOT NULL,
    senderPhone VARCHAR( 15 ) NOT NULL,
    fileName VARCHAR( 32 ) NOT NULL,
    state INT NOT NULL,
    PRIMARY KEY (id)
);
