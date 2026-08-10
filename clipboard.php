<?php

$clipboard = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if ((isset($_POST["robotCheck"]) && $_POST["robotCheck"]) && (isset($_COOKIE["robotCheck"]) && $_COOKIE["robotCheck"])) {
    setcookie("robotCheck", "true", 0, "/");

    if (empty($_POST["clipboard"])) {
      $clipboard = "";
    } else {
      $clipboard = test_input($_POST["clipboard"]);

      // Write to clipboard with submitted text
      $filename = 'clipboard.txt';
      if (is_writable($filename)) {
        if (!$handle = fopen($filename, 'a')) {
          echo "Error: Cannot open file ($filename)";
          exit;
        }

        file_put_contents($filename, ""); // Clean
        if (fwrite($handle, $clipboard) === FALSE) {
          echo "Error: Cannot write to file ($filename)";
          exit;
        }
        fclose($handle);
      } else {
        echo "Error: The file $filename is not writable";
      }
    }
  }

  // Prevent form resubmission warnings on browser refresh.
  header("Location: /");
  exit;
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  return htmlspecialchars($data);
}

?>

<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" href="style.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="js/cookie-utils.js"></script>
</head>

<body>
<div id="clipboard-input">
  <h3>Text To Submit<br></h3>
  <form method="POST" action="/">
    <textarea autofocus name="clipboard" rows="12"><?php echo $clipboard; ?></textarea><br>
    <input id="robotCheck" type="checkbox" name="robotCheck" <?php if (isset($_COOKIE["robotCheck"]) && $_COOKIE["robotCheck"]) echo "checked"; ?>>I'm not a robot</input>
    <script>
      const checkbox = document.getElementById('robotCheck');
      checkbox.addEventListener('change', (event) => {
        if (event.currentTarget.checked) {
          setCookie("robotCheck", true);
        } else {
          setCookie("robotCheck", false);
        }
      });
    </script>
    <div style="text-align:center">
      <button id="btn-submit" type="submit">Submit</button>
    </div>
  </form>
</div>

<div id="clipboard-content">
  <h3>Clipboard<br></h3>
  <div class="clipboard">
    <div>
    <?php
    if ($file = fopen("clipboard.txt", "r")) {
      while (!feof($file)) {
        $line = fgets($file);
        echo $line;
        echo "<br>";
      }
      fclose($file);
    }
    ?>
    </div>
  </div>

  <div style="text-align:center; margin-top:2px">
    <button onclick="copyToClipboard(readTextFile('clipboard.txt'))">Copy text</button>
    <button onclick="openLink(readTextFile('clipboard.txt'))">Open link</button>
    <script>
    function copyToClipboard(text) {
      var $temp = $("<input>");
      $("body").append($temp);
      $temp.val(text).select();
      document.execCommand("copy");
      $temp.remove();
    }

    function openLink(text) {
      console.log("Opening link " + text);
      window.open(text, "_blank");
    }

    function readTextFile(file) {
      let rawFile = new XMLHttpRequest();
      let result;
      rawFile.open("GET", file, false);
      rawFile.onreadystatechange = function () {
        if (rawFile.readyState === 4) {
          if(rawFile.status === 200 || rawFile.status == 0) {
            result = rawFile.responseText;
          }
        }
      }
      rawFile.send(null);
      return result;
    }

    // Ctrl + enter to submit
    document.onkeydown = keydown;
    function keydown(event) {
      if (event.ctrlKey && event.code === "Enter") {
        document.getElementById("btn-submit").click(); 
      }
    }
    </script>
  </div>
</div>
<br>
</body>

</html>
