<?php
namespace App\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index()
    {
        $client = new Client();

        $urls = [
            'england' => 'https://api-football-v1.p.rapidapi.com/v3/standings?season=2023&league=39',
            'france' => 'https://api-football-v1.p.rapidapi.com/v3/standings?season=2023&league=61',
            'germany' => 'https://api-football-v1.p.rapidapi.com/v3/standings?season=2023&league=78',
            'spain' => 'https://api-football-v1.p.rapidapi.com/v3/standings?season=2023&league=140',
            'italy' => 'https://api-football-v1.p.rapidapi.com/v3/standings?season=2023&league=135',
        ];

        
        $subFolderName = 'api';
        // Enregistrer le fichier JSON en local
        $publicDirectory = getcwd();
        
        // Vérifiez si le sous-dossier existe dejà
        if (!file_exists($publicDirectory . '/' . $subFolderName)) {
            // Créez le sous-dossier
            mkdir($publicDirectory . '/' . $subFolderName, 0777, true);
            
        }
        // Modifiez la variable $publicDirectory pour inclure le nom du sous-dossier
        $publicDirectory = $publicDirectory . '/' . $subFolderName;


        $fileNames = [];

        foreach ($urls as $key => $url) {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-RapidAPI-Host' => 'api-football-v1.p.rapidapi.com',
                    'X-RapidAPI-Key' => 'a4284bf981msh13ebeb55888f019p113f7djsnf6fd41c5c633',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            
            // generate a name for each country
            $fileName = 'data-' . $key . '.json';
            
            file_put_contents($publicDirectory . '/' . $fileName, json_encode($data));
            
            $fileNames[] = $fileName;

        }

        $data = ['message' => 'jsons enregistrer avec success'];

        return new JsonResponse($data, 200, ['Content-Type' => 'application/json']);

    }
}


