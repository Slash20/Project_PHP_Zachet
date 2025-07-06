<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class CurrencyApiController extends AbstractController
{

    #[Route('/currency', name: 'api_currency_list', methods: ['GET'])]
    public function currenciesFromJson(): JsonResponse
    {
        $file = __DIR__ . '/../../var/exchange_rates/exchange_rates_2025-05-09.json';

        if (!file_exists($file)) {
            return $this->json(['error' => 'File not found'], 404);
        }

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $currencies = [];
        foreach ($data as $entry) {
            $currencies[$entry['currency']] = $entry['rate'];
        }

        return $this->json($currencies);
    }

    #[Route('/convert', name: 'api_currency_convert', methods: ['GET'])]
    public function convert(Request $request): JsonResponse
    {
        $from   = $request->query->get('from');
        $to     = $request->query->get('to');
        $amount = $request->query->get('amount');
        $date   = $request->query->get('date');

        if (!$from || !$to || !$amount) {
            return $this->json(['error' => 'Missing parameters. Required: from, to, amount'], 400);
        }

        $directory = __DIR__ . '/../../var/exchange_rates';
        if ($date) {
            $filename = "exchange_rates_{$date}.json";
        } else {
            $files = glob($directory . '/exchange_rates_*.json');
            if (empty($files)) {
                return $this->json(['error' => 'No exchange rate files available'], 404);
            }
            sort($files);
            $filename = basename(end($files));
        }
        $path = "{$directory}/{$filename}";

        if (!file_exists($path)) {
            return $this->json(['error' => 'Exchange rate file not found'], 404);
        }

        $exchangeRates = json_decode(file_get_contents($path), true);
        if (!is_array($exchangeRates)) {
            return $this->json(['error' => 'Error decoding the exchange rate JSON'], 400);
        }
        $rates = [];
        foreach ($exchangeRates as $item) {
            $rates[$item['currency']] = $item['rate'];
        }

        if ($from !== 'EUR' && !isset($rates[$from])) {
            return $this->json(['error' => "Currency rate not found for the \"from\" currency: {$from}"], 400);
        }
        if ($to !== 'EUR' && !isset($rates[$to])) {
            return $this->json(['error' => "Currency rate not found for the \"to\" currency: {$to}"], 400);
        }

        $fromRate = ($from === 'EUR') ? 1.0 : $rates[$from];
        $toRate   = ($to   === 'EUR') ? 1.0 : $rates[$to];
        $result   = ($amount / $fromRate) * $toRate;

        return $this->json([
            'result'    => round($result, 2),
            'from'      => $from,
            'to'        => $to,
            'amount'    => $amount,
            'rate_from' => $fromRate,
            'rate_to'   => $toRate,
        ]);
    }
}
