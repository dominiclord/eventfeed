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