<?php
require_once("config.php");
require_once("lib/Tinify/Exception.php");
require_once("lib/Tinify/ResultMeta.php");
require_once("lib/Tinify/Result.php");
require_once("lib/Tinify/Source.php");
require_once("lib/Tinify/Client.php");
require_once("lib/Tinify.php");
\Tinify\setKey(TINYPNG_KEY);
$expiration_seconds = (60 * 60 * 24 * 1); // 1 day
//$expiration_seconds = (5); // 1 min
remove_files_from_dir_older_than_x_seconds(dirname(__file__).'/tmp/', $expiration_seconds); // 1 day
$status = 200;
$message = '';
$original_filesize = '';
$tiny_filesize = '';
$original_filesize_readable = '';
$tiny_filesize_readable = '';
$filesize_reduced_percentage = '';
$original_filename = '';
$filename = '';
$filepath = '';
$compression_count = 1;
if (isset($_FILES['upload_file'])) 
{
    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], "tmp/" . $_FILES['upload_file']['name']))
    {
        $source = \Tinify\fromFile("tmp/" . $_FILES['upload_file']['name']);
        $source->toFile("tmp/tiny_" . $_FILES['upload_file']['name']);
        $original_filename = $_FILES['upload_file']['name'];
        $filename = "tiny_" . $_FILES['upload_file']['name'];
        $filepath = "tmp/tiny_" . $_FILES['upload_file']['name'];
        $status = 200;
        $message = "Image optimized!";
        $compression_count = \Tinify\compressionCount();
        $original_filesize = filesize("tmp/" . $_FILES['upload_file']['name']);
        $original_filesize_readable = ReadableFilesize($original_filesize);
        $tiny_filesize = filesize($filepath);
        $tiny_filesize_readable = ReadableFilesize($tiny_filesize);
        $filesize_reduced_percentage = round(($tiny_filesize / $original_filesize) * 100);
        $filesize_reduced_savings_percentage = 100 - $filesize_reduced_percentage;
    }
} 
else 
{
    $status = 403;
    $message = "No images uploaded...";
}
$output = array(
    'message' => $message,
    'filename' => $filename,
    'filepath' => $filepath,
    'original_filename' => $original_filename,
    'compression_count' => $compression_count,
    'compression_max' => 500,
    'original_filesize' => $original_filesize,
    'original_filesize_readable' => $original_filesize_readable,
    'tiny_filesize' => $tiny_filesize,
    'tiny_filesize_readable' => $tiny_filesize_readable,
    'filesize_reduced_percentage' => $filesize_reduced_percentage,
    'filesize_reduced_savings_percentage' => $filesize_reduced_savings_percentage
);
echo _response($output, $status);
exit;
function _response($data, $status = 200) 
{
    header("HTTP/1.1 " . $status . " " . _requestStatus($status));
    return json_encode($data);
}
function _requestStatus($code) 
{
    $status = array(  
        200 => 'OK',
        403 => 'Invalid',
        404 => 'Not Found',   
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
    ); 
    return ($status[$code])?$status[$code]:$status[500]; 
}
function remove_files_from_dir_older_than_x_seconds($dir,$seconds = 3600) {
    $files = glob(rtrim($dir, '/')."/*");
    $now   = time();
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= $seconds) {
                //echo "removed $file<br>".PHP_EOL;
                unlink($file);
            }
        } else {
            remove_files_from_dir_older_than_x_seconds($file,$seconds);
        }
    }
}
function ReadableFilesize($bytes, $decimals = 2) 
{
    $size = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}