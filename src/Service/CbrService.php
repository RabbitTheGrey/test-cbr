<?php

namespace App\Service;

use App\Enums\CurrenciesEnum;
use App\Exception\CbrException;
use App\Service\Contract\CbrContract;
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

        foreach (CurrenciesEnum::cases() as $case) {
            $params = [
                'date_req1' => $dateStart->format('d/m/Y'),
                'date_req2' => $dateEnd->format('d/m/Y'),
                'VAL_NM_RQ' => $case->value,
            ];

            $result[$case->name] = $this->exec(self::API_METHOD_DYNAMIC, $params);
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

        // очень оригинально, но решает проблемы и с кодировкой, и с рекусивным обходом xml
        return json_decode(json_encode(simplexml_load_string($xml)));
    }
}
