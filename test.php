<?php
require_once __DIR__ . '/vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('392219111176-bluhvp5d9fg2eiav5dqbdssj85jb2qav.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/project-main/tms/');
$client->addScope('email');
$client->addScope('profile');

session_start();

if (isset($_GET['code'])) {
  try {
    $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/google-login/profile.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
  } catch (Google_Service_Exception $e) {
    // Handle authentication errors
  } catch (Google_Exception $e) {
    // Handle other errors
  }
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $oauth2 = new Google_Service_Oauth2($client);
  $user = $oauth2->userinfo->get();
  $user_id = $user->id;
  $user_name = $user->name;
  $user_email = $user->email;

  $sql ="SELECT EmailId,social_id FROM tblusers WHERE EmailId=:user_email and social_id=:user_id";
  $query= $dbh -> prepare($sql);
  $query-> bindParam(':user_email', $user_email, PDO::PARAM_STR);
  $query-> bindParam(':user_id', $user_id, PDO::PARAM_STR);
  $query-> execute();
  $results=$query->fetchAll(PDO::FETCH_OBJ);
if($query->rowCount() > 0)
{ 
  $sql="INSERT INTO  tblusers(FullName,EmailId,social_id) VALUES(:user_name,:user_email,:user_id)";
  $query = $dbh->prepare($sql);
  $query->bindParam(':user_name',$user_name,PDO::PARAM_STR);
  $query->bindParam(':user_email',$user_email,PDO::PARAM_STR);
  $query->bindParam(':user_id',$user_id,PDO::PARAM_STR);
  $query->execute();
  $_SESSION['login']=$user_email;
  $lastInsertId = $dbh->lastInsertId();

    if($lastInsertId)
    {
    $_SESSION['msg']="You are Scuccessfully registered. Now you can login ";
    header('location:thankyou.php');
    }
} else{
    $sql="INSERT INTO  tblusers(FullName,EmailId,social_id) VALUES(:user_name,:user_email,:user_id)";
  $query = $dbh->prepare($sql);
  $query->bindParam(':user_name',$user_name,PDO::PARAM_STR);
  $query->bindParam(':user_email',$user_email,PDO::PARAM_STR);
  $query->bindParam(':user_id',$user_id,PDO::PARAM_STR);
  $query->execute();
  $_SESSION['login']=$user_email;
  $lastInsertId = $dbh->lastInsertId();

    if($lastInsertId)
    {
    $_SESSION['msg']="You are Scuccessfully registered. Now you can login ";
    header('location:thankyou.php');
    }
}

 

} else {
  $auth_url = $client->createAuthUrl();
}
?>

<html>
<head>
  <title>Google Sign-In Example</title>
</head>
<body>
  <?php if (isset($auth_url)): ?>
    <a href="<?php echo $auth_url; ?>">Sign in with Google</a>
  <?php else: ?>
    <p>Welcome, <?php echo $user_name; ?>!</p>
    <p>Your email address is <?php echo $user_email; ?></p>
  <?php endif; ?>
</body>
</html>
