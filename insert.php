<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] !== 'POST') :
	http_response_code(405);
	echo json_encode([
		'success' => 0,
		'message' => 'Invalid Request Method. HTTP method should be POST',
	]);
	exit;
endif;

require 'database.php';
$database = new database();
$conn = $database->dbConnection();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || !isset($data->course_enrolled) || !isset($data->is_leader)) :

	echo json_encode([
		'success' => 0,
		'message' => 'Please fill all the fields | name, course_enrolled, is_leader.',
	]);
	exit;

elseif (empty(trim($data->name)) || empty(trim($data->course_enrolled)) || empty(trim($data->is_leader))) :

	echo json_encode([
		'success' => 0,
		'message' => 'Oops! empty field detected. Please fill all the fields.',
	]);
	exit;

endif;

try {

	$name = htmlspecialchars(trim($data->name));
	$course_enrolled = htmlspecialchars(trim($data->course_enrolled));
	$is_leader = htmlspecialchars(trim($data->is_leader));

	if($is_leader =='1'){
				$leadership_role="Student leader";
			}
			else{
				$leadership_role="Not a student leader";
			}

	$query = "INSERT INTO `students`(name,course_enrolled,is_leader) VALUES(:name,:course_enrolled,:is_leader)";

	$stmt = $conn->prepare($query);

	$stmt->bindValue(':name', $name, PDO::PARAM_STR);
	$stmt->bindValue(':course_enrolled', $course_enrolled, PDO::PARAM_STR);
	$stmt->bindValue(':is_leader', $leadership_role, PDO::PARAM_STR);

	if ($stmt->execute()) {

		http_response_code(201);
		echo json_encode([
			'success' => 1,
			'message' => 'Data Inserted Successfully.'
		]);
		exit;
	}

	echo json_encode([
		'success' => 0,
		'message' => 'Data not Inserted.'
	]);
	exit;

} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode([
		'success' => 0,
		'message' => $e->getMessage()
	]);
	exit;
}