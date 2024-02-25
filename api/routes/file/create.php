<?php

header('Access-Control-Allow-Origin: *');
header('Context-Type: application/json');

include_once '../../database.php';
include_once '../../models/file.php';
include_once '../../utils.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');

    echo json_encode([
        'type' => $_SERVER['REQUEST_METHOD'],
        'path' => $_SERVER['REQUEST_URI'],
        'message' => 'Method Not Allowed',
        'data' => [],
    ]);
    return;
}

try {
    $db = (new Database)->connect();
    $file = new File($db);
    $errorBag = [];
    $fillable = ['title', 'filename'];

    foreach($fillable as $f) {
        if(!array_key_exists($f, $_POST)) {
            $errorBag[] = "$f is required.";
        }
    }

    if(!isset($_FILES['thumb'])) {
        $errorBag[] = 'thumb is required.';
    }

    if(!empty($errorBag)) {
        header('HTTP/1.0 422 Method Not Allowed');

        echo json_encode([
            'type'      => $_SERVER['REQUEST_METHOD'],
            'path'      => $_SERVER['REQUEST_URI'],
            'message'   => 'Unprocessable Entity',
            'data'      => $errorBag
        ]);
        return;
    }

    // Generate random name for unique filenaming
    // prevent from duplication
    $fname  = $_POST['filename'];//generateRandomString(25);
    $fext   = pathinfo($_FILES['thumb']['name'], PATHINFO_EXTENSION);
    
    $newFname = $fname.'.'.$fext;

    $file->title    = $_POST['title'];
    $file->filename = $newFname;
    $file->thumb    = '/public/uploads/'.$newFname;

    move_uploaded_file($_FILES['thumb']['tmp_name'], ROOT_DIR.$newFname);

    $file->create();

    // normalize file name on response
    $file->filename = explode('.', $file->filename)[0]; 
    
    header("HTTP/1.0 200 Ok");

    echo json_encode([
        'type'      => $_SERVER['REQUEST_METHOD'],
        'path'      => $_SERVER['REQUEST_URI'],
        'message'   => 'Successfull',
        'data'      => [
            'title'     => $file->title,
            'filename'  => $file->filename,
            'thumb'     => $file->thumb,
        ]
    ]);
    return;

} catch(Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');

    echo json_encode([
        'type' => $_SERVER['REQUEST_METHOD'],
        'path' => $_SERVER['REQUEST_URI'],
        'message' => $e->getMessage(),
        'data' => [
            'exception_type' => get_class($e),
            'exception_code' => $e->getCode(),
            'exception_file' => $e->getFile(),
            'exception_line' => $e->getLine(),
        ],
    ]);
    return;
}
