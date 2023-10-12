<?php

namespace App\Service\Cache;

use AllowDynamicProperties;
use App\Service\CbrService;
use App\Service\Contract\CbrContract;
use DateTime;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

#[AllowDynamicProperties] class CbrCacheService implements CbrContract
{
    public function __construct(private readonly CbrService $service) {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(DateTime $dateStart, DateTime $dateEnd): array
    {
        $key = sprintf('cbr_%s_%s', $dateStart->format('d-m-Y'), $dateEnd->format('d-m-Y'));
        $cachedCurrencies = $this->cache->getItem($key);

        if (!$cachedCurrencies->isHit()) {
            $cachedCurrencies->set($this->service->getCurrency($dateStart, $dateEnd));
            $cachedCurrencies->expiresAfter(3600);
            $this->cache->save($cachedCurrencies);
        }

        return $cachedCurrencies->get();
    }
}
