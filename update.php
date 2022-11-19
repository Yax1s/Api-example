<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, is_leaderization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') :
	http_response_code(405);
	echo json_encode([
		'success' => 0,
		'message' => 'Invalid Request Method. HTTP method should be PUT',
	]);
	exit;
endif;

require 'database.php';
$database = new Database();
$conn = $database->dbConnection();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
	echo json_encode(['success' => 0, 'message' => 'Please provide the post ID.']);
	exit;
}

try {

	$fetch_post = "SELECT * FROM `students` WHERE id=:post_id";
	$fetch_stmt = $conn->prepare($fetch_post);
	$fetch_stmt->bindValue(':post_id', $data->id, PDO::PARAM_INT);
	$fetch_stmt->execute();

	if ($fetch_stmt->rowCount() > 0) :

		$row = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
		$post_name = isset($data->name) ? $data->name : $row['name'];
		$post_course_enrolled = isset($data->course_enrolled) ? $data->course_enrolled : $row['course_enrolled'];
		$post_is_leader = isset($data->is_leader) ? $data->is_leader : $row['is_leader'];


		if($post_is_leader =='1'){
			$post_leadership_role = "Student leader";
		}
		else{
			$post_leadership_role = "Not a student leader";
		}

		$update_query = "UPDATE `students` SET name = :name, course_enrolled = :course_enrolled, is_leader = :is_leader 
        WHERE id = :id";

		$update_stmt = $conn->prepare($update_query);

		$update_stmt->bindValue(':name', htmlspecialchars(strip_tags($post_name)), PDO::PARAM_STR);
		$update_stmt->bindValue(':course_enrolled', htmlspecialchars(strip_tags($post_course_enrolled)), PDO::PARAM_STR);
		$update_stmt->bindValue(':is_leader', htmlspecialchars(strip_tags($post_leadership_role)), PDO::PARAM_STR);
		$update_stmt->bindValue(':id', $data->id, PDO::PARAM_INT);


		if ($update_stmt->execute()) {

			echo json_encode([
				'success' => 1,
				'message' => 'Post updated successfully'
			]);
			exit;
		}

		echo json_encode([
			'success' => 0,
			'message' => 'Post Not updated. Something is going wrong.'
		]);

	else :
		echo json_encode(['success' => 0, 'message' => 'Invalid ID. No students found by the ID.']);
	endif;
	exit;
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode([
		'success' => 0,
		'message' => $e->getMessage()
	]);
	exit;
}