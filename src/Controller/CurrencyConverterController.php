<?php

namespace App\Controller;

use App\Entity\ExchangeRate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyConverterController extends AbstractController
{
    #[Route('/api/convert', name: 'api_currency_convert', methods: ['GET'])]
    public function convert(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $from = strtoupper($request->query->get('from'));
        $to = strtoupper($request->query->get('to'));
        $dateString = $request->query->get('date');

        if (!$from || !$to || !$dateString) {
            return $this->json(['error' => 'Missing parameters. Required: from, to, date'], 400);
        }

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], 400);
        }

        if ($from === $to) {
            return $this->json(['rate' => 1.0]);
        }

        $fromRate = $em->getRepository(ExchangeRate::class)->findOneBy(['currency' => $from, 'date' => $date]);
        $toRate = $em->getRepository(ExchangeRate::class)->findOneBy(['currency' => $to, 'date' => $date]);

        if (!$fromRate || !$toRate) {
            return $this->json(['error' => 'Currency rate not found for one or both currencies'], 404);
        }

        // Все курсы относительно EUR, считаем кросс-курс:
        // from -> EUR = 1 / fromRate
        // EUR -> to = toRate
        // from -> to = (1 / fromRate) * toRate = toRate / fromRate
        $rate = $toRate->getRate() / $fromRate->getRate();

        return $this->json([
            'from' => $from,
            'to' => $to,
            'date' => $date->format('Y-m-d'),
            'rate' => round($rate, 6),
        ]);
    }
}
