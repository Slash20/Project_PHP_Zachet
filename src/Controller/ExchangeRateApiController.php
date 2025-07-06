<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/files')]
class ExchangeRateApiController extends AbstractController
{
    private string $directory = __DIR__ . '/../../var/exchange_rates';

    #[Route('', name: 'api_file_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $files = glob($this->directory . '/exchange_rates_*.json');
        $dates = array_map(function ($file) {
            return basename($file, '.json');
        }, $files);

        return $this->json($dates);
    }

    #[Route('/{date}', name: 'api_file_get', methods: ['GET'])]
    public function getByDate(string $date): JsonResponse
    {
        $path = "{$this->directory}/exchange_rates_{$date}.json";

        if (!file_exists($path)) {
            return $this->json(['error' => 'File not found'], 404);
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        return $this->json($data);
    }

    #[Route('/{date}', name: 'api_file_update', methods: ['PUT'])]
    public function update(string $date, Request $request): JsonResponse
    {
        $path = "{$this->directory}/exchange_rates_{$date}.json";

        if (!file_exists($path)) {
            return $this->json(['error' => 'File not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $this->json(['success' => true]);
    }

    #[Route('/{date}', name: 'api_file_delete', methods: ['DELETE'])]
    public function delete(string $date): JsonResponse
    {
        $path = "{$this->directory}/exchange_rates_{$date}.json";

        if (!file_exists($path)) {
            return $this->json(['error' => 'File not found'], 404);
        }

        unlink($path);

        return $this->json(['success' => true]);
    }

}


