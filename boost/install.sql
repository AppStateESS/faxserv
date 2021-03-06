-- @author Jeremy Booker <jbooker at tux dot appstate dot edu>

CREATE TABLE IF NOT EXISTS faxmaster_fax (
    id INT NOT NULL,
    senderPhone VARCHAR( 15 ) NOT NULL,
    fileName VARCHAR( 32 ) NOT NULL,
    dateReceived INTEGER NOT NULL,
    numPages INTEGER default 0,
    firstName VARCHAR( 64 ),
    lastName VARCHAR( 64 ),
    bannerId VARCHAR( 9 ),
    state INTEGER NOT NULL,
    printed INTEGER NOT NULL,
    hidden INTEGER NOT NULL default 0,
    archived INTEGER NOT NULL default 0,
    whichArchive VARCHAR( 255 ),
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS faxmaster_action_log (
    id INT NOT NULL,
    username VARCHAR( 32 ) NOT NULL,
    activity VARCHAR( 32 ) NOT NULL,
    timePerformed INTEGER NOT NULL,
    faxName VARCHAR( 50 ),
    PRIMARY KEY (id)
);
