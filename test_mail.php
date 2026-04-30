<?php
$smtp = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 10);
if ($smtp) {
    echo "✅ Port 587 is OPEN - connection works!";
    fclose($smtp);
} else {
    echo "❌ Port 587 is BLOCKED - error: $errstr ($errno)";
}

$smtp2 = fsockopen('smtp.gmail.com', 465, $errno2, $errstr2, 10);
if ($smtp2) {
    echo "<br>✅ Port 465 is OPEN - connection works!";
    fclose($smtp2);
} else {
    echo "<br>❌ Port 465 is BLOCKED - error: $errstr2 ($errno2)";
}
?>
--dgeg mqcx vtvd vvjl