<html>

  <head>
    <title>AJB SS User Page</title>
  </head>

  <body>
    <?php

      ## This is this user's page!
      $user = $_GET["user"];
      $user_data = [];
      $all_users_names = [];

      ## establish database connection
      $dir = 'sqlite:ajbss.db';
      $dbh = new PDO($dir) or die("can't talk to SQLite");

      ## DB query to retrieve the user database
      $get_all_users_query = "SELECT * FROM users";
      $get_all_users = $dbh->prepare($get_all_users_query);
      $get_all_users->execute();
      $all_users_info = $get_all_users->fetchAll(PDO::FETCH_ASSOC);

      foreach($all_users_info as $key=>$entry) {
        if($key == $user) {

          $user_data['name'] = $entry['user_name'];
          ## $user_data[$entry['type']]['status'] = $entry['status'];
          $user_data['address'] = $entry['address'];
          $all_users_names[$key] = $entry['user_name'];

        } else {

          $all_users_names[$key] = $entry['user_name'];

        }

      }

      ## DB query to retrieve list of exisitng santee pairings for this specific user
      $get_previous_santee_query = "SELECT santa, santee, year, type FROM pairings where santa = ?";

      ## DB query to retrieve list of existing santa pairings for this specific user
      $get_previous_santa_query = "SELECT santa, santee, year, type FROM pairings where santee = ?";

      ## DB query to change status to signed up ("1")
      $sign_up_user_query = "UPDATE users SET status = 1 WHERE user_id = ?";

      ## $previous_santee_pairings is used to hold the previous santee pairings we get back from the database in an easy-to-use format
      $previous_santee_pairings = [];

      ## $previous_santa_pairings is used to hold the previous santa pairings we get back from the database in an easy-to-use format
      $previous_santa_pairings = [];

      ## retrieve list of existing santee pairings, put it into one big array
      $previous_santee = $dbh->prepare($get_previous_santee_query);
      $previous_santee->execute([$user]);
      $previous_santee_list = $previous_santee->fetchAll();

      ## retrieve list of existing santa pairings, put it into one big array
      $previous_santa = $dbh->prepare($get_previous_santa_query);
      $previous_santa->execute([$user]);
      $previous_santa_list = $previous_santa->fetchAll();

      ## the $previous_santee_pairings array is based on [type][year] = santee, so we can see ALL of the pairings for this santa
      foreach($previous_santee_list as list($a, $b, $c, $d)) {

        $previous_santee_pairings[$d][$c] = $b;

      }

      ## the $previous_santa_pairings array is based on [type][year] = santa, so we can see ALL of the pairings for this santee
      foreach($previous_santa_list as list($a, $b, $c, $d)) {

        $previous_santa_pairings[$d][$c] = $a;

      }

      echo "<h1>Welcome <span style='float: none;' title='User ID: " . $user . "'>" . $user_data['name'] . "!</span></h1>";
      echo "<h2>This is your address: " . $user_data['address'] . "</h2>";

      echo "<h2>Here are your previous santees:</h2>";

      foreach($previous_santee_pairings as $key=>$value) {

        echo "<b>Type: " . $key . "</b><br>";
        
        foreach($value as $k2=>$v2) {

          echo " Year: " . $k2 . " Santee: " . $all_users_names[$v2] . " (id: " . $v2 . ")<br>";

        }

        echo "<br>";

      }

      echo "<h2>Here are your previous santas:</h2>";

      foreach($previous_santa_pairings as $key=>$value) {

        echo "<b>Type: " . $key . "</b><br>";

        foreach($value as $k2=>$v2) {

          echo " Year: " . $k2 . " Santa: " . $all_users_names[$v2] . " (id: " . $v2 . ")<br>";

        }

        echo "<br>";

      }

    ?>
  </body>

</html>        