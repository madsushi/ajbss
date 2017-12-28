<html>

  <head>
    <title>AJB SS User List</title>
  </head>

  <body>
    <?php

      $dir = 'sqlite:ajbss.db';
      $dbh = new PDO($dir) or die("can't talk to SQLite");
      $query = "SELECT * FROM users";

      echo "user_id" . " | " . "user_name" . " | " . "status"  . " | " . "address" . " | " . "phone" . " | " . "international";

      foreach ($dbh->query($query) as $key => $row) {
        echo "<br>";
        echo $row[0] . " | " . $row[1] . " | " . $row[2] . " | " . $row[3] . " | " . $row[4] . " | " . $row[5];
      }

    ?>
  </body>

</html>