-- @author Jeremy Booker <jbooker at tux dot appstate dot edu>

CREATE TABLE faxmaster_fax (
    id INT NOT NULL,
    senderPhone VARCHAR( 15 ) NOT NULL,
    fileName VARCHAR( 32 ) NOT NULL,
    dateReceived INTEGER NOT NULL,
    pages INTEGER,
    firstName VARCHAR( 64 ),
    lastName VARCHAR( 64 ),
    bannerId VARCHAR( 9 ),
    state INTEGER NOT NULL,
    printed INTEGER NOT NULL,
    PRIMARY KEY (id)
);
