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
    public function makeAdsArray(Collection $sources, $numberOfAds): array{
        $totalAds = [];
        foreach ($sources as $source) {
            $ads = Repo::getRecords('ads', ['*'], ['source_id' => $source->source_id, 'enable' => 1])->orderByRaw('RAND()');
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
    private function generateAdLinks(LengthAwarePaginator $ads): void{
        foreach ($ads as $ad) {
            (new AdLinkGenerator($ad))->generateLink();
        }
    }

    private function generateAdPicLinks(LengthAwarePaginator $ads){
        foreach ($ads as $ad) {
            (new AdPicGenerator($ad))->generatePicObject();
        }
    }

    /**
     * @param $source
     * @param LengthAwarePaginator $ads
     * @return array
     */
    private function adBlockFormatter($source, LengthAwarePaginator $ads): array{
        return [
            'title' => $source->display_name,
            'color' => 'white',
            'icon' => 'icon',
            'data' => $ads
        ];
    }

}
