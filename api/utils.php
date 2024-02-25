<?php

define('ROOT_DIR', __DIR__.'/../public/uploads/');

function generateRandomString(int $l = 10) {
    return substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes($l))), 0, $l);
}