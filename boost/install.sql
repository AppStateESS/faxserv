-- @author Jeremy Booker <jbooker at tux dot appstate dot edu>

CREATE TABLE skeleton_skeletons (
    id              int not null default 0,
    key_id          int not null default 0,
    title           varchar(255),
    description     text,
    file_id         int not null default 0,
    died            int not null default 0,
    PRIMARY KEY   (id)
);

CREATE TABLE skeleton_bones (
    id              int not null default 0,
    skeleton_id     int not null default 0,
    title           varchar(255),
    description     text,
    file_id         int not null default 0,
    PRIMARY KEY (id)
);
