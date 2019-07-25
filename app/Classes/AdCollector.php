<?php

namespace App\Classes;

use App\Repositories\Repo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdCollector
{
    /**
     * @param AdLinkGenerator $adLinkGenerator
     * @param Collection $sources
     * @param $numberOfAds
     * @return array
     */
    public function makeAdsArray(Collection $sources, $numberOfAds): array
    {
        $totalAds = [];
        foreach ($sources as $source) {
            $ads = Repo::getRecords('ads', ['UUID', 'name', 'link', 'image'], ['source_id' => $source->id, 'enable' => 1]);
            $ads = $ads->paginate($numberOfAds, ['*'], 'page');
            $this->generateAdLinks($ads);
            $this->generateAdPicLinks($ads);
            $totalAds[] = $this->adBlockFormatter($source, $ads);
        }
        return $totalAds;
    }

    /**
     * @param LengthAwarePaginator $ads
     */
    private function generateAdLinks(LengthAwarePaginator $ads): void
    {
        foreach ($ads as $ad) {
            // ToDo : Refactor
            $adLinkGenerator = new AdLinkGenerator($ad);
            $adLinkGenerator->generateLink();
        }
    }

    private function generateAdPicLinks(LengthAwarePaginator $ads)
    {
        foreach ($ads as $ad) {
            // ToDo : Refactor
            $adLinkGenerator = new AdPicLinkGenerator($ad);
            $adLinkGenerator->generatePicLink();
        }
    }

    /**
     * @param $source
     * @param LengthAwarePaginator $ads
     * @return array
     */
    private function adBlockFormatter($source, LengthAwarePaginator $ads): array
    {
        return [
            'title' => $source->display_name,
            'color' => 'white',
            'icon' => 'icon',
            'data' => $ads
        ];
    }

}
