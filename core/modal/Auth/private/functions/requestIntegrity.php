<?php 
/**
 * @author Mudassir Mirza
 * @package Robust - B2B Productions
 * @version 1.0 beta
 */
ob_start();

require_once(dirname(__FILE__,4) . "/initialize.php");


//public method to check the status of account if active or not
//return type of this method is string
function userAccountStatus($params){
    //get the database connection and environment urls
    global $link;

    $username= $params['username'];

    //run the query
    $stmt= $link->prepare("SELECT username, userAccountStatus FROM `opd_user_accounts` WHERE username= :username");
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->execute();

    if($row= $stmt->fetch()){
        $username= $row['username'];
        $userAccountStatus= $row['userAccountStatus'];

        return $userAccountStatus;
    }else{
        return 'false';
    }

    //dispose the objects
    $link= NULL;
    $stmt= NULL;

}



//public method to check the status of user IP
//return type of this method is string
function userIPStatus($params){
    //get the database connection and environment urls
    global $link;

    $ipAddress= $params['ip'];

    //run the query
    $stmt= $link->prepare("SELECT * FROM `opd_user_accounts` WHERE userIP= :ipAddress");
    $stmt->bindParam(":ipAddress", $ipAddress, PDO::PARAM_STR);
    $stmt->execute();

    if($stmt->rowCount() == 1){
        return "blocked";
    }else{
        return 'false';
    }

    //dispose the objects
    $link= NULL;
    $stmt= NULL;

}


function isAdminValid($userID){
    global $link;
    global $appBaseURL;

    if(isset($_SESSION['username']) AND $_SESSION['username']){

    $stmt= $link->prepare("SELECT id, username, access_level, account_status FROM `user_accounts` WHERE id= :id");
    $stmt->bindParam(":id", $userID, PDO::PARAM_STR);
    $stmt->execute();

    if($row= $stmt->fetch()){
        $id= $row['id'];
        $username= $row['username'];
        $access_level= $row['access_level'];
        $account_status= $row['account_status'];

        if($access_level==='ADMIN' AND $account_status=== 'ACTIVE' AND $username=== $_SESSION['username']){

        return true;
        }else{
            return false;
            session_unset();
            session_destroy();
        }

    }else{
        return false;
        session_unset();
        session_destroy();
    }
    
}

else{
            return false;
            session_unset();
            session_destroy();
}

//dispose the database connection
$link= $appBaseURL= NULL;
}



function isEmployeeValid($userID){

    if(isset($_SESSION['username']) AND $_SESSION['username']){

    global $link;
    global $appBaseURL;

    $stmt= $link->prepare("SELECT id, username, access_level, account_status FROM `user_accounts` WHERE id= :id");
    $stmt->bindParam(":id", $userID, PDO::PARAM_STR);
    $stmt->execute();

    if($row= $stmt->fetch()){
        $id= $row['id'];
        $username= $row['username'];
        $access_level= $row['access_level'];
        $account_status= $row['account_status'];

        if($access_level==='USER' AND $account_status=== 'ACTIVE'){

        return true;
        }else{
            header("Location: $appBaseURL/sandbox?error=ERR_ACCESS_DENIED($account_status)");
            session_unset();
            session_destroy();
            exit();
            return false;
        }

    }else{
        header("Location: $appBaseURL/sandbox?error=ERR_ACCESS_DENIED");
            session_unset();
            session_destroy();
            exit();
        return false;
    }
    
}

else{
    header("Location: $appBaseURL/sandbox?error=ERR_ACCESS_DENIED");
            session_unset();
            session_destroy();
            exit();
    return false;
}

//dispose the database connection
$link= NULL;
}


function isUserAlreadyExist($username){
    global $link;

    $stmt= $link->prepare("SELECT username FROM `user_accounts` WHERE username= :username");
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        return TRUE;
    }else{
        return FALSE;
    }

    //dispose the db connection
    $link= NULL;
}


function afterSuccessfullAuthentication($params){
    // Regenerate session ID to invalidate the old one.
      // Super important to prevent session hijacking/fixation.
  
    //creating dateTime object
      $DateTime= new DateTime();
  
      $_SESSION['signedIn'] = true;
  
      // Save these values in the session, even when checks aren't enabled 
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
      $_SESSION['last_login'] = time();
      $_SESSION['ip']= $_SERVER['REMOTE_ADDR'];
      $_SESSION['username'] = $params['username'];
      $_SESSION['userAccessLevel']= $params['userAccessLevel'];
      $_SESSION['userRole'] = $params['userRole'];
      $_SESSION['timestamp']= $DateTime->getTimeStamp();
  }
  
  
  //method to perform authorization against 
  //return type of this method is string
  function authorization($params){
    //get the database connection
    global $link;
  
  
    $stmt = $link->prepare("SELECT id, username, userAccountStatus, userAccessLevel FROM `opd_user_accounts` WHERE username=:username");
    $stmt->bindParam(":username", $params['username'], PDO::PARAM_STR);
  
    if($stmt->execute()){
      if($row= $stmt->fetch()){
  
        $id= $row['id'];
        $username= $row['username'];
        $userAccountStatus= $row['userAccountStatus'];
        $userAccessLevelActual= $row['userAccessLevel'];
  
        if($userAccountStatus === 'ACTIVE' AND $userAccessLevelActual === $params['userAccessLevel']){
          return "true";
        }else{
  
          //if authorization fails
          return "false";
        }
      
        }else{
  
          //if records does not exists
          return "false";
        }
      }else{
  
        //if query does not executes
        return "false";
      }
  }
  
  
  
  function redirectTo($new_location) {
    header("Location: " . $new_location);
    exit;
  }

?>