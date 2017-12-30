<html>

  <head>
    <title>AJB SS Matchmaking</title>
  </head>

  <body>
    <?php

      ## establish database connection
      $dir = 'sqlite:ajbss.db';
      $dbh = new PDO($dir) or die("can't talk to SQLite");

      ## DB query to retrieve list of users, this needs to be more strict (only users that signed up, etc)
      ## limit is in place to make finding errors/failures faster
      ## should we have different users per year/type? otherwise how do we check if they're signed up THIS year?
      $get_santas_query = "SELECT user_id FROM users WHERE status=0 limit 10";

      ## DB query to retrieve list of exisitng pairings, for exclusion from new pairings
      $get_previous_pairings_query = "SELECT santa, santee, year FROM pairings where type = ?";

      ## DB query to add pairings once a complete match set is created
      ## prepare this one in advance since we'll call it multiple times within a loop
      $add_new_pairings_query = "INSERT INTO pairings (year, type, santa, santee, status) VALUES (?,?,?,?,?)";
      $add_new_pairings = $dbh->prepare($add_new_pairings_query);

      ## set/get some defaults, these would be selected by admins
      $year = $_GET["year"];
      $type = $_GET["type"];
      
      ## $previous_pairings is used to hold the previous pairings we get back from the database in an easy-to-use format
      $previous_pairings = [];
      
      ## $santas_master_list is where we hold potential santas, and DO NOT MODIFY IT, in case we have to reset and try again
      $santas_master_list = [];
      
      ## $to_be_insterted is where we put our pairings for INSERT once it's completed
      $to_be_inserted = [];
      
      ## we start with $status at 0 since the pairings are new, this will be incremented by user and admin pages
      $status = 0;
      
      ## this is a throwaway variable for the next-year links
      $next_year = $year + 1;

      ## throwaway links for re-running multiple years and wiping data for testing
      echo "<p><a href='/ajbss/sort.php?year=" . $next_year . "&type=" . $type . "'>NEXT YEAR</a></p><p><a href='/ajbss/delete.php'>DELETE DATA (no worries)</a></p>";

      ## retrieve the list of santas from SQLite and initialize variable, we're only grabbing their ID, so we use FETCH_COLUMN
      $get_santas_results = $dbh->query($get_santas_query)->fetchAll(PDO::FETCH_COLUMN);

      ## set the key = value for the $santas_list array, so that we can easily punch stuff out
      foreach($get_santas_results as $value) {

        $santas_master_list[$value] = $value;

      }

      ## shuffle the user list, this is where part of our entropy comes from
      ## shuffle($user_id_list);
      ## this doesn't work right, since $key != $value anymore
      ## the foreach($get_santas_results) list needs to have keys match values for easy access

      ## make a copy of the $santas_list, so that we can punch users out of each list (santas, santees) individually
      ## these are the list we can modify
      $santa_list = $santas_master_list;
      $santee_list = $santas_master_list;
      

      ## retrieve list of previous pairings, put it into one big array
      $previous_pairings_list_query = $dbh->prepare($get_previous_pairings_query);
      $previous_pairings_list_query->execute([$type]);
      $previous_pairings_list = $previous_pairings_list_query->fetchAll();

      ## the $previous_pairings_array is based on [santa][year] = santee, so we can see ALL of the pairings for each santa
      ## if we want to filter out people from having a previous santa as their santee, we'll have to do this array twice?
      foreach($previous_pairings_list as list($a, $b, $c)) {

        $previous_pairings[$a][$c] = $b;

      }

      ## pull santas one by one from the big list of signed-up santas
      foreach($santa_list as $key=>$santa) {
          
        ## throwaway for showing each santa's previous pairings and new pairing
        echo "Santa: " . $santa . "<br>";

        ## we create a temporary copy of the remaining $santee_list members
        ## and punch out everyone who isn't eligible
        $candidate_list = $santee_list;
        
        ## FILTER SECTION:
        ## can't match with yourself, so we punch out the santa themselves
        unset($candidate_list[$key]);
        
        ## check for previous pairings...
        ## throwaway to echo the years / santees of past pairings for this santa, just for visual review
        foreach($previous_pairings[$santa] as $key_2=>$santee) {

          ## punch out previous santees
          unset($candidate_list[$santee]);
          echo "OLD Year: " . $key_2 . " | Santa ID: " . $santa . " | Santee ID: " . $santee . "<br>";

        }
        
        ## other filters go here...
        ## maybe previous santas of this santa, etc
        
        ## randomly pull a remaining user from the $candidate_list
        ## this is the most naive part of pairing
        ## it's a greedy algorithm, and may not leave viable candidates for future santas
        $candidate = array_rand($candidate_list, 1);

        ## if the candidate list is empty, just error and bail out
        ## this is throwaway
        ## what we SHOULD do is just re-roll automatically
        ## basically just reset $santas_list and reset $santee_list back to $santas_master_list
        if(!isset($candidate)) {

          echo "<h1><font color=red>CANDIDATE RNG FAILED, TRY AGAIN</font></h1>";
          exit;

        }

        ## if we have a viable candidate:
        ## punch out the $santee_list
        unset($santee_list[$candidate]);

        ## throwaway to display / check code
        echo "<b><font color=green>NEW Year: " . $year . " | Santa ID: " . $santa . " | Santee ID: " . $candidate . "</font></b><br><br>";

        ## store the pairing in the $to_be_inserted holder
        ## we will only run the INSERT command if we have a complete pairing (all santas/santees paired)
        $to_be_inserted[$santa]["year"] = $year;
        $to_be_inserted[$santa]["type"] = $type;
        $to_be_inserted[$santa]["santa"] = $santa;
        $to_be_inserted[$santa]["candidate"] = $candidate;
        $to_be_inserted[$santa]["status"] = $status;

      }

      ## if we exit the main loop succesfully, go ahead and insert all the pairings
      ## we need to add some controls to check for existing pairings for the target year first, so we don't overwrite/duplicate
      foreach($to_be_inserted as $insert) {

        $add_new_pairings->execute([$insert["year"],$insert["type"],$insert["santa"],$insert["candidate"],$insert["status"]]);

      }


    ?>
  </body>

</html>        