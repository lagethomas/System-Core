<?php
/**
 * Backend Lead Scraper V2 - Thomas Henrique
 * Foco: Performance e Limite de 100 itens
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

define('SERP_API_KEY', 'c6ed92b4a2954e2c7d322efbbf2428931ffb4a19fa767f55ac4ed05429a6a19d'); 

$input = json_decode(file_get_contents('php://input'), true);
$keyword = $input['keyword'] ?? '';

if (empty($keyword)) {
    http_response_code(400);
    echo json_encode(['error' => 'Termo de busca vazio.']);
    exit;
}

$queryParams = [
    'engine'  => 'google_maps',
    'q'       => $keyword,
    'api_key' => SERP_API_KEY,
    'hl'      => 'pt-br',
    'gl'      => 'br',
    'num'     => 100 // Solicita o máximo de resultados (até 100)
];

$ch = curl_init("https://serpapi.com/search.json?" . http_build_query($queryParams));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Aumentado para lidar com volume maior
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Falha na API', 'code' => $httpCode]);
    exit;
}

$data = json_decode($response, true);
$leads = [];

if (isset($data['local_results'])) {
    foreach ($data['local_results'] as $result) {
        $leads[] = [
            'nome'     => $result['title'] ?? 'N/A',
            'email'    => 'Não disponível', 
            'telefone' => $result['phone'] ?? 'S/ Tel',
            'link'     => $result['place_id_search'] ?? '#',
            'rating'   => $result['rating'] ?? 'N/A',
            'reviews'  => $result['reviews'] ?? 0
        ];
    }
}

echo json_encode($leads);