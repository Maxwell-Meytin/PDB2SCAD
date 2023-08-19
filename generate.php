<html>
<head> <title> Start </title> </head>
<body> Generating
<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$target_dir = "input/";
$target_file = basename($_FILES["protfile"]["name"]);
$outvar = "output_" . rtrim($target_file, ".pdb") . ".scad";
$_SESSION["outfile"]=$outvar;
if (move_uploaded_file($_FILES["protfile"]["tmp_name"], $target_dir . $target_file)) {
    //echo "The file ". htmlspecialchars( $target_file). " has been uploaded.";
    $command = escapeshellcmd('/usr/bin/python3 bin/protapp.py ' . $target_file);
    $output = shell_exec($command);
    //echo $output;
    header('Location: output_page.html');

  } else {
    echo "Sorry, there was an error uploading your file.";
}



?>
</body>
</html>