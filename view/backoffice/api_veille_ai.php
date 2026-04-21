<?php
require_once dirname(__DIR__, 2) . '/controller/VeilleAIController.php';
require_once dirname(__DIR__, 2) . '/controller/VeilleC.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$aiController = new VeilleAIController();
$veilleC = new VeilleC();

switch ($action) {
    case 'generate_draft':
        $data = json_decode(file_get_contents('php://input'), true);
        $draft = $aiController->generateDraft($data);
        echo json_encode(['success' => true, 'draft' => $draft]);
        break;

    case 'scout_data':
        $data = json_decode(file_get_contents('php://input'), true);
        $query = $data['query'] ?? '';
        if (empty($query)) {
            echo json_encode(['success' => false, 'error' => 'Query is required']);
            break;
        }
        $result = $aiController->scoutMarketData($query);
        echo json_encode(['success' => true, 'data' => $result]);
        break;

    case 'get_forecast':
        $secteur = $_GET['secteur'] ?? '';
        if (empty($secteur)) {
            echo json_encode(['success' => false, 'error' => 'Secteur is required for forecasting']);
            break;
        }
        
        $reports = $veilleC->afficherRapports();
        $historical = [];
        foreach ($reports as $r) {
            // Check if the report belongs to the requested sector
            $reportSecteurs = array_map('trim', explode(',', $r['secteur_principal'] ?? ''));
            if (in_array($secteur, $reportSecteurs) || empty($secteur)) {
                $historical[] = [
                    'date' => $r['date_publication'],
                    'salary' => $r['salaire_moyen_global'],
                    'demand' => $r['niveau_demande_global']
                ];
            }
        }
        
        $forecast = $aiController->generateForecast($historical, $secteur);
        echo json_encode(['success' => true, 'forecast' => $forecast]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
