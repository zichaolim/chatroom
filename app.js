var io=require('socket.io'), mysql=require('mysql');
var express = require('express'),
	app = express(),
	server = require('http').createServer(app),
	io = require('socket.io').listen(server);

var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : '',
  database	:'chat'
});


var CryptoJS = require("crypto-js");
 
connection.connect();
server.listen(8080);

// routing
// app.get('/', function (req, res) {
//   res.sendfile(__dirname + '/index.html');
// });

// usernames which are currently connected to the chat
var usernames = {};

console.log('start');

io.sockets.on('connection', function(socket) {
var rooms = [];
// // rooms which are currently available in chat
// 	socket.on('sendClientID', function (data) {
		// var sql="SELECT * FROM room WHERE chat_room_code='"+data.roomCode+"' AND STATUS=1";
	// when the client emits 'adduser', this listens and executes

	socket.on('update guest', function(data) {
		var sql = "SELECT a.chat_room_name as rn,b.id as idd, b.chat_passcode as pc FROM room a, guest b WHERE a.chat_room_code= b.chat_room_code AND chat_nickname='"+data+"'";
		console.log(sql);
		connection.query(sql, function(err, rows, fields){

		if(rows.length>0){
			console.log(rows);
			io.sockets.emit('updateguest', '<tr id="hey" class="hey"><td>'+data+'</td><td>'+rows[0].rn+'</td><td>'+rows[0].pc+'</td><td><input type="checkbox" class="approve" onchange="ajaxApproveGuest('+rows[0].idd+');" id="'+rows[0].idd+'" /></td></tr>');
		}

		});
		
		// $('#manageGuest tbody tr:first').after('<tr><td>test1</td><td>test2</td><td>test3</td></tr>');		
	});

	socket.on('get message', function(callback) {
console.log('this is message');
		var sqlSelectMessage = "SELECT chat_message,chat_room_name,chat_user_id,date_created FROM message ORDER BY date_created DESC"; 
		connection.query(sqlSelectMessage, function(err, rows, fields){
		var cntRowMessage = 0;var strMessage = '';
		while(cntRowMessage < rows.length){	
			var decrypted = CryptoJS.AES.decrypt(rows[cntRowMessage].chat_message.toString(), "secret key 123");
			var plaintext = decrypted.toString(CryptoJS.enc.Utf8);

			if(rows[cntRowMessage].chat_user_id == '1'){ 
				strMessage += "<tr><td>Me</td><td>"+rows[cntRowMessage].chat_room_name+"</td><td>"+plaintext+"</td><td>"+rows[cntRowMessage].date_created+"</td></tr>";
			} else{
				strMessage += "<tr><td>"+rows[cntRowMessage].chat_user_id+"</td><td>"+rows[cntRowMessage].chat_room_name+"</td><td>"+plaintext+"</td><td>"+rows[cntRowMessage].date_created+"</td></tr>";
			}	
			cntRowMessage+=1;	 
		}
		console.log(strMessage);
		callback(strMessage);
		});

	});

	socket.on('adduser', function(username,roomCode){	
		var sql="SELECT a.chat_room_name as rn,b.chat_username as un FROM room a, user b WHERE a.chat_room_user_id='"+username+"' AND a.chat_room_user_id = b.id UNION SELECT d.chat_room_name as rn, c.chat_nickname as un FROM guest c,room d WHERE d.chat_room_code='"+ roomCode +"' AND c.chat_nickname='"+username+"' AND d.chat_room_code= c.chat_room_code AND c.chat_status<>0";
		var cntRow = 0; 
		console.log(sql);
		connection.query(sql, function(err, rows, fields){				
			if (err) throw err;				
			var rowData='';
			var rooms = ['Public'];
		    while(cntRow < rows.length){
		    	
				//rowData += '<tr><td>'+rows[cntRow].currency+'</td><td>'+rows[cntRow].we_buy+'</td><td>'+rows[cntRow].we_sell+'</td><td style="width:500px;text-align: center;">'+rows[cntRow].date_updated+'</td></tr>';
				if(rooms.indexOf(rows[cntRow].rn) == -1){
					rooms.push(rows[cntRow].rn);					
				}
				var usern = rows[cntRow].un;
		    	cntRow += 1;		    		    		
		    }
		    // store the username in the socket session for this client
			socket.username = usern;
			console.log(socket.username);
			// store the room name in the socket session for this client
			socket.room = 'Public';
			// add the client's username to the global list
			usernames[username] = usern;
			// send client to room 1
			socket.join('Public');
			// echo to client they've connected
			socket.emit('updatechat', 'System Admin', 'you have connected');
			// echo to room 1 that a person has connected to their room
			socket.broadcast.to('Public').emit('updatechat', 'System Admin', usern + ' has connected to this Public Room');
			socket.emit('updaterooms', rooms, 'Public');
			socket.roomlist = rooms;
			console.log(socket.roomlist);				  		 
		});				
	});
		
	// when the client emits 'sendchat', this listens and executes
	socket.on('sendchat', function (data) {
		
// Encrypt 
var ciphertext = CryptoJS.AES.encrypt(data.message, 'secret key 123');
var consulta="INSERT message(chat_message,chat_room_name,chat_user_id) VALUES('"+ciphertext+"','"+socket.room+"','"+data.idasdds+"')";
		
		connection.query(consulta, function(err, rows, fields){
			if (err){
				console.log("Error: " + err.message);
			}else{
				console.log('ok');
			}
		});
		// we tell the client to execute 'updatechat' with 2 parameters
		io.sockets.in(socket.room).emit('updatechat', socket.username, data.message);
	});
	
	socket.on('switchRoom', function(newroom,aroom){
		socket.leave(socket.room);
		socket.join(newroom);
		socket.emit('updatechat', 'System Admin', 'you have connected to '+ newroom);
		// sent message to OLD room
		socket.broadcast.to(socket.room).emit('updatechat', 'System Admin', socket.username+' has left this room');
		// update socket session room title
		socket.room = newroom;
		socket.broadcast.to(newroom).emit('updatechat', 'System Admin', socket.username+' has joined this room');
		// console.log(newroom + '/'+aroom+'/'+rooms);
		
		rooms=socket.roomlist;
		if(rooms.indexOf(newroom) == -1)rooms.push(newroom);

		var sql="SELECT a.chat_room_code as rc FROM room a, user b WHERE b.chat_username='"+socket.username+"' AND b.id=a.chat_room_user_id AND a.chat_room_name='"+newroom+"'";
		console.log(sql);
		connection.query(sql, function(err, rows, fields){				
			if (err) throw err;	
				if(rows.length>0){
					var rc = rows[0].rc;
					// console.log(rc);
					socket.emit('showRC',rc);
				}
		});
		socket.emit('updaterooms', rooms, newroom);
	});	

	// when the user disconnects.. perform this
	socket.on('disconnect', function(){
		// remove the username from global usernames list
		delete usernames[socket.username];
		// update list of users in chat, client-side
		io.sockets.emit('updateusers', usernames);
		// echo globally that this client has left
		if(socket.username ==null || socket.username ==''||socket.username ==undefined){			
		}else{
			socket.broadcast.emit('updatechat', 'System Admin', socket.username + ' has disconnected');
		}
		socket.leave(socket.room);
	});
});
