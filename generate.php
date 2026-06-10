<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

function fail($msg, $code = 400) {
	http_response_code($code);
	header('Content-Type: application/json');
	echo json_encode(array("error" => $msg));
	exit;
}

$pre_url = "https://files.rcsb.org/download/";
$target_dir = "input/";
$output_dir = "output/";

$has_upload = isset($_FILES["protfile"])
	&& $_FILES["protfile"]["error"] === UPLOAD_ERR_OK
	&& $_FILES["protfile"]["name"] !== "";

if ($has_upload) {

	$input_name = basename($_FILES["protfile"]["name"]);
	// Server-side validation: .pdb extension only, safe characters only
	if (strtolower(pathinfo($input_name, PATHINFO_EXTENSION)) != "pdb") {
		fail("Sorry, only .pdb files are accepted.");
	}
	$input_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $input_name);
	if (!move_uploaded_file($_FILES["protfile"]["tmp_name"], $target_dir . $input_name)) {
		fail("Sorry, there was an error uploading your file.", 500);
	}

} else {

	$prot_id = isset($_POST["pdbID"]) ? trim($_POST["pdbID"]) : "";
	if (!preg_match('/^[A-Za-z0-9]{4}$/', $prot_id)) {
		fail("Enter a 4-character PDB ID or choose a .pdb file.");
	}
	$input_name = $prot_id . ".pdb";
	$url = $pre_url . $input_name;
	$save_file_loc = $target_dir . $input_name;
	$ch = curl_init($url);
	$fp = fopen($save_file_loc, "wb");
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	$curl_failed = curl_errno($ch);
	$res_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	curl_close($ch);
	fclose($fp);
	if ($curl_failed || $res_code != 200) {
		@unlink($save_file_loc);
		fail("Unable to download " . $prot_id . " from RCSB (HTTP " . $res_code . ").", 502);
	}

}

$base = preg_replace('/\.pdb$/i', '', $input_name);
$out_name = "output_" . $base . ".scad";
$out_path = $output_dir . $out_name;

shell_exec('/usr/bin/python3 bin/protapp.py ' . escapeshellarg($input_name));

// Input is no longer needed once the converter has run
@unlink($target_dir . $input_name);

if (!file_exists($out_path) || filesize($out_path) === 0) {
	@unlink($out_path);
	fail("Conversion failed — the converter produced no output. Check that the file is a valid PDB structure.", 500);
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $out_name . '"');
header('Content-Length: ' . filesize($out_path));
readfile($out_path);

// Downloaded — free the space on the server
unlink($out_path);
exit;
