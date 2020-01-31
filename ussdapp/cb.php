<?php
//receive AT Posts
//require_once('dbConnector.php');

$recordingUrl = $_POST['sessionId'];
$isActive  = $_POST['isActive'];
$direction = $_POST['direction'];
$callerNumber = $_POST['callerNumber'];
$destinationNumber = $_POST['destinationNumber'];
$durationInSeconds  = $_POST['durationInSeconds'];
$currencyCode  = $_POST['currencyCode'];
$amount  = $_POST['amount'];
$language = "";

$dbname = 'dissemination';
$dbuser = 'root';
$dbpass = 'Rc4@dm1n';
//$dbpass = '';
$dbhost = 'localhost';
$conn = new mysqli($dbhost, $dbuser,$dbpass,$dbname);
        $sql = "SELECT Level8 FROM ussdtransaction WHERE Msisdn = $callerNumber ORDER BY TranId DESC LIMIT 1 ";
        $query = $conn->query($sql);
        if($query->num_rows > 0){
          $field= $query->fetch_assoc();
          $language = $field['Level8'];
          
        }
        else{
          
          logFile("No such record for ".$callerNumber);
        }

if ($isActive == 1) {
    $text = "Please select language.";
    
     if ($language == "LUGANDA") {
    
      // Compose the response
         $response  = '<?xml version="1.0" encoding="UTF-8"?>';
         $response .= '<Response>';
     
         $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
         //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
     
         $response .= '</Response>';
       
         // Print the response onto the page so that our gateway can read it
         header('Content-type: text/plain');
         echo $response;
     }elseif ($language == "CHOPE") {
       // Compose the response
       $response  = '<?xml version="1.0" encoding="UTF-8"?>';
       $response .= '<Response>';
   
       $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/CHOPE_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
       //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
   
       $response .= '</Response>';
     
       // Print the response onto the page so that our gateway can read it
       header('Content-type: text/plain');
       echo $response;
     }
     elseif ($language == "LUSOGA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUSOGA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }
    elseif ($language == "IKE") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/IKE_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }
    elseif ($language == "ACHOLI") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/ACHOLI_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }
    elseif ($language == "ALUR") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/ALUR_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }
    elseif ($language == "ATESO") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/ATESO_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }
    elseif ($language == "JOPADHOLA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/JOPADHOLA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }
    elseif ($language == "KAKWA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/KAKWA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }
    elseif ($language == "KINYARWANDA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/KINYARWANDA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "KUMAM") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/KUMAM_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "KUPSAPINY") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/KUPSAPINY_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LANGO") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LANGO_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LUBWISI") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUBWISI_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LUGBARA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUGBARA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LUGUNGU") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUGUNGU_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LUGWERE") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUGWERE_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LUKONZO") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUKONZO_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    } elseif ($language == "LUMASABA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUMASABA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LUNYOLE") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUNYOLE_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LU0") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUO_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LURUULI") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LURUULI_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "LUSAMIA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/LUSAMIA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "MADI") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/MADI_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "NG'AKARIMOJONG") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/NG\'AKARIMOJONG_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "NUBI") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/NUBI_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "POKOT") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/POKOT_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "RUKIGA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/RUKIGA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "RUNYANKOLE") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/RUNYANKOLE_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "RUNYARUGURU") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/RUNYARUGURU_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "RUNYORO") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/RUNYORO_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "RUTOORO") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/RUTOORO_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "RWAMBA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/RWAMBA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }

    elseif ($language == "URUFUMBIRA") {
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
      $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/URUFUMBIRA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
      //   $response .= '<Say>'.$text.'</Say>'; https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
    }
    else{
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
  
     // $response .= '<Play url="http://wids.mak.ac.ug/Dissemination/uploads/URUFUMBIRA_MAM19.mp3"/>';//http://wids.mak.ac.ug/Dissemination/uploads/LUGANDA_MAM19.mp3
       $response .= '<Say>'.$text.'</Say>';// https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3
  
      $response .= '</Response>';
    
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
      
    }








}else {
  // You can then store this information in the database for your records
  $durationInSeconds  = $_POST['durationInSeconds'];
  $currencyCode  = $_POST['currencyCode'];
  $amount  = $_POST['amount'];
  echo ' call back';
  //echo ' call back'
}
