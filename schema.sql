drop table if exists posts;
create table posts (
  id integer primary key autoincrement,
  timestamp integer not null,
  timestamp_modified integer,
  author text not null,
  text text not null,
  image text not null,
  status text not null,
  type text not null
);

drop table if exists options;
create table options (
  id integer primary key autoincrement,
  speed integer not null
);

insert into options values (1,15000);
insert into posts values (1, 1398034771, NULL, 'asda', '', '1398034771.jpg', 'moderation', 'image');
insert into posts values (2, 1398034876, NULL, 'aasda', 'broo', '1398034876.jpg', 'moderation', 'hybrid');
insert into posts values (3, 1398034890, NULL, 'hey', 'heyyyÿ', '', 'moderation', 'text');