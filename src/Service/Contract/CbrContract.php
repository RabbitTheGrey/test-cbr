<?php

namespace App\Service\Contract;

use App\Exception\CbrException;
use DateTime;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

interface CbrContract
{
    public const API_HOST = 'http://www.cbr.ru/scripts/';
    public const API_METHOD_DYNAMIC = 'XML_dynamic';

    /**
     * Получение курсов валют с Cbr за выбранный период
     *
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     *
     * @return array
     * @throws CbrException
     * @throws TransportExceptionInterface
     */
    public function getCurrency(DateTime $dateStart, DateTime $dateEnd): array;
}
