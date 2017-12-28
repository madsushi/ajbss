<html>

  <head>
    <title>AJB SS Matchmaking</title>
  </head>

  <body>
    <?php

      ## establish database connection
      $dir = 'sqlite:ajbss.db';
      $dbh = new PDO($dir) or die("can't talk to SQLite");

      ## retrieve list of users, this needs to be more strict (only users that signed up, etc)
      $user_id_query = "SELECT user_id FROM users WHERE status=0";

      ## set some defaults, these would be selected by admins
      $pairing_id = 0;
      $year = 2018;
      $type = 5;

      ## retrieve the data from SQLite and initialize variable
      $user_id_list = $dbh->query($user_id_query)->fetchAll(PDO::FETCH_COLUMN);

      ## shuffle the user list, this is where part of our entropy comes from
      shuffle($user_id_list);

      ## make two copies of the list, so that we can punch out eacn individually
      $santa_list = $user_id_list;
      $santee_list = $user_id_list;

      ## this punches out $santa_list
      foreach($santa_list as $key=>$santa) {

        $candidate_list = $santee_list;
        ## can't match with yourself!
        unset($candidate_list[$key]);
        ## check for previous pairings...
        ## other filters go here...
        $candidate = array_rand($candidate_list, 1);

        ## punch out the $santee_list
        unset($santee_list[$candidate]);

        ## throwaway to display / check code
        echo "Pairing ID: " . $pairing_id++ . " | Year: " . $year . " | Santa ID: " . $santa . " | Santee ID: " . $candidate . "<br>";

      }


    ?>
  </body>

</html>