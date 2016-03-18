<?php session_start(); //die($_SESSION["role"].'y'.$_SESSION["guest"]); 
if( isset($_SESSION["role"]) && $_SESSION["role"] ==0) {header("location:chat.php?err=login");}

//if ( isset($_SESSION["role"]) && $_SESSION["role"] != 1 ){header("location:chat.php?err=yes");}?>
<script src="https://cdn.socket.io/socket.io-1.4.5.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  <style>
 	.hey{ 		 	   
	    -webkit-animation: fadeinout 3s linear forwards;
	    animation: fadeinout 4s linear forwards;  		
	}

	@-webkit-keyframes fadeinout {
	  0% { opacity: 0; }
	  100% { opacity: 1; }
	}

	@keyframes fadeinout {
	  0% { opacity: 0; }
	  100% { opacity: 1; }
	}
  </style>
  <script>
	var socket = io.connect( 'http://'+window.location.hostname+':8080' );
	var height = 0;	
	// on load of page
	$(document).ready(function(){
		
		socket.emit('get message',function(data){
			$("#viewMessage > tbody").html("");

			if(data){								
				$("#viewMessage > tbody").append(data);								
			}
		});

		if (typeof(Storage) !== "undefined") {	    
	  		var idasdd = localStorage.getItem("idasdd");
	  		var rc = localStorage.getItem("rc");	  	
		} else {
		    alert("Your device is not supported..please contact admin");
		    return;
		}
		if(idasdd ==null || idasdd=='') window.location.replace("chat.html");
		
		$('#datasend').click( function() {
			var messages = $('#data').val();
			$('#data').val('');
			// tell server to execute 'sendchat' and send along one parameter
			socket.emit('sendchat', {message:messages,idasdds:idasdd});
			socket.emit('get message',function(data){
				$("#viewMessage > tbody").html("");

				if(data){								
					$("#viewMessage > tbody").append(data);								
				}
			});
		});

		$('#btnAddChat').click( function() {
			var txtRN = $('#txtRN').val();
			$.ajax({
				url: 'addRoom.php',
				type: 'post',
				data: {
				  	'asd': idasdd,
				  	'sd' :txtRN
				},
		      success: function(data) {
		      	$('#rooms').append('<div>' + data + '</div>');		      			      	
		      },
		      error: function(xhr, desc, err) {
		        console.log(xhr);
		        console.log("Details: " + desc + "\nError:" + err);
		      }
		    }); // end ajax call
		});

		$('.approve').each(function(index) {
			$(this).on("click", function(){
			// $(':checkbox:checked').each(function(i){
	  //       	alert($(this).val());
	  //       });
		        if( $(this).prop('checked')===true ){
		        	guestVal = 'a/'+ $(this).val();
		        	//alert($(this).val());
		        }else{
		        	guestVal = 'b/'+ $(this).val();
		        }
		       
		  
			// $("input[type=checkbox]:checked").each(function() {
	       		
	       		var token ='';
	       			
	   			$.ajax({
					url: 'approveGuest.php',
					type: 'post',
					data: {
					  	'asd': guestVal,
					  	'sd' : token
					},
			      success: function(data) {			      	
			      	$('#errMsg').html(data);
			      },
			      error: function(xhr, desc, err) {
			        $('#errMsg').html(desc);			        
			      }
		    	}); // end ajax call		    	
			});   		
		});

		// when the client hits ENTER on their keyboard
		$('#data').keypress(function(e) {
			if(e.which == 13) {
				$(this).blur();
				$('#datasend').focus().click();
				$('#data').focus();
			}
		});		
	});//end add chat

	
		// on connection to server, ask for user's name with an anonymous callback
	socket.on('connect', function(){

		var idasdd = localStorage.getItem("idasdd");
		var rc = localStorage.getItem("rc");			
		
		socket.emit('adduser', idasdd,rc);
	});

	socket.on('updatechat', function (username, data) {
		$('#conversation').append('<b>'+username + ':</b> ' + data + '<br>');
		height += 100;
		$('#conversation').animate({scrollTop: height});
	});

	socket.on('updateguest', function (data) {
		$('#manageGuest > thead tr:first').after(data);		
	});

	// listener, whenever the server emits 'updaterooms', this updates the room the client is in
	socket.on('updaterooms', function(rooms, current_room) {
		$('#rooms').empty();
		$.each(rooms, function(key, value) {
			if(value == current_room){
				$('#rooms').append('<div>' + value + '</div>');
			}
			else {
				$('#rooms').append('<div><a href="#" onclick="switchRoom(\''+value+'\')">' + value + '</a></div>');
			}
		});
	});

	function deletechat(){
		var token ='';
		$.ajax({
			url: 'approveGuest.php',
			type: 'post',
			data: {
			  	'asdd': 'g',
			  	'sd' : token
			},
	      success: function(data) {			      	
	      	$('#errMsg').html(data);
			$("#viewMessage > tbody").html("");
	      },
	      error: function(xhr, desc, err) {
	        $('#errMsg').html(desc);			        
	      }
    	}); // end ajax call		    	
	}

	function deleteGuest(){
		var token ='';
		$.ajax({
			url: 'approveGuest.php',
			type: 'post',
			data: {
			  	'asdd': 'u',
			  	'sd' : token
			},
	      success: function(data) {			      	
	      	$('#errMsg').html(data);
	      	$("#manageGuest > tbody").html("");	      	
	      },
	      error: function(xhr, desc, err) {
	        $('#errMsg').html(desc);			        
	      }
    	}); // end ajax call		
	}		

	function ajaxApproveGuest(element){			
			
		if(document.getElementById(element).checked==true){				        
	        guestVal = 'a/'+ element;
		}else{
			guestVal = 'b/'+ element;
		}		

   		var token ='';
		$.ajax({
			url: 'approveGuest.php',
			type: 'post',
			data: {
			  	'asd': guestVal,
			  	'sd' : token
			},
	      success: function(data) {			      	
	      	$('#errMsg').html(data);
	      },
	      error: function(xhr, desc, err) {
	        $('#errMsg').html(desc);			        
	      }
    	}); // end ajax call
	}

	function switchRoom(room,updateroom){
		socket.emit('switchRoom', room,updateroom);
		socket.on('showRC', function (rc) {
			if(rc=='a'||rc==null){$('#errMsg').html('This is a public room');return;}
			$("#errMsg").css({"background-color":"#f0ffff","font-size":"large","height":"30px","width":"500px"});
			$('#errMsg').html('The code for room '+room+' is <b>'+rc+'<br>');
		}); 	
	}

	function toggleGuest(){
		//document.getElementById('//')
	}

	function Logout(){		
		window.location.replace("chat.php")
	}	
</script>
<div class="container" >
<div><p id='errMsg'></p></div>
<blockquote><b><h5 style="color:#191970">P2P Super  Smart Chat System</h5></b> 
<?php if( isset($_SESSION["role"]) && $_SESSION["role"] == 1) {?> 
<div id='createChatRoom'>New Chat Room: <input type="text" id="txtRN" /><input type="submit" id="btnAddChat" value="Add" /> <a class="btn btn-info" href="javascript:Logout();">
<span class="glyphicon glyphicon-log-out"></span> Logout</a> </div>
<?php } ?>
 </blockquote>

<div id='groupchat'>
<div style="float:left;width:100px;border-right:1px solid black;height:350px;padding:10px;overflow:scroll-y;">
	<b>ROOMS</b>
	<div id="rooms"></div>
</div>
<div style="float:left;width:60%;height:300px;overflow:scroll-y;padding:10px;">
	<div id="conversation" style="height:300px;width:100%;overflow: auto;"></div>
	<input type="text" id="data" style="width:200px;margin-top:10px;"></textarea>
	<input type="button" id="datasend" value="send" />
</div>
</div>
<div id='manageUser' style="margin-top: 10cm;">
<?php if( isset($_SESSION["role"]) && $_SESSION["role"] == 1) { ?>
	 <blockquote><b><h5 style="color:#191970">Manage All My Guests</h5></b>
	 <a class="btn btn-info" href="javascript:deleteGuest();"><span class="glyphicon glyphicon-minus"></span> Delete All Guests</a>
	 </blockquote>
		<div id="approval"><?php 
	
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "chat";

	$conn = new mysqli($servername, $username, $password, $dbname);
	
	if ($conn->connect_error) {
		echo "Connection failure!";
	    die();
	}

	$sqlSelect = "SELECT b.chat_room_name as rn, a.chat_nickname as nn,a.chat_status as cs,a.id as i, a.chat_passcode as pc FROM guest a, room b WHERE 
		a.chat_room_code = b.chat_room_code AND b.chat_room_user_id ='".$_SESSION["ownerId"]."' 
		ORDER BY a.chat_status DESC";
	// $sqlSelectMessage = "SELECT chat_message,chat_room_name,chat_user_id,date_created FROM message ORDER BY date_created DESC"; 
		//echo $sqlSelect;
	$resultSelect = $conn->query($sqlSelect);
	// $resultSelectMessage = $conn->query($sqlSelectMessage);

	echo "<table id='manageGuest' class='table'><thead><tr><th>Guests</th><th>Room Name</th><th>Secret Cipher</th><th>Approved</th></tr></thead><tbody>";
	while($row = $resultSelect->fetch_assoc()) {
 		echo "<tr><td>".$row['nn']."</td><td>".$row['rn']."</td><td>".$row['pc']."</td><td>";
 		if($row['cs']==0){
 			//echo "<button type='button'  class='btn btn-primary'>block</button><button type='button' name='".$row['rn']."' class='btn btn-default'>Approve</button>";
 			echo "<input type='checkbox' class='approve' value='".$row['i']."' />";
 		}else{
 			echo "<input type='checkbox' class='approve' value='".$row['i']."' checked/>";
 		} 		
	}
	echo "</td></tbody></table>";
	echo "<blockquote><b><h5 style='color:#191970'>View All Messages</h5></b>
	<a class='btn btn-info' href='javascript:deletechat();'><span class='glyphicon glyphicon-minus'></span> Delete All Chat History</a>
	</blockquote>";
	echo "<table id='viewMessage' class='table'><thead><tr><th>Name</th><th>Room</th><th>Message</th><th>Time</th></tr></thead><tbody id='tbodyid'></tbody></table>";
	
	?></div>	
	
<?php } ?>
</div>
</div>