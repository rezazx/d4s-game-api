<?php

use MRZX\User;

if (DB::query("CREATE TABLE IF NOT EXISTS "._DB_PERFIX_."users(
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nickname VARCHAR(64) ,
    email VARCHAR(128) ,
    auth VARCHAR(32) ,
    create_at DATETIME,
    last_login DATETIME,
    game_status TINYINT
) ENGINE = "._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8; "))
    echo "<h2>Table "._DB_PERFIX_."users created successfully </h2>";
else
    echo "<h2>Error creating table Table "._DB_PERFIX_."users </h2>";


if (DB::query("CREATE TABLE IF NOT EXISTS "._DB_PERFIX_."d4s(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    p1_id INT(6) NOT NULL,
    p2_id INT(6) NOT NULL,
    board TEXT,
    auth VARCHAR(32) ,
    winner INT(6),
    create_at DATETIME,
    last_update DATETIME NULL DEFAULT NULL
) ENGINE = "._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8; "))
    echo "<h2>Table "._DB_PERFIX_."d4s created successfully </h2>";
else
    echo "<h2>Error creating table Table "._DB_PERFIX_."d4s </h2>";


if (DB::query("CREATE TABLE IF NOT EXISTS "._DB_PERFIX_."game_request (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6),
    target VARCHAR(32),
    create_at DATETIME
) ENGINE = "._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8; "))
    echo "<h2>Table "._DB_PERFIX_."game_request created successfully </h2>";
else
    echo "<h2>Error creating table Table "._DB_PERFIX_."game_request </h2>";



$user=new User();
if($user->register('MRZX-ROBOT','d4s-bot@mrzx.ir'))
    echo 'The robot user was created.';
else
    echo 'Error adding robot user.';