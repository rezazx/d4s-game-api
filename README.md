# d4s-api
Connect4 (D4S) is a game with online and single player capabilities.

An api for playing connect 4 written in php.
Php version 7.2 or higher is required to run.

```
Connect Four (also known as Connect 4, Four Up, Plot Four, Find Four, Captain's Mistress, Four in a Row, Drop Four, and Gravitrips in the Soviet Union) is a two-player connection board game, in which the players choose a color and then take turns dropping colored tokens into a seven-column, six-row vertically suspended grid. The pieces fall straight down, occupying the lowest available space within the column. The objective of the game is to be the first to form a horizontal, vertical, or diagonal line of four of one's own tokens. Connect Four is a solved game. The first player can always win by playing the right moves.
[read more on wikipedia](https://en.wikipedia.org/wiki/Connect_Four)
```

## back-end
This is an open source project to implement a php api for CONNECT4.
Users can play randomly with each other or with the embedded robot ID number 1.

## front-end
For the appearance of the game, the react native library for Android has been used, which will soon put its source in the relevant repository.  [d4s-game-app](https://github.com/rezazx/d4s-game-app)

# Installation

- clone files to your host
- run : ``` composer update ```
- edit defies-sample.php to defines.php
    - set host
    - set database name
    - set username
    - set password
    - set _SITE_URL_ and _BASE_PATH_  (for example if you install in http://example.com/d4s-app set _SITE_URL_='http://example.com' and _BASE_PATH_='/d4s-app/' )
    - set _KEY_ (enter random and strong string)
    - if use SSL set _IS_SSL_ to true
- edit .htaccess on line 7 and enter your rewritebase like '/d4s-app/'
- run install to create database and tables (for example http://example.com/d4s-app/install)

After the database installation is successful, it is better to delete the install.php file

