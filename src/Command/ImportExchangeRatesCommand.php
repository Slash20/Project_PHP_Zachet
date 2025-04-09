<?php

namespace App\Command;

use App\Entity\ExchangeRate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-exchange-rates',
    description: 'Импортирует курсы валют из ECB XML и создает JSON файл'
)]
class ImportExchangeRatesCommand extends Command
{
    private HttpClientInterface $client;
    private EntityManagerInterface $em;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->client = $client;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

        $output->writeln('Загружаем XML...');
        try {
            $response = $this->client->request('GET', $url);
            $xmlString = $response->getContent();

            $xml = simplexml_load_string($xmlString);
            if (!$xml) {
                throw new \Exception("Не удалось загрузить XML");
            }

            $xml->registerXPathNamespace('e', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');
            $cubes = $xml->xpath('//e:Cube/e:Cube');

            if (!$cubes || !isset($cubes[0])) {
                throw new \Exception("Курсы валют не найдены.");
            }

            $date = new \DateTime((string)$cubes[0]['time']);
            $output->writeln('Дата: ' . $date->format('Y-m-d'));

            $count = 0;
            $exchangeRates = [];

            $output->writeln("Валюты в XML:");
            foreach ($cubes[0]->Cube as $cube) {
                $currency = (string)$cube['currency'];
                $rate = (float)$cube['rate'];
                $output->writeln("Валюта: $currency, Курс: $rate");
            }

            foreach ($cubes[0]->Cube as $cube) {
                $currency = (string)$cube['currency'];
                $rate = (float)$cube['rate'];

                $existing = $this->em->getRepository(ExchangeRate::class)->findOneBy([
                    'currency' => $currency,
                    'date' => $date
                ]);

                if ($existing) {
                    $output->writeln("Запись для валюты $currency уже существует.");
                } else {
                    $exchangeRate = new ExchangeRate();
                    $exchangeRate->setCurrency($currency);
                    $exchangeRate->setRate($rate);
                    $exchangeRate->setDate($date);

                    $this->em->persist($exchangeRate);
                    $count++;
                }

                
                $exchangeRates[] = [
                    'currency' => $currency,
                    'rate' => $rate,
                    'date' => $date->format('Y-m-d'),
                ];
            }

            $this->em->flush();

            if (empty($exchangeRates)) {
                $output->writeln('Ошибка: Массив с курсами валют пуст.');
            } else {
                $jsonFile = 'exchange_rates_' . $date->format('Y-m-d') . '.json';
                file_put_contents($jsonFile, json_encode($exchangeRates, JSON_PRETTY_PRINT));

                $output->writeln("Импортировано: $count курсов валют.");
                $output->writeln("JSON файл сохранён как: $jsonFile");
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $output->writeln('Ошибка: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
