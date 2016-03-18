<?php

if(isset($_POST['asd']) ){
// echo 'test';
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "chat";
	$roomCode = sha1(time().$_POST['sd']);
	$roomCode = substr($roomCode,1,6);

	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
		echo "Connection failure!";
	    die();
	} 
	$sqlSelect = "SELECT * FROM room WHERE chat_room_name='".$_POST['sd']."'";
	$resultSelect = $conn->query($sqlSelect);
	if ($resultSelect->num_rows == 0) {	

		$sql = "INSERT INTO room(chat_room_user_id,chat_room_name,chat_room_code) VALUES('".$_POST['asd']."','".$_POST['sd']."','"
			.$roomCode."')";
		$result = $conn->query($sql);
	 	
	 	if($result){echo "<a href='#' onclick=\"switchRoom('".$_POST['sd']."','".$_POST['sd']."')\">".$_POST['sd']."</a>"; }
 	}

}
?>