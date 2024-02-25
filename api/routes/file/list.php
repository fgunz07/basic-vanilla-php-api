<?php

header('Access-Control-Allow-Origin: *');
header('Context-Type: application/json');

include_once '../../database.php';
include_once '../../models/file.php';

if($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.0 405 Method Not Allowed');

    echo json_encode([
        'type'      => $_SERVER['REQUEST_METHOD'],
        'path'      => $_SERVER['REQUEST_URI'],
        'message'   => 'Method Not Allowed',
        'data'      => [],
    ]);
    return;
}

try {
    $db         = (new Database)->connect();
    $file       = new File($db);
    $result     = $file->read();
    $rows       = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        // normalize file name on response
        $row['filename'] = explode('.', $row['filename'])[0]; 
        $rows[] = $row;
    }

    header("HTTP/1.0 200 Ok");

    echo json_encode([
        'type'      => $_SERVER['REQUEST_METHOD'],
        'path'      => $_SERVER['REQUEST_URI'],
        'message'   => 'Successfull',
        'data'      => $rows
    ]);
    return;

} catch(Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");

    echo json_encode([
        'type'      => $_SERVER['REQUEST_METHOD'],
        'path'      => $_SERVER['REQUEST_URI'],
        'message'   => $e->getMessage(),
        'data'      => [
            'exception_type' => get_class($e),
            'exception_code' => $e->getCode(),
            'exception_file' => $e->getFile(),
            'exception_line' => $e->getLine(),
        ],
    ]);
    return;
}
