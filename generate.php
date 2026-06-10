<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pre_url = "https://files.rcsb.org/download/";
$post_url = ".pdb";
$target_dir = "input/";
$error = "";

$target_file = isset($_FILES["protfile"]) ? basename($_FILES["protfile"]["name"]) : "";

if ($target_file != "") {

	// Server-side validation: .pdb extension only, safe characters only
	if (strtolower(pathinfo($target_file, PATHINFO_EXTENSION)) != "pdb") {
		$error = "Sorry, only .pdb files are accepted.";
	} else {
		$target_file = preg_replace('/[^A-Za-z0-9._-]/', '_', $target_file);
		$outvar = "output_" . preg_replace('/\.pdb$/i', '', $target_file) . ".scad";
		$_SESSION["outfile"] = $outvar;
		if (move_uploaded_file($_FILES["protfile"]["tmp_name"], $target_dir . $target_file)) {
			$command = '/usr/bin/python3 bin/protapp.py ' . escapeshellarg($target_file);
			$output = shell_exec($command);
			header('Location: output_page.html');
			exit;
		} else {
			$error = "Sorry, there was an error uploading your file.";
		}
	}

} else {

	$prot_id = isset($_POST["pdbID"]) ? $_POST["pdbID"] : "";
	if (!preg_match('/^[A-Za-z0-9]{4}$/', $prot_id)) {
		$error = "Sorry, Invalid ID entered";
	} else {
		$outvar = "output_" . $prot_id . ".scad";
		$_SESSION["outfile"] = $outvar;
		$url = $pre_url . $prot_id . $post_url;
		$ch = curl_init($url);
		$file_name = basename($url);
		$save_file_loc = $target_dir . $file_name;
		$fp = fopen($save_file_loc, "wb");
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		if (!curl_errno($ch)) {
			$res_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
			curl_close($ch);
			fclose($fp);
			if ($res_code != 200) {
				unlink($save_file_loc);
				$error = "Code: " . $res_code . ", Unable to download file. (" . htmlspecialchars($url) . ")";
			} else {
				$command = '/usr/bin/python3 bin/protapp.py ' . escapeshellarg($file_name);
				$output = shell_exec($command);
				header('Location: output_page.html');
				exit;
			}
		} else {
			curl_close($ch);
			fclose($fp);
			unlink($save_file_loc);
			$error = "Unable to get file";
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-YQ6MQJ10VV"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-YQ6MQJ10VV');
</script>
<title> Start </title> </head>
<body>
<p><?php echo htmlspecialchars($error); ?></p>
<p><a href="index.html">Back to PDB2SCAD</a></p>
</body>
</html>
