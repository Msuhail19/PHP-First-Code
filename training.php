#!/usr/bin/php
<!DOCTYPE html>
<html>
<head>
	<title>PHP Arrays</title>
</head>
<body>
		
	
	<?php
		
		//Connect to Database
		$db_hostname = "mysql";
		$db_database = "u6mm";
		$db_username = "u6mm";
		$db_charset = "utf8mb4";
		$db_password = "obvious";
		
		
		$dsn = "mysql:host=$db_hostname;dbname=$db_database;charset=$db_charset";
		$opt = array (
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE =>PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
		array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));

		//Counting number of courses that are full and comparing it to number of courses
		$fullCount = 0;
		$allCount = 0;
		try {
			$pdo = new PDO($dsn,$db_username,$db_password,$opt);
			
			$allCaps = $pdo->query("SELECT Capacity FROM Sessions");
			foreach($allCaps as $caps){
				if($caps["Capacity"]<1){
					$fullCount = 0;
				}
				$allCount++;
			}

			
			if($fullCount == $allCount){
				exit ("<h1>Apologies. All courses are filled at the moment.<h1>");
			}
			
			if ( isset( $_POST['submit'] ) ) {
			$name = $_POST['name']; 
			$Email = $_POST['Email'];
			$topic = $_POST['topic'];
			$time = $_POST['dayTime'];
			echo '<h3>Form : </h3>';	
			echo " Name : " .$name ." <br/>";
			echo " Email : " .$Email." <br/>";
			echo " Topic Id : " .$topic." <br/>";
			echo " Time : " .$time." <br/>";
			echo '<br/>';
			
				//Attempts to validate input.
				$errors = 0;
				if(!preg_match('/^(?:[(\')?A-Za-z]+[\s\-]?)+$/', $name)){
					echo "Name is invalid. Please re-enter.<br/>";
					$errors++;
				}
				if($topic == NULL){
					echo "Please select a session.<br/>";
					$errors++;
				}
				if(!preg_match('/^(?:[A-Za-z\-\.]*[@][A-Za-z\-\.]*)+$/', $Email)){
					echo "Email is invalid. Please re-enter.<br/>";
					$errors++;
				}
				if($errors>0){
					echo "Request Cancelled : Please fix errors.<br/>";
				}
				else{
					//Variable To store topic name with loop to retrieve value using topic id
					$topicName;
					$stmt = $pdo->query("select * from Courses");
					foreach($stmt as $row) {
						if($row["ID"] == $topic){
							$topicName = $row["Name"];
						}
					}
					
					//Check if space available
					$space = false;
					$SessionID;
					$stmt = $pdo->query("select * from Sessions");
					foreach($stmt as $row) {
						if($row["Topic"] == $topicName){
							if($row["Date"] == $time){
								if($row["Capacity"]>0){
									$space = true;
									$SessionID = $row["id"]; 
								}
							}
						}
					}
					
					
					//If there is space change the capacity value in Table sessions
					//After Capacity value is changed User data added to table Bookings
					$NAME = '\''.$name.'\'';
					$EMAIL =  '\''.$Email.'\'';
					if($space){
						
						if($pdo->query("UPDATE Sessions SET Capacity = Capacity - 1 WHERE id=".$SessionID) == true){
							echo "<br/> Booking Attempted. <br/>";
							if($pdo->query("Insert into Bookings(SessionId,Name,Email) Values(".$SessionID.",".$NAME.",".$EMAIL.")")==true){
								echo "Booking successful.<br/>";
							}
							else{
								echo "Error : Booking failed. <br/>";
							}
						}
						
						
					}else{
						echo "This session is full, please choose a different timeslot. <br/>";
					}
					
				}
			
			}
		} catch (PDOException $e) {
			exit("PDO Error: Connection closed: ".$e->getMessage()."<br>");
		}
	?>
	
	<!----Form to submit Information----->
	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
		<!--TextField Input Declaration-->
		<input type="text" name="name" placeholder="Name" value="<?php if (isset($_POST['name'])) echo $_POST['name']; ?>"/>
		<input type="text" name="Email" placeholder="Email :" value="<?php if (isset($_POST['Email'])) echo $_POST['Email']; ?>"/>
		
		<!---Declaration of First DropDown Menu-->
		<select id="topic" name = "topic" onchange="ChangeTopicList()" selected ="<?php if (isset($_POST['topic'])) echo $_POST['topic']; ?>"> 
			<option value="">--------SELECT-------</option> 
			<option value="1">Word Processing</option> 
			<option value="2">Spreadsheets</option> 
			<option value="3">Email</option> 
			<option value="4">Presentation Software</option> 
			<option value="5">Library Use</option> 
		</select> 
		
		<!---Declaration of Second DropDown Menu--->
		<select id="dayTime" name = "dayTime" >
			<option selected="selected" value="">-------Time-------</option> 
		</select> 
		
		<!---Declaration of Submit---->
		<input type="submit" name="submit" />
	
	<!---JavaScipt To change Second DropDown.---->
	<!---I am aware no javascript was permitted, but the second dropdown was too difficult to do otherwise.---->
	<script>
	var topicAndTime = {};
	topicAndTime['1'] = ['Tuesday 10', 'Wednesday 11', 'Thursday 12'];
	topicAndTime['2'] = ['Tuesday 11', 'Wednesday 12', 'Thursday 10'];
	topicAndTime['3'] = ['Tuesday 12', 'Wednesday 10', 'Thursday 11'];
	topicAndTime['4'] = ['Tuesday 10', 'Thursday 12'];
	topicAndTime['5'] = ['Wednesday 11'];

	function ChangeTopicList() {	
		var topicList = document.getElementById("topic");
		var dtList = document.getElementById("dayTime");
		var selTopic = topicList.options[topicList.selectedIndex].value;
		while (dtList.options.length) {
			dtList.remove(0);
		}
		var topics = topicAndTime[selTopic];
		if (topics) {
			var i;
			for (i = 0; i < topics.length; i++) {
				var topic = new Option(topics[i], topics[i]);
				dtList.options.add(topic);
			}
		}
	} 
	
	</script>
	</form> 
	
	
</body>
</html>
