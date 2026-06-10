<?php
session_start();
if (!isset($_SESSION["outfile"])) {
    echo "No conversion found for this session.";
    exit;
}
$file = "/var/www/html/pdb2scad/output/" . basename($_SESSION["outfile"]);

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    exit;
}else{
echo "file does not exist:" . $file;
}
?>