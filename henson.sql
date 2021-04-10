PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
  CREATE TABLE tasks ( id integer primary key autoincrement, name varchar(255) not null, email varchar(255) not null, taskText text , isDone boolean not null default false, wasEdited boolean not null default false);
  DELETE FROM sqlite_sequence;
COMMIT;
