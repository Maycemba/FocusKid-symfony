<?php
// Fichier: public/api-voice.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$commande = $data['commande'] ?? $data['command'] ?? '';
$commande = strtolower(trim($commande));

// Réponse par défaut
$response = ['success' => false, 'message' => 'Commande non reconnue'];

if ($commande === 'ping') {
    $response = ['success' => true, 'message' => 'pong'];
}
elseif (strpos($commande, 'coloriage') !== false || strpos($commande, 'colorier') !== false || strpos($commande, 'dessin') !== false) {
    $response = [
        'success' => true,
        'action' => 'coloriage',
        'redirect' => '/sessions/de/calme/activite/coloriage',
        'message' => 'Ouverture de la session coloriage. Bon amusement !'
    ];
}
elseif (strpos($commande, 'histoire') !== false || strpos($commande, 'raconter') !== false || strpos($commande, 'conte') !== false) {
    $response = [
        'success' => true,
        'action' => 'histoire',
        'redirect' => '/sessions/de/calme/activite/histoire',
        'message' => 'Ouverture de la session histoire. Préparez-vous à écouter !'
    ];
}
elseif (strpos($commande, 'respiration') !== false || strpos($commande, 'respirer') !== false || strpos($commande, 'calme') !== false) {
    $response = [
        'success' => true,
        'action' => 'respiration',
        'redirect' => '/sessions/de/calme/activite/respiration',
        'message' => 'Ouverture de la session respiration. Inspirez, expirez...'
    ];
}
elseif (strpos($commande, 'musique') !== false || strpos($commande, 'chanson') !== false || strpos($commande, 'son') !== false) {
    $response = [
        'success' => true,
        'action' => 'musique',
        'redirect' => '/sessions/de/calme/activite/musique',
        'message' => 'Ouverture de la session musique. Laissez-vous emporter par les sons !'
    ];
}

echo json_encode($response);