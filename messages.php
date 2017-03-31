<?php
session_start();
if(isset($_POST['submit_login']))
{
	process_login_form();
}
elseif(isset($_POST['send_message']))
{
	process_message_page(array());
}

elseif(isset($_POST['inbox']))
{
	display_inbox(array());
}
elseif(isset($_POST['outbox']))
{
	display_outbox(array());
}
elseif(isset($_POST['logout']))
{
	logout();
}
elseif(isset($_SESSION['username']))
{
	display_message_page(array(),array());
}
else
{
	display_login_form(array(),array());
}
?>

<?php
function logout()
 {
	unset($_SESSION['username']);
	unset($_SESSION['password']);
	session_write_close();
	header("Location: messages.php");
 }
 ?>
 
 <?php
function display_login_form($error= array(), $missing_field=array())
{
	if($missing_field)
	{
		foreach($missing_field as $missing_fields)
		{
			echo "<br>".$missing_fields." is missing kindly fill it<br>"; 
		}
	}
	elseif($error)
	{
		foreach ($error as $error_message)
		 {
			echo $error_message."<br>";
		}
	}

	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<title>Login page</title>
<head>
 <meta charset="utf-8">
</head>
<body>
<h1>Login  form </h1><br>
<form action= "messages.php" method="POST">
<input type = "username" name = "login_username" placeholder="Username"><br>
<input type = "password" name = "login_password" placeholder="Password"><br>
<button type="submit" name="submit_login" value="submit_login"> Submit</button><br>
</form>
</body>
</html>

<?php
}
?>

<?php
function validate_login_form($error = array(), $missing_field= array())
{
	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		if(empty($_POST['login_username']))
		{
			$missing_field[] = 'login_username'; 
		}
		if(!isset($_POST['login_username']) or (!$_POST['login_username']))
		{
			$error[] = '<p>login username is missing</p>'; 
		}
		if(empty($_POST['login_password']))
		{
			$missing_field[] = 'login_password';
		}
		if(!isset($_POST['login_password']) or (!$_POST['login_password']))
		{
			$error[] = '<p>login password is missing</p>';
		}
		if($error or $missing_field)
		{
			display_login_form($error, $missing_field);
		}
		elseif(get_by_username($_POST['login_username']))
		{
			//header("Location:real_portal.php");
			$_SESSION['login_username'] = $_POST['login_username'];
			$_SESSION['login_password'] = $_POST['login_password'];
		}
		else
		{
			echo  "not returning data from database line 597<br>";
		}
	}

}
?>

<?php
function process_login_form()
{
	$error= array();
	$missing_field= array();
	validate_login_form($error,$missing_field);
	if(isset($_SESSION['login_username']) && isset($_SESSION['login_password']) && (get_by_username($_SESSION['login_username'])))
	{
		$_SESSION['username'] = $_SESSION['login_username'];
		$_SESSION['password'] = $_SESSION['login_password'];
		//echo "login successful";
		//header("real_portal.php");
		display_message_page(array(),array());
	}
}
?>

<?php
function connection()
{
	$dsn="mysql:host=localhost; dbname=db;charset=utf8";
	$username= "password";
	$password= "password";

	try{
		$conn =  new PDO($dsn, $username, $password);
		$conn->setAttribute( PDO::ATTR_PERSISTENT , true );
		$conn->setAttribute( PDO::ATTR_ERRMODE , PDO:: ERRMODE_EXCEPTION );	
	}
	catch(PDOException $e)
	{
		echo "<span style='color:red;'> *connection failed</span>" .$e->getMessage();
	}
	return $conn;
}
?>

<?php
function get_by_username($username)
	{
		$conn = connection();
		$sql = "SELECT * FROM prototype WHERE username = :username";
		try{
			$st= $conn->prepare( $sql);
			$st->bindValue(":username",$username,PDO::PARAM_STR);
			$st->execute();
			$user_data = $st->fetch();
			//parent::disconnect();
			$conn=  null;
			if($user_data)
			{
				return ($user_data);
			}
		}
		catch(PDOException $e){
			echo  "<br>line 163 Query failed<br>".$e->getMessage();
			header("Location:messages.php");
		}
	}
?>

<?php
function display_message_page($error= array())
{
	 if($error)
	{
		foreach($error as $error_message)
		{
			echo $error_message;
		}
	}
	


	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<title>message_page</title>
<head>
<meta charset="utf-8">
</head>
<body>
<h1>Message System</h1><br>
<h2><?php echo "<br>Hi ".$_SESSION['username']." welcome to message system<br>"; ?></h2>
<form action ="messages.php" method="POST">
TO: <input type="text" name="frnd_username" placeholder="Friend Username"> <br>
 <h4> <?php echo "From: ".$_SESSION['username']; ?> </h5>
<div name="chat"  style= "border:1px solid black; width:45%; height:330px; overflow: auto;"  >

<table style="border-color:grey;" >
<tr>
<th style="border-right: 1px solid black; padding-right: 150px; border-bottom: 1px solid black";>Messages</th>
<th style="border-right: 1px solid black; padding-right: 15px;border-bottom: 1px solid black";>Date</th>

<th style="border-right: 1px solid black; padding-right: 15px;border-bottom: 1px solid black";>To User</th>

<th style="border-right: 1px solid black; padding-right: 15px;border-bottom: 1px solid black";>From User</th>
<?php

			
				$user_data = message_fetch($_SESSION['username']);
				if($user_data)
				{

					foreach($user_data as $user)
					{
						$msg=  $user['msgs'];
						$date = $user['time'];
						$to_user = $user['to_user'];
						$frm_user = $user['frm_user'];
						if($user['frm_user'] = NULL)
						{
							$frm_user = "No";
						}
						if($user['to_user'] = NULL)
						{
							$to_user = "No";
						}
						echo "<tr><td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $msg ."</td>" ;
						echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $date."</td>";
						echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $to_user."</td>";
						echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $frm_user."</td></tr>";
					}
				}
				if(!$user_data)
				{
					echo "<tr><td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'> No messages </td>" ;
					echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'> No messages</td></tr>";
				}
			
		    unset($_POST['frnd_username']);
			unset($_POST['message']);
?>
</table>
</div>
<br>

<div name="message" width="20%" height="30%">
<label>Message:</label><br>
 <textarea name="message" rows="2" cols="30" placeholder="Message..." > </textarea>
</div> 
<button type="submit"  name="send_message" >Send Message</button><br><br>
<a href ="messages.php?action=inbox"><button type ="submit" name = "inbox" >Inbox </button></a>
<a href ="messages.php?action=outbox"><button type ="submit" name = "outbox" >Outbox </button></a>
<a href ="messages.php?action=logout"><button type ="submit" name = "logout" style="float:right; margin-right: 200px; padding: 3px;">Logout </button></a><br>
</form>
</body>
</html>

<?php
}
?>

<?php
function validate_message_page($error)
{
	$error = array();
	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		if(!isset($_POST['frnd_username']) or !$_POST['frnd_username'] or empty($_POST['frnd_username']))
		{
			$error[] = 'Friend username is missing';
		}
		if($_POST['frnd_username'] == $_SESSION['username'])
		{
			$error[] = 'Enter valid username to send message';
		}
		if(!isset($_POST['message']) or  !$_POST['message'] or empty($_POST['message']))
		{
			$error[] = 'Message field is missing';
		}
		if($error)
		{
			display_message_page($error);
		}
		elseif(!$error)
		{
			inbox_store($_SESSION['username'], $_POST['frnd_username'], $_POST['message']);
			outbox_store($_POST['frnd_username'], $_SESSION['username'], $_POST['message']);
			message_store($_SESSION['username']);
			header("Location: messages.php");
		}
	}
}
?>

<?php
function process_message_page($error= array())
{
	validate_message_page($error);
}
?>

<?php
function msg_inbox_fetch($username)
{
	$conn= connection();
	$sql = "SELECT * FROM inbox WHERE username = :username";
	try{
		$st= $conn->prepare($sql);
		$st->bindValue(":username", $username, PDO::PARAM_STR);
		$st->execute();
		$result= $st->fetch(PDO::FETCH_ASSOC);
		$conn=null;
		if($result)
		{
			return $result;
		}
	}
	catch(PDOException $e){
		echo "Query failed line 325 ".$e->getMessage();
	}
}
?>



<?php
function  message_store($username)
{
	if(inbox_fetch($username))
	{
		$row = inbox_fetch($username);
	
		foreach($row as $key => $value)
		{
			

					$frm_user = $value['frm_user'];
					$msg = $value['msg'];
					$inb_id = $value['in_id'];
					$user = $username;
					$conn= connection();

				    

						$sql = "INSERT INTO messages(msgs,username,frm_user,to_user,inb_id, out_id) VALUES( :msg, :user, :frm_user, NULL, :inb_id, NULL) ";
						try{
							$st= $conn->prepare($sql);
							$st->bindValue(':msg', $msg, PDO::PARAM_STR);
							$st->bindValue(':user', $user, PDO::PARAM_STR);
							$st->bindValue(':frm_user', $frm_user, PDO::PARAM_STR);
							$st->bindValue(':inb_id', $inb_id, PDO::PARAM_INT);
							$st->execute();
							$conn= NULL;
							
						}
						catch(PDOException $e){
							echo "line 453 Query failed ".$e->getMessage();
						}
			}
	}

	
	if(outbox_fetch($username))
	{
		$row = outbox_fetch($username);
		foreach($row as $key => $value)
		{
			
					$to_user = $value['to_user'];
					$msg = $value['msg'];
					$out_id = $value['out_id'];
					$user = $username;
					$conn= connection();

						$sql = "INSERT  INTO messages(msgs,username,frm_user,to_user,inb_id,out_id) VALUES( :msg, :user ,NULL, :to_user,NULL, :out_id) ";
						try{
							$st= $conn->prepare($sql);
							$st->bindValue(':msg', $msg, PDO::PARAM_STR);
							$st->bindValue(':user', $user, PDO::PARAM_STR);
							$st->bindValue(':to_user', $to_user, PDO::PARAM_STR);
							$st->bindValue(':out_id', $out_id, PDO::PARAM_INT);
							$st->execute();
							$conn= NULL;

						}
						catch(PDOException $e){
							echo "line 444 Query failed ".$e->getMessage();
						}
					
		}
	}
}


?>

<?php
function message_fetch($username)
{	
	$conn= connection();
	$sql = "SELECT * FROM messages WHERE username = :username ORDER BY time DESC";
	try{
		$st= $conn->prepare($sql);
		$st->bindValue(":username", $username, PDO::PARAM_STR);
		$st->execute();
		$user_data = $st->fetchAll();
		$conn= NULL;
		if($user_data)
		{
			return $user_data;
		}
	}
	catch(PDOException $e){
		echo  "line 353 Query failed ".$e->getMessage();
	}
}
?>

<?php
function inbox_store($frm_user, $username, $msg)
{
	$conn= connection();
	$sql = "INSERT INTO inbox(frm_user, username, msg) VALUES(:frm_user, :username, :msg)";
	try{
		$st= $conn->prepare( $sql );
		$st->bindValue(':frm_user', $frm_user, PDO::PARAM_STR);
		$st->bindValue(':username', $username, PDO::PARAM_STR);
		$st->bindValue(':msg', $msg, PDO::PARAM_STR);
		$st->execute();
		$conn= null;
	}
	catch(PDOException $e){
		echo "line 384 Query failed ".$e->getMessage();
	}
}

function inbox_fetch( $username)
{
	{
		$conn = connection();
		$sql = "SELECT * FROM inbox WHERE username = :username ORDER BY time DESC";
		try{
			$st = $conn->prepare( $sql );
			$st->bindValue(':username', $username, PDO::PARAM_STR);
			$st->execute();
			$inbox = $st->fetchAll();
			$conn= null;
			if($inbox)
			{
				return $inbox;
			}
		}
		catch(PDOException $e){
			echo "line 311 Query failed ".$e->getMessage();
		}
	}

}

?>

<?php
function inbox_fetch_process($username, $error=array())
{
	if(!inbox_fetch($username))
	{
		$error[] ='Your inbox is empty';
	}
	if($error)
	{
		display_inbox($error);
	}
	else
	{
		display_inbox($error);
	}
}
?>

<?php
function inbox_store_process($frm_user,$username,$msg, $error = array())
{
	if(!$isset($_POST['send_message']) && $_POST['message'])
	{
		$error[] = 'Enter the message first';
	}
	if($error)
	{
		display_message_page($error);
	}
	elseif(!$error)
	{
		inbox_store($frm_user, $username, $msg);
		display_message_page($error);
	}
	else
	{
		display_message_page($error);
	}
}
?>


<?php
function display_inbox($error= array())
{
	if($error)
	{
		echo $error;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<title>Inbox_page</title>
<head>
<meta charset="utf-8">
</head>
<body>
<h1>Inbox</h1><br>
<h2><?php echo "<br>Hi ".$_SESSION['username']." here's your INBOX<br>"; ?> </h2>

<div name="chat"  style= "border:1px solid black; width:45%; height:130px; overflow: auto;" >
<table style="border-color:grey;" >
<tr>
<th style="border-right: 1px solid black; padding-right: 150px; border-bottom: 1px solid black";>Messages</th>
<th style="border-right: 1px solid black; padding-right: 15px;border-bottom: 1px solid black";>Date</th>
<th style="border-right: 1px solid black; padding-right: 15px;border-bottom: 1px solid black";>From User</th>
</tr>
	<?php
			$user_data = inbox_fetch($_SESSION['username']);
				if($user_data)
				{

					foreach($user_data as $user)
					{
						$msg=  $user['msg'];
						$date = $user['time'];
						$frm_user = $user['frm_user'];
						
						echo "<tr><td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $msg ."</td>" ;
						echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $date."</td>";
						echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $frm_user."</td></tr>";
					}
				}
				if(!$user_data)
				{
					echo "<tr><td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'> No messages </td>" ;
					echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'> No messages</td></tr>";
				}

	?>
</table>
</div>
<a href="messages.php?action=back_inbox"<button type= "submit" >Back </button></a>
</body>
</html>

<?php
	}
?>


<?php
function outbox_store($to_user, $username, $msg)
{
	$conn= connection();
	$sql = "INSERT INTO outbox(to_user, username, msg) VALUES(:to_user, :username, :msg)";
	try{
		$st= $conn->prepare( $sql );
		$st->bindValue(':to_user', $to_user, PDO::PARAM_STR);
		$st->bindValue(':username', $username, PDO::PARAM_STR);
		$st->bindValue(':msg', $msg, PDO::PARAM_STR);
		$st->execute();
		$conn= null;
	}
	catch(PDOException $e){
		echo "line 521 Query failed ".$e->getMessage();
	}
}

function outbox_fetch($username)
{
	//if(outbox_store($to_user, $username, $msg))
	{
		$conn = connection();
		$sql = "SELECT * FROM outbox WHERE username = :username ORDER BY time DESC";
		try{
			$st = $conn->prepare($sql);
			$st->bindValue(':username',$username, PDO::PARAM_STR);
			$st->execute();
			$outbox = $st->fetchAll();
			$conn= null;
			if($outbox)
			{
				return $outbox;
			}
		}
		catch(PDOException $e){
			echo "line 543  Query failed ".$e->getMessage();
		}
	}
}

?>

<?php
function outbox_fetch_process($username, $error=array())
{
	if(!outbox_fetch($username))
	{
		$error[] ='Your outbox is empty';
	}
	if($error)
	{
		display_outbox($error);
	}
	else
	{
		display_outbox($error);
	}
}
?>

<?php
function outbox_store_process($frm_user,$username,$msg, $error = array())
{
	if(!$isset($_POST['send_message']) && $_POST['message'])
	{
		$error[] = 'Enter the message first';
	}
	if($error)
	{
		display_message_page($error);
	}
	elseif(!$error)
	{
		outbox_store($frm_user, $username, $msg);
		display_message_page($error);
	}
}
?>

<?php
function display_outbox($error = array())
{
	if($error)
	{
		echo $error;
	}
	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<title>Outbox_page</title>
<head>
<meta charset="utf-8">
</head>
<body>
<h1>Outbox</h1><br>
<h2><?php echo "<br>Hi ".$_SESSION['username']." here's your OUTBOX<br>"; ?></h2>

<div name="chat"  style= "border:1px solid black; width:45%; height:130px; overflow: auto;"  >
<table style="border-color:grey;" >
<tr>
<th style="border-right: 1px solid black; padding-right: 150px; border-bottom: 1px solid black";>Messages</th>
<th style="border-right: 1px solid black; padding-right: 15px;border-bottom: 1px solid black";>Date</th>
<th style="border-right: 1px solid black; padding-right: 15px;border-bottom: 1px solid black";>To User</th>
</tr>

	<?php
			$user_data = outbox_fetch($_SESSION['username']);
				if($user_data)
				{

					foreach($user_data as $user)
					{
						$msg=  $user['msg'];
						$date = $user['time'];
						$to_user = $user['to_user'];
						
						echo "<tr><td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $msg ."</td>" ;
						echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $date."</td>";
						echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'>". $to_user."</td></tr>";
					}
				}
				if(!$user_data)
				{
					echo "<tr><td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'> No messages </td>" ;
					echo "<td style='border-right: 1px solid black; padding-right: 15px; border-bottom: 1px solid black;'> No messages</td></tr>";
				}

	?>
</table>
</div>
<a href="messages.php?action=back_outbox"<button type= "submit" >Back </button>
</body>
</html> 
<?php
}

?>

