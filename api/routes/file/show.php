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
    $db     = (new Database)->connect();
    $file   = new File($db);

    $params         = [];
    $queryString    = parse_str($_SERVER['QUERY_STRING'], $params);
    $result         = $file->show($params['id']);
    $row            = $result->fetch(PDO::FETCH_ASSOC);

    if(!$row) {
        header("HTTP/1.0 404 Not Found");

        echo json_encode([
            'type'      => $_SERVER['REQUEST_METHOD'],
            'path'      => $_SERVER['REQUEST_URI'],
            'message'   => 'Not Found',
            'data'      => []
        ]);
        return;
    }

    header("HTTP/1.0 200 Ok");
    // normalize file name on response
    $row['filename'] = explode('.', $row['filename'])[0]; 

    echo json_encode([
        'type'      => $_SERVER['REQUEST_METHOD'],
        'path'      => $_SERVER['REQUEST_URI'],
        'message'   => 'Successfull',
        'data'      => $row
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
