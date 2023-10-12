<?php

namespace App\Service;

use App\Enums\CurrenciesEnum;
use App\Exception\CbrException;
use App\Service\Contract\CbrContract;
use DateInterval;
use DatePeriod;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CbrService implements CbrContract
{
    public function __construct(private readonly HttpClientInterface $client) {
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(DateTime $dateStart, DateTime $dateEnd): array
    {
        $result = [];
        $period = new DatePeriod($dateStart, new DateInterval('P1D'), $dateEnd, DatePeriod::INCLUDE_END_DATE);

        foreach (CurrenciesEnum::cases() as $case) {
            $params = [
                'date_req1' => $dateStart->format('d/m/Y'),
                'date_req2' => $dateEnd->format('d/m/Y'),
                'VAL_NM_RQ' => $case->value,
            ];

            $response = $this->exec(self::API_METHOD_DYNAMIC, $params);

            foreach ($response->Record as $record) {
                $date = (string) $record->attributes()->Date;
                $result[$case->name][$date] = (string) $record->Value;
            }

            if (empty($result)) {
                return $result;
            }

            foreach ($period as $day) {
                $date = $day->format('d.m.Y');
                if (array_key_exists($date, $result[$case->name])) {
                    $lastFilledValue = $result[$case->name][$date];
                } elseif (isset($lastFilledValue)) {
                    $result[$case->name][$date] = $lastFilledValue;
                }
            }

            uksort($result[$case->name], function (string $date1, string $date2) {
                return strtotime($date1) - strtotime($date2);
            });
        }

        return $result;
    }

    /**
     * Execute API request
     *
     * @param string $method
     * @param array $params
     *
     * @return mixed
     * @throws CbrException
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function exec(string $method, array $params = []): mixed
    {
        $url = sprintf('%s%s.asp', self::API_HOST, $method);

        if (!empty($params)) {
            $param = array_key_first($params);
            $value = array_shift($params);

            $url .= sprintf('?%s=%s', $param, $value);

            foreach ($params as $param => $value) {
                $url .= sprintf('&%s=%s', $param, $value);
            }
        }

        $response = $this->client->request('GET', $url);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new CbrException('Ошибка при выполнении запроса к Cbr');
        }

        $xml = $response->getContent();

        return simplexml_load_string($xml);
    }
}
