<?php
session_start();
if(isset($_POST['login']))
{
	display_login_form(array(),array());
}
elseif(isset($_POST['submit_login']))
{
	process_login_form();
}
elseif(isset($_POST['register']))
 {
	display_register_form(array(),array());
}
elseif(isset($_POST['submit_register']))
	{
		process_register();
	}
elseif(isset($_POST['reset_register']))
{

	display_register_form(array(),array());
}
elseif (isset($_GET["action"]) && ($_GET["action"]=="change_password"))
 {
	display_change_password_form(array() ,array());
}
elseif(isset($_POST['submit_change_password']))
{
	submit_change_password();
}
elseif(isset($_GET["action"]) && $_GET["action"] == "logout")
{
	logout();
}


elseif(isset($_SESSION['username'])  &&  isset($_SESSION['email']) &&  (isset($_SESSION['password'])  or isset($_SESSION['new_password'])))
{
	welcome_page();
}
elseif(isset($_SESSION['login_username']) && isset($_SESSION['login_password']))
{
	welcome_page();
}


else
{
	display_portal();
}
?>


<?php
function display_portal()
{
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<title>General Portal</title>
<head>
<meta charset="utf-8">
</head>
<body>
<h1>General Portal </h1><br>
<form action ="real_portal.php" method="POST">
<button type="submit" name="login" >login</button><br>
<button type ="submit" name ="register">Register</button><br>
</form>
</body>
</html>

<?php
}
?>

<?php
function display_register_form($error=array() , $missing_field=array())
{
	if($missing_field)
			{
				foreach($missing_field as $missing_fields)
				{
					echo "<br>".$missing_fields." is missing kindly fill it.<br>";
				}
			}

	elseif($error)
			{
				foreach($error as $error_message)
				{
					echo "<br>".$error_message."<br>";
				}
			}
	unset($error);
	unset($missing_field);
	
	?>

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<title>Register page</title>
<head>
 
<meta charset="utf-8">
</head>
<body>
<h1>Registration  form </h1><br>
<form  method="POST" action= "real_portal.php">
<input type = "text" name="username"  placeholder="Username"  value="<?php if(isset($_POST['username']) && 
(!(get_by_username($_POST['username'])))) { echo $_POST['username'];}else{echo "";}?>" > 
<br>
<input type = "email" name = "email" placeholder="Email" value="<?php if(isset($_POST['email']) && 
(!(get_by_username($_POST['username'])))) { echo $_POST['email'];}else{echo "";}?>">
<br>
<input type ="password" name="password" placeholder="Password"><br>
<input type = "password" name="retype_password" placeholder="Retype Password"><br>
<button type="submit" name="submit_register" value="submit_register"> Submit</button><br>
<a href="real_portal.php?action=submit_register"><button type="submit" name="reset_register" value="reset_register">Reset </button></a><br>	
</form>
</body>
</html>

<?php

}
?> 



<?php
function validate_register($error=array() , $missing_field=array())
{
	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		if(empty($_POST['username']))
		{
			$missing_field[] = 'username';
		}
		if(!isset($_POST['username']) or (!$_POST['username']))
		{
			$error[]= '<p>username is missing</p>';
		}
		if(empty($_POST['email']))
		{
			$missing_field[] = 'email';
		}
		if(!isset($_POST['email']) or  (!$_POST['email']))
		{
			$error[] = '<p>email is missing</p>';
		}
		if(empty($_POST['password']))
		{
			$missing_field[]= 'password';
		}
		if(!isset($_POST['password']) or (!$_POST['password']))
		{
			$error[] = '<p>password is missing</p>';
		}
		if(empty($_POST['retype_password']))
		{
			$missing_field[]= 'retype_password';
		}
		if(!isset($_POST['retype_password']) or (!$_POST['retype_password']))
		{
			$error[] = '<p>retype_password is missing</p>';
		}
		if($_POST['password'] != $_POST['retype_password'])
		{
			$error[] = '<p>passwords did not matched</p>'; 
		}
		if(get_by_username($_POST['username']))
		{
			$missing_field[]= '<p>username olready exists</p>';

		}
		if(get_by_email($_POST['email']))
		{
			$missing_field[] = '<p>email olready existed try another</p>';
		}
		if($error or $missing_field)
		{
			display_register_form($error,$missing_field);
		}
		else
		{
			//session_start();
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['email'] = $_POST['email'];
			$_SESSION['password'] = $_POST['password'];
			session_write_close();
		}
	}
	else
	{
		echo "<br>not showing field errors<br>";
	}
}
?>

<?php
function process_register()
{
	$error = array() ; 
	$missing_field= array();
	//if(isset($_POST['submit_register']))
		validate_register($error, $missing_field);
		if(isset($_SESSION['username']) &&  isset($_SESSION['email']) &&  isset($_SESSION['password']))
			{
				insert($_SESSION['username'],$_SESSION['email'], $_SESSION['password']);
				header("Location:real_portal.php");
			}
	
}
?>

<?php
function connection()
{
	$dsn="mysql:host=localhost; dbname=db;charset=utf8";
	$username= "***";
	$password= "***";

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

function insert($user,$em,$pass)
{
	if($_SESSION['username'] && $_SESSION['email'] && $_SESSION['password'])
	{
		$conn = connection();
		$sql = "INSERT INTO prototype(username,email,password)VALUES(:user,:em,password(:pass))";
		try{
			$st= $conn->prepare( $sql);
			$st->bindValue(':user',$user,PDO::PARAM_STR);
			$st->bindValue(':em',$em, PDO::PARAM_STR);
			$st->bindValue(':pass',$pass, PDO::PARAM_STR);
			$st->execute();
			$conn=null;
			//header("Location:real_portal.php");
		}
		catch(PDOException $e){
			$conn= null;
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			unset($_SESSION['email']);
			//session_write_close();
			die("<br>Query failed ".$e->getMessage());
			//header("Location:real_portal.php");
		}
	}
	else
	{
		header("Location:real_portal.php");
	}
}
?>

<?php
 function logout()
 {
	unset($_SESSION['username']);
	unset($_SESSION['password']);
	unset($_SESSION['email']);
	if(isset($_SESSION['new_password']))
	{
		unset($_SESSION['new_password']);
	}
	if(isset($_SESSION['login_username']) && isset($_SESSION['login_password']))
	{
		unset($_SESSION['login_username']);
		unset($_SESSION['login_password']);
	}
	session_write_close();
	header("Location: real_portal.php");
 }
 ?>


 <?php
function details()
{
	if(isset($_SESSION['new_password']))
	{
		echo "<br>username is " .$_SESSION['username'];
		echo "<br>password is " .$_SESSION['new_password'];
	}
	
	elseif(isset($_SESSION['username']) && isset($_SESSION['password']))
	{
		echo "<br>username is " .$_SESSION['username'];
		echo "<br>password is " .$_SESSION['password'];
	}
	else
	{
		header("Location: real_portal.php");


	}
}

?>


<?php
function welcome_page()
{
	?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<title>welcome page</title>
<head>
<meta charset="utf-8">
</head>
<h1>
<?php echo "my details are <br>"; ?>
<pre>
<?php   details(); ?>
</pre>
</h1>
<h2>sample</h2>
<br>

<a href="real_portal.php?action=logout"><button type= "submit" name="logout" > logout</button></a> <br><br>
 <a href="real_portal.php?action=change_password"><button type="submit" name="change_password"   > Change My Password  </button> </a> 
</body>
</html>

<?php
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
			echo  "<br>Query failed<br>".$e->getMessage();
			header("Location:real_portal.php");
		}
	}
 function get_by_email($email)
	{
		$conn = connection();
		$sql = "SELECT * FROM prototype WHERE email = :email";
		try{
			$st= $conn->prepare( $sql );
			$st->bindValue(":email",$email,PDO::PARAM_STR);
			$st->execute();
			$user_data= $st->fetch();
			//parent::disconnect();
			$conn=  null;
			if($user_data)
			{
				return  $user_data ;
			}
		}
		catch(PDOException $e){
			echo  "<br>Query failed<br>".$e->getMessage();
			header("Location:real_portal.php");
		}
	}
?>

<?php
function display_change_password_form($error=array() , $missing_field=array())
{
	if($missing_field)
	{
		foreach($missing_field as $missing_fields)
		{
			echo "<br>".$missing_fields." is missing or error occured<br>" ;
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
	<title>change password</title>
	<head>
	<meta charset="utf-8">
	</head>
	<body>
	<form method="POST" action="real_portal.php">
	<label>username</label>
	<input type ="text" name="old_username" placeholder="username"><br>
	<label>old password</label>
	<input type ="password" name ="old_password" placeholder="password"><br>
	<label>New Password </label>
	<input type="password" name ="new_password" placeholder="new password">
	<br>
	<label> Confirm New Password </label>
	<input type = "password" name="retype_new_password" placeholder="retype new password">
	<br>
	<button type="submit" name= "submit_change_password" value= "submit_change_password" >Submit Change </button>
	</form>
	</body>
	</html>
	
	
	<?php
} 
?>



<?php
function validate_change_password_form($error = array() , $missing_field = array())
{
	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		if(empty($_POST['old_username']))
		{
			$missing_field[] = "old_username";
		}
		if(!isset($_POST['old_username']) or (!$_POST['old_username']))
		{
			$error[]= '<p>old username is missing</p>';
		}
		if(empty($_POST['old_password']))
		{
			$missing_field[]= 'old_password';
		}
		if(!isset($_POST['old_password']) or (!$_POST['old_password']))
		{
			$error[] = '<p>old_password is missing</p>';
		}
		if(empty($_POST['new_password']))
		{
			$missing_field[]= 'new_password';
		}
		if(!isset($_POST['new_password']) or (!$_POST['new_password']))
		{
			$error[] = '<p>new_password is missing</p>';
		}
		if(empty($_POST['retype_new_password']))
		{
			$missing_field[]= 'retype_new_password';
		}
		if(!isset($_POST['retype_new_password']) or (!$_POST['retype_new_password']))
		{
			$error[] = '<p>retype_new_password is missing</p>';
		}
		if($_POST['new_password'] != $_POST['retype_new_password'])
		{
			$missing_field[] = '<p>passwords_did_not_matched</p>'; 
		}
		if($error or $missing_field)
		{
			display_change_password_form($error, $missing_field);
		}
		else
		{
			$user_data = get_by_username($_POST['old_username']);
			if(get_by_username($_POST['old_username']))
			{
				
				$_SESSION['new_password'] = $_POST['new_password'];
				//$_SESSION['old_username'] = $_POST['old_username'];
				session_write_close();
			}
			
			else
			{
				echo "<br>error occurred<br>";
			}
		}	
		
	}
}

function update_new_password($new_password,$username)
{
	if($_SESSION['new_password'])
	{
		$conn = connection();
		$sql = " UPDATE prototype SET password = password(:new_password) WHERE username = :username";
		try{
			$st = $conn->prepare( $sql );
			$st->bindValue(":new_password", $new_password, PDO::PARAM_STR);
			$st->bindValue(":username", $username, PDO::PARAM_STR);
			$st->execute();
		}
		catch(PDOException $e)
		{
			echo "password not updated".$e->getMessage();
			//header("Location: real_portal.php");
		}
	}
}
?>

<?php
function submit_change_password()
{
	$error = array();
	$missing_field = array();
	validate_change_password_form($error, $missing_field);
	
		if(!empty($_POST['new_password']) && ($_POST['new_password'] == $_POST['retype_new_password']))
		{
			update_new_password($_SESSION['new_password'] , $_SESSION['username']);
			echo "<br>passwords updated<br>";
		header("Location: real_portal.php");
		}
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
<form action= "real_portal.php" method="POST">
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
		welcome_page();
	}
}



?>
