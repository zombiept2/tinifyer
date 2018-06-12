<?php
$data = new stdClass();
foreach ($_REQUEST as $key => $value)
{
    $data->$key = $value;
}
$file = ExtractDataValue($data, 'file');
$output = ExtractDataValue($data, 'output');
if ($file != '' && $output != '')
{
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"" . $output . "\""); 
    readfile($file); // do the double-download-dance (dirty but worky)
}
else
{
    echo 'Missing file!';
}
function ExtractDataValue($data, $name)
{
	$value = '';
	if (property_exists($data, $name))
	{
		$value = $data->$name;
	}
	return $value;
}