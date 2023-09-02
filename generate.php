<html>
<head> <title> Start </title> </head>
<body> 
<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pre_url="https://files.rcsb.org/download/";
$post_url=".pdb";
$target_dir = "input/";
$target_file = basename($_FILES["protfile"]["name"]);


if ($target_file!="") {
	
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
} else {
	
	$prot_id=$_POST["pdbID"];
	if (strlen($prot_id)!=4) {
		echo "Sorry, Invalid ID entered";
	} else {
		$outvar = "output_" . $prot_id . ".scad";
		$_SESSION["outfile"]=$outvar;
		$url=$pre_url.$prot_id.$post_url;
		$ch=curl_init($url);
		$file_name=basename($url);
		$save_file_loc=$target_dir.$file_name;
		$fp=fopen($save_file_loc, "wb");
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		if (!curl_errno($ch)) {
			$res_code=curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
			if ($res_code!=200) {
				echo 'Code: ', $res_code, ', Unable to download file. (', $url, ")", "\n";
				curl_close($ch);
				fclose($fp);
			} else {
			curl_close($ch);

			fclose($fp);
			
			$command = escapeshellcmd('/usr/bin/python3 bin/protapp.py ' . $prot_id.$post_url);
			$output = shell_exec($command);
			header('Location: output_page.html');
			}
		}	else {
			echo "Unable to get file", "\n";
			curl_close($ch);
			fclose($fp);
		}
	}
}


?>
</body>
</html>