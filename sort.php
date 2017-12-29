<html>

  <head>
    <title>AJB SS Matchmaking</title>
  </head>

  <body>
    <?php

      ## establish database connection
      $dir = 'sqlite:ajbss.db';
      $dbh = new PDO($dir) or die("can't talk to SQLite");

      ## query to retrieve list of users, this needs to be more strict (only users that signed up, etc)
      $user_id_query = "SELECT user_id FROM users WHERE status=0 limit 5";

      ## query to retrieve list of pairings
      $pairing_query = "SELECT santa, santee, year FROM pairings where type = ?";

      ## query to add pairings
      $add_pairing_query = "INSERT INTO pairings (year, type, santa, santee, status) VALUES (?,?,?,?,?)";
      $add_pairing = $dbh->prepare($add_pairing_query);

      ## set some defaults, these would be selected by admins
      $year = $_GET["year"];
      $type = 5;
      $pairing_array = [];
      $status = 0;
      $user_id_list = [];
      $insert_array = [];

      ## retrieve the data from SQLite and initialize variable
      $user_id_results = $dbh->query($user_id_query)->fetchAll(PDO::FETCH_COLUMN);

      foreach($user_id_results as $value) {

        $user_id_list[$value] = $value;

      }

      ## shuffle the user list, this is where part of our entropy comes from
      ## shuffle($user_id_list);
      ## this doesn't work right, since $key != $value anymore

      ## make two copies of the list, so that we can punch out eacn individually
      $santa_list = $user_id_list;
      $santee_list = $user_id_list;

      ## retrieve list of pairings
      $pairing_list_query = $dbh->prepare($pairing_query);
      $pairing_list_query->execute([$type]);
      $pairing_list = $pairing_list_query->fetchAll();

      foreach($pairing_list as list($a, $b, $c)) {

        $pairing_array[$a][$c] = $b;

      }

      ## this punches out $santa_list
      foreach($santa_list as $key=>$santa) {
        echo "<p>Key: " . $key . " Santa: " . $santa . "</p>";


        $candidate_list = $santee_list;
        ## can't match with yourself!
        unset($candidate_list[$key]);
        ## check for previous pairings...
        foreach($pairing_array[$santa] as $key_2=>$santee) {

          unset($candidate_list[$santee]);
          echo "OLD Year: " . $key_2 . " | Santa ID: " . $santa . " | Santee ID: " . $santee . "<br>";

        }
        ## other filters go here...
        $candidate = array_rand($candidate_list, 1);

        if(!isset($candidate)) {

          echo "<h1>CANDIDATE RNG FAILED, TRY AGAIN</h1>";
          exit;

        }

        ## punch out the $santee_list
        unset($santee_list[$candidate]);
        
        ## throwaway to display / check code
        echo "NEW Year: " . $year . " | Santa ID: " . $santa . " | Santee ID: " . $candidate . "<br><br>";

        $insert_array[$santa]["year"] = $year;
        $insert_array[$santa]["type"] = $type;
        $insert_array[$santa]["santa"] = $santa;
        $insert_array[$santa]["candidate"] = $candidate;
        $insert_array[$santa]["status"] = $status;

      }

      foreach($insert_array as $insert) {

        $add_pairing->execute([$insert["year"],$insert["type"],$insert["santa"],$insert["candidate"],$insert["status"]]);

      }


    ?>
  </body>

</html>        