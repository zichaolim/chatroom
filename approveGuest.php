<?php
		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "chat";
		$conn = new mysqli($servername, $username, $password, $dbname);

		if ($conn->connect_error) {
			echo "Connection failure!";
	    	die();
		}

	if( isset($_POST['asd'])) {		

		$arrCheckVal = explode('/',$_POST['asd']);
//test
		$action = $arrCheckVal[0];
		$sdi = $arrCheckVal[1];

		$sqlSelectGuest = "SELECT chat_nickname FROM guest WHERE id=$sdi";
		$resultSelectGuest = $conn->query($sqlSelectGuest);
		
		while($row = $resultSelectGuest->fetch_assoc()) {
			$nick = $row['chat_nickname'];
		}

		if($action=='a'){ 
			$sqlAction = '1';
			$message ='<div class="alert alert-success" style="height:40px;padding:5px">
			<strong>Success! </strong>'.$nick.' has been approved successfully.</div>';  
		}else{ 
			$sqlAction = '0'; $message ='<div class="alert alert-danger" style="height:40px;padding:5px">
			 	 <strong>Success! </strong>'.$nick.' has been blocked successfully.</div>'; 
		}
		$sqlUpdate = "UPDATE guest SET chat_status=$sqlAction WHERE id=$sdi"; 
		$resultUpdate = $conn->query($sqlUpdate);

		if($resultUpdate) echo $message;
	}
	
	//delete all messages
		if( isset($_POST['asdd']) ){  

			if($_POST['asdd']=='g') {
				$sqlDelete = "DELETE FROM `message`";
				$resultDelete = $conn->query($sqlDelete);
				echo "<div class='alert alert-warning' style='height:40px;padding:5px'>All messages have been deleted!</div>";
			}
			if($_POST['asdd']=='u') {
				$sqlDelete = "DELETE FROM `guest`";
				$resultDelete = $conn->query($sqlDelete);
				echo "<div class='alert alert-warning' style='height:40px;padding:5px'>All guests have been deleted!</div>";
			}
		}
?>