<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') :
	http_response_code(405);
	echo json_encode([
		'success' => 0,
		'message' => 'Invalid Request Method. HTTP method should be GET',
	]);
	exit;
endif;

require 'database.php';
$database = new Database();
$conn = $database->dbConnection();
$post_id = null;

if (isset($_GET['id'])) {
	$post_id = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
		'options' => [
			'default' => 'all_posts',
			'min_range' => 1
		]
	]);
}

try {

	$sql = is_numeric($post_id) ? "SELECT * FROM `students` WHERE id='$post_id'" : "SELECT * FROM `students`";

	$stmt = $conn->prepare($sql);

	$stmt->execute();

	if ($stmt->rowCount() > 0) :

		$data = null;

		if (is_numeric($post_id)) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
		} else {
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$student_name=$data['name'];
			$course_enrolled=$data['course_enrolled'];
			$leadership=$data['is_leader'];
			if($leadership=='1'){
				$leadership_role="Student Leader";
			}
			else{
				$leadership_role="Not a student Leader";
			}
			response($student_name,$course_enrolled,$leadership_role);
		}

		function response($student_name,$course_enrolled,$leadership): void
		{
			$response['Student_Name']=$student_name;
			$response['Course_Enrolled']=$course_enrolled;
			$response['Leadership_Status']=$leadership;

		//	$json_response=json_encode($response);
		//	echo($json_response);
			echo json_encode([
				'success' => 1,
				'data' => $response,
			]);
		}

	else :
		echo json_encode([
			'success' => 0,
			'message' => 'No Result Found!',
		]);
	endif;
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode([
		'success' => 0,
		'message' => $e->getMessage()
	]);
	exit;
}