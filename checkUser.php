<?php
	session_start(); 	
	$_SESSION["role"] = 0;	
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "chat";

	$conn = new mysqli($servername, $username, $password, $dbname);

	if ($conn->connect_error) {
		echo "Server down! Please contact system administrator.";
	    die();
	} 

	//delete all room exceed 1 day
	$sqlDeleteRoom = "DELETE FROM room WHERE DATE_ADD(`date_created`, INTERVAL 1 DAY)<NOW() AND `chat_room_name`<>'Public'";
	$resultDeleteRoom = $conn->query($sqlDeleteRoom);

if(isset($_POST['name']) && isset($_POST['pwd']) && isset($_POST['rc']) && $_POST['rc'] ==''  ){
	
	$pwd = hash("sha256",base64_decode($_POST['pwd']));
	$sql = "SELECT * FROM user WHERE chat_username='".$_POST['name']."' AND chat_password='".$pwd."'";
	$result = $conn->query($sql);
	$cnt = 0;
	if ($result->num_rows > 0) {		
		while($row = $result->fetch_assoc()) { 
			echo $row['id'];
			$_SESSION["ownerId"] = $row['id'];
		}
		$_SESSION["role"] = 1;		
	} else {   
	    echo "no";
	    $_SESSION["role"] = 0;
	}
}

if( isset($_POST['rc']) && $_POST['rc'] != '' && isset($_POST['name']) ){

	$strRC = $_POST['rc'];
	$passcode = isset($_POST['m'])?" AND chat_passcode ='".$_POST['m']."'":'';

	$sqlSelectGuest = "SELECT * FROM guest WHERE chat_nickname='".$_POST['name']."' AND chat_room_code='".$strRC."' AND chat_status<>0;";
	$sqlVerifyGuest = "SELECT * FROM guest WHERE chat_nickname='".$_POST['name']."' AND chat_room_code='".$strRC."'".$passcode;
	$sqlSelectName = "SELECT * FROM guest WHERE chat_nickname='".$_POST['name']."' AND chat_room_code='".$strRC."'";
	$resultSelectGuest = $conn->query($sqlSelectGuest);
	$resultVerifyGuest = $conn->query($sqlVerifyGuest);
	$resultSelectName  = $conn->query($sqlSelectName);
	
	if ($resultVerifyGuest->num_rows == 0) {
		if($resultSelectName->num_rows==0){	
		    $sqlInsert = "INSERT INTO guest(chat_nickname,chat_room_code,chat_passcode) 
			VALUES('".$_POST['name']."','".$_POST['rc']."','".$_POST['m']."')";
			$result = $conn->query($sqlInsert);	
			$_SESSION["role"] = 0;
			echo "new/".$_POST['name'];
		}else{
			echo "duplicate user";
		}
	}else{
		// check guest authorization	
		if($resultSelectGuest->num_rows == 0){
			$_SESSION["role"] = 0;
		}else{
			$_SESSION["role"] = 2;
		}		
		echo $_POST['name'];		
	}
	
}
?>