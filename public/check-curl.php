<?php
echo "<h1>Vérification cURL</h1>";
echo "cURL activé : " . (extension_loaded('curl') ? '✅ OUI' : '❌ NON') . "<br>";
echo "PHP version : " . phpversion() . "<br>";

if (extension_loaded('curl')) {
    echo "<h2>Test connexion Mailtrap</h2>";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://send.api.mailtrap.io/api/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: " . $httpCode . "<br>";
    echo "Mailtrap API accessible: " . ($httpCode > 0 ? '✅ OUI' : '❌ NON') . "<br>";
}
?>