<?php

header('Access-Control-Allow-Origin: *');
header('Context-Type: application/json');

include_once '../../database.php';
include_once '../../models/file.php';
include_once '../../utils.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    $errorBag = [];
    $fillable = ['title', 'filename'];

    foreach($fillable as $f) {
        if(!array_key_exists($f, $_POST)) {
            $errorBag[] = "$f is required.";
        }
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

    // New file is uploaded
    if(file_exists(ROOT_DIR.$row['filename']) && isset($_FILES['thumb'])) {
        unlink(ROOT_DIR.$row['filename']);
    }
    // Get existing file ext from name
    $fext               = explode('.', $row['filename'])[1];
    $newFname           = $_POST['filename'].'.'.$fext;

    $file->title        = $_POST['title'];
    $file->thumb        = '/public/uploads/'.$newFname;
    $file->filename     = $newFname;

    // If file name has changes
    if(
        $row['filename'] !== $newFname 
        && file_exists(ROOT_DIR.$row['filename']) 
        && !isset($_FILES['thumb'])
    ) {
        rename(ROOT_DIR.$row['filename'], ROOT_DIR.$newFname);
    }

    if(isset($_FILES['thumb'])) {
        // Generate random name for unique filenaming
        // prevent from duplication
        $fext   = pathinfo($_FILES['thumb']['name'], PATHINFO_EXTENSION);
        $newFname       = $_POST['filename'].'.'.$fext;
        $file->filename = $newFname;
        $file->thumb    = '/public/uploads/'.$newFname;

        move_uploaded_file($_FILES['thumb']['tmp_name'], ROOT_DIR.$newFname);
    }

    $file->update($params['id']);

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
