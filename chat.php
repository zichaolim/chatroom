<?php session_start(); $_SESSION["role"] = 0; ?>
<html>
<head>
	<title>P2P Smart Chat System</title>
	<style>
		#chat{
			height:500px;
			overflow: scroll;
			overflow: auto;
		}
		#contentWrap{
			display: none;			
		}
		#chatWrap{
			float: left;			
			border: 1px #000 solid;
		}
		.error{
			color: red;
		}
		.whisper{
			color: gray;
			font-style: italic;
		}
	</style>	
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="https://cdn.socket.io/socket.io-1.4.5.js"></script>
	<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
    <script src="jsencrypt.js"></script>
</head>
<body>
<div class="container">
<p><b><h3>P2P Super Smart Chat System</h3></b></p>
<div id='firstStep'>
	<p id="nickError"><?php if(isset($_GET['err']) ){ echo '<div class="alert alert-danger"> Warning: You are not authorized to login until admin approves!</div>'; } ?></p>
	<a class="btn btn-info" id="btnGuest" href="javascript:guest();">Guest</a> 
	<a style="display:inline-block;text-decoration: none;padding:6px 12px;background-color: #fff; border: 1px solid grey;border-radius: 4px;" id="btnAdmin" href="javascript:admin();">Admin Login</a>
   <br><br>

    <p>Enter a nickname:<br><input size="100" id="txtNickname" name="txtNickname"></input>
	  <div id='admin' style='display:none'>		
		Enter password:<br><input type="password" size="100" id="txtPassword" name="txtPassword" placeholder="Password should be more than 6 characters"></p>
	  </div>
	  <div id='guest'>
		<p>Enter room code:<br>		
			<input size="100" id="roomcode" placeholder="You need a room code to join conversation room"></input><br><br> 
			Passcode (optional) :<br><input size="100" id="passcode" placeholder="It's like a secret cipher between moderator and you"></input>
			</p>
	  </div>
	  <input type="submit" name="btnSubmit" id="btnSubmit" value="Connect"></input>
</div>
	<script>
var socket = io.connect( 'http://'+window.location.hostname+':8080' );
	function admin(){
		document.getElementById("guest").style.display='none';
		document.getElementById("admin").style.display='block';
		document.getElementById("btnSubmit").value='Login';
		document.getElementById("btnAdmin").style='';
		document.getElementById("btnAdmin").className='btn btn-info';
		document.getElementById("btnGuest").style='display:inline-block;color: #337ab7;text-decoration: none;padding:6px 12px;background-color: #fff; border: 1px solid grey;border-radius: 4px;';		
	}

	function guest(){
		document.getElementById("guest").style.display='block';
		document.getElementById("admin").style.display='none';
		document.getElementById("btnGuest").style='';
		document.getElementById("btnSubmit").value='Connect';
		document.getElementById("btnGuest").class='btn btn-info';
		document.getElementById("btnAdmin").style='display:inline-block;color: #337ab7;text-decoration: none;padding:6px 12px;background-color: #fff; border: 1px solid grey;border-radius: 4px;';			
	}
		
 $("#btnSubmit").click(function(){
 	
	var nick = $("#txtNickname").val();
	var pwd = $("#txtPassword").val();
	var rc = $("#roomcode").val();
	var m = $("#passcode").val();

	$.ajax({
	  url: 'checkUser.php',
	  type: 'post',
	  data: {
	  	'name': nick,
	    'pwd': window.btoa(pwd),
	    'rc': rc,
	    'm':m
	},
      success: function(data, status) {
      	var guest ='';
      	var findGuest = data.split("/");
      	if(findGuest.length>1){
	      	guest = findGuest[0];
	      	data = findGuest[1];
      	}

      	if(data =='no') {
      		document.getElementById("nickError").innerHTML = '<div class="alert alert-danger"> Warning: Login Failed </div>';
      		localStorage.setItem("idasdd", '0');
      		localStorage.setItem("rc", '0');	     
      		return;
      	}

      	if(data =='duplicate user'){
      		document.getElementById("nickError").innerHTML = '<div class="alert alert-danger">Someone already has that username. Try another?</div>'
      		return;
      	}

      	if(guest =='new'){
      		socket.emit('update guest', data);
      	}	

      	if (typeof(Storage) !== "undefined") {	    
      		localStorage.setItem("idasdd", data);
      		localStorage.setItem("rc", rc);
      		    
		} else {
		    alert("Your device is not supported..please contact admin");
		    return;
		}
      	if(data != 'no' || data !='duplicate user') window.location.replace("index.php");         
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log("Details: " + desc + "\nError:" + err);
      }
    }); // end ajax call
  });
	</script>
	</div>
</body>
</html>