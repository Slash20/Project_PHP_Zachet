<?php

// app/src/Controller/CurrencyApiController.php

namespace App\Controller;

use App\Entity\ExchangeRate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class CurrencyApiController extends AbstractController
{
    #[Route('/currency', name: 'api_currency_list', methods: ['GET'])]
    public function currencies(EntityManagerInterface $em): JsonResponse
    {
        $rates = $em->getRepository(ExchangeRate::class)->findAll();

        $data = [];
        foreach ($rates as $rate) {
            $data[$rate->getCurrency()] = $rate->getRate();
        }

        return $this->json($data);
    }

    #[Route('/convert', name: 'api_currency_convert', methods: ['GET'])]
public function convert(Request $request): JsonResponse
{
    $from   = $request->query->get('from');
    $to     = $request->query->get('to');
    $amount = $request->query->get('amount');
    $date   = $request->query->get('date'); // может быть null

    if (!$from || !$to || !$amount) {
        return $this->json(['error' => 'Missing parameters. Required: from, to, amount'], 400);
    }

    // Определяем путь к JSON
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

    // Читаем и мапим валюты в ключи массива
    $exchangeRates = json_decode(file_get_contents($path), true);
    if (!is_array($exchangeRates)) {
        return $this->json(['error' => 'Error decoding the exchange rate JSON'], 400);
    }
    $rates = [];
    foreach ($exchangeRates as $item) {
        $rates[$item['currency']] = $item['rate'];
    }

    // Проверяем, что нужные валюты есть
    if ($from !== 'EUR' && !isset($rates[$from])) {
        return $this->json(['error' => "Currency rate not found for the \"from\" currency: {$from}"], 400);
    }
    if ($to !== 'EUR' && !isset($rates[$to])) {
        return $this->json(['error' => "Currency rate not found for the \"to\" currency: {$to}"], 400);
    }

    // Конвертация
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
