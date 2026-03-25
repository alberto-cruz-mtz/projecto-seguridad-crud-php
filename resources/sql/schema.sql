CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE IF NOT EXISTS roles
(
    id          serial primary key,
    name        varchar(30) not null unique,
    description varchar(255)
);

CREATE TABLE IF NOT EXISTS users
(
    id        uuid default uuid_generate_v4() not null primary key,
    email     varchar(150)                    not null unique,
    password  varchar(200)                    not null,
    role_id   integer                         not null references roles
);

CREATE TYPE gender_type AS ENUM ('male', 'female', 'other');

CREATE TABLE IF NOT EXISTS people
(
    user_id      UUID PRIMARY KEY REFERENCES users (id) ON DELETE CASCADE,
    first_name   VARCHAR(50)  NOT NULL,
    last_name    VARCHAR(50)  NOT NULL,
    age          SMALLINT     NOT NULL CHECK (age >= 0 AND age <= 100),
    address      VARCHAR(255) NOT NULL,
    phone_number VARCHAR(15)  NOT NULL UNIQUE,
    gender       GENDER_TYPE  NOT NULL
);

CREATE TABLE students
(
    id        VARCHAR(10) NOT NULL UNIQUE default concat('S', substring(md5(random()::text), 1, 9)),
    person_id UUID        NOT NULL UNIQUE REFERENCES people (user_id) ON DELETE CASCADE,
    PRIMARY KEY (person_id, id)
);

CREATE TABLE payments
(
    id               SERIAL PRIMARY KEY,
    student_id       VARCHAR(10)    NOT NULL REFERENCES students (id) ON DELETE CASCADE,
    week_number      SMALLINT       NOT NULL CHECK (week_number >= 1 AND week_number < 54),
    payment_date     TIMESTAMPTZ    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount           DECIMAL(10, 2) NOT NULL CHECK (amount > 0),
    receiver_user_id UUID           NOT NULL REFERENCES users (id) ON DELETE RESTRICT
);
