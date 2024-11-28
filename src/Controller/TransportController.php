<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\ExcelTransport as Excel;
use DateInterval;
use DateTime;

class TransportController extends AbstractController
{
    const FILEPATH = "../result.xlsx";
    const BASEURL = "https://europe.albion-online-data.com/api/v2";
    const LOCATIONS = [
        "Lymhurst",
         "Black Market",
         "Caerleon",
         //"Bridgewatch",
         //"Martlock",
         //"Thetford",
         //"FortSterling"
        ];
    const  WAY = ['Lymhurst to Caerleon', 'Caerleon to Lymhurst', 'Lymhurst to Black Market'];   
    const LISTFOOD = [
        'sandwich' => '4sandwich',
        'sandwich ava' => '4sandwich avalon',
        'ragout' => '4stew',
        'ragout ava' => '4stew avalon',
        // 'deadwater eel stew'=> 'FISH_FRESHWATER_FOREST_RARE',
        // 'thunderfall Sandwich'=> 'FISH_FRESHWATER_HIGHLANDS_RARE',
        'omellette' => '3omelette',
        'omellette ava' => '3omelette avalon',
        'crab omelette' => '3omelette fish',
        'pie' => '3pie',
        'frostpeak pie' => '3PIE FISH',
        'roast' => '3ROAST',
        'roasted snapper' => '3ROAST_FISH',
        'salade' => '4salad',
        'deepwater salade' => '4salad fish',
        'soup' => '3soup',
        'clam soup' => '3soup_fish'
    ];
    const LISTSTUFF = [
        'plate' => ['tiers' => [4, 5, 6, 7, 8], 'items' => ['shoes', 'armor', 'head'], 'sets' => [1, 2, 3]],
        'leather' => ['tiers' => [5, 6, 7, 8], 'items' => ['shoes', 'armor', 'head'], 'sets' => [1, 2, 3]],
        'tissu' => ['tiers' => [4, 5, 6, 7, 8], 'items' => ['shoes', 'armor', 'head'], 'sets' => [1, 2, 3]]
    ];

    public function __construct(private HttpClientInterface $client)
    {
    }

    #[Route('/transport', name: 'app_transport')]
    public function index(): JsonResponse
    {
        // food
        $url = self::BASEURL.'/stats/prices/'.$this->formatItemsUrl(self::LISTFOOD, "food").".json?locations=".$this->formatlocationsUrl(true). "&qualities=1";
        $responseFood = json_decode($this->client->request('GET',$url)->getContent(),true);
        $foodItems = $this->formatResponse($responseFood, 'food');
        // historique
        $date = new DateTime('now');
        $interval = new DateInterval('P1D');
        $url = self::BASEURL .'/stats/history/'.$this->formatItemsUrl(self::LISTFOOD, 'food') .'?date='.$date->sub($interval)->format('n-j-Y').'&end_date='.$date->format('n-j-Y').'&locations='. $this->formatlocationsUrl(false) . "&qualities=1&time-scale=3";
        $historyFood = json_decode($this->client->request('GET',$url)->getContent(),true);
        $foodItems = $this->addHistoryPrices($historyFood, $foodItems);

        // stuff
        $url = self::BASEURL .'/stats/prices/'. $this->formatItemsUrl(self::LISTSTUFF, "stuff") . ".json?locations=" . $this->formatlocationsUrl(false) . "&qualities=1,2,3,4";
        $responseStuff = json_decode($this->client->request('GET',$url)->getContent(),true);
        $stuffItems = $this->formatResponse($responseStuff, 'stuff');
        // historique
        $date = new DateTime('now');
        $interval = new DateInterval('P1D');
        $url = self::BASEURL .'/stats/history/'.$this->formatItemsUrl(self::LISTSTUFF, 'stuff') .'?date='.$date->sub($interval)->format('n-j-Y').'&end_date='.$date->format('n-j-Y').'&locations='. $this->formatlocationsUrl(false) . "&qualities=1,2,3,4&time-scale=3";
        $historyStuff = json_decode($this->client->request('GET',$url)->getContent(),true);
        $stuffItems = $this->addHistoryPrices($historyStuff, $stuffItems);

        $items = array_merge($stuffItems, $foodItems);

        // création tableur
        $sheetNames = ['Lymhurst to Caerleon', 'Caerleon to Lymhurst', 'Lymhurst to Black Market'];
        $tableur = new Excel(self::FILEPATH, 'Lymhurst to Caerleon');
        $tableur->createSheets($sheetNames);

        // affectation des valeurs dans les colonnes
        foreach($sheetNames as $sheetName)
        {
            $tableur->setValuesSheet($sheetName, $items);
        }

        $tableur->save(self::FILEPATH);

        return $this->json([
            'message' => 'Welcome to your new controller!'
        ]);
    }

    public function formatItemsUrl(array $listItems, string $typeItems)
    {
        $res = "";
        switch ($typeItems) {
            case "food":
                foreach (array_keys($listItems) as $itemName) {
                    $name = substr($listItems[$itemName], 1, strlen($listItems[$itemName]));
                    $tiers = substr($listItems[$itemName], 0, 1);
                    $res .= $this->formatFoodNamesUrl($name, $tiers) . ',';
                }
                return substr($res, 0, -1);

            case "stuff":
                foreach (array_keys($listItems) as $keyTypeStuff) {
                    $typeStuff = $listItems[$keyTypeStuff];
                    foreach ($typeStuff['items'] as $partStuff) {
                        // $res .= "T2_" . $partStuff . "_" . $keyTypeStuff . "_SET1,";
                        // $res .= "T3_" . $partStuff . "_" . $keyTypeStuff . "_SET1,";
                        foreach ($typeStuff['tiers'] as $tier) {
                            foreach ($typeStuff['sets'] as $set) {
                                $res .= "T" . $tier . "_" . $partStuff . "_" . $keyTypeStuff . "_SET" . $set . ",";
                            }
                        }
                    }
                }
                return substr($res, 0, -1);
        }
    }

    public function formatFoodNamesUrl(string $itemName, int $tierMin)
    {
        $res = "";
        $formattedName = trim(strtoupper(str_replace(' ', '_', $itemName)));
        $res .= 'T' . $tierMin . '_' . 'MEAL_' . $formattedName . ',';
        $res .= 'T' . ($tierMin + 2) . '_' . 'MEAL_' . $formattedName . ',';
        $res .= 'T' . ($tierMin + 4) . '_' . 'MEAL_' . $formattedName;
        return $res;
    }

    public function formatlocationsUrl(bool $food)
    {
        $res = "";
        foreach (self::LOCATIONS as $location) {
            if($location=="Black Market") {
                if ($food) {
                    continue;
                }
                $location = str_replace(' ','',$location);
            }
            $res .= $location . ',';
        }
        return substr($res, 0, -1);
    }

    //
    // $items = [
        //     'nomItem'=>[
        //         'nomVille'=>['sellPrice','orderBuyPrice','quantity','avgSellPrice']
        //     ]
        // ];
    public function formatResponse($response, string $typeItems) {
        $items = [];
        switch ($typeItems) {
            case 'stuff':
                foreach($response as $responseItem) {
                    $name = $responseItem['item_id'].'q'.$responseItem['quality'];
                    $location = $responseItem['city'];
                    $items[$name][$location]['sellPrice']= $responseItem['sell_price_min'];
                    $items[$name][$location]['orderBuyPrice']= $responseItem['buy_price_max'];
                }
                break;
            case 'food':
                foreach($response as $responseItem) {
                    $name = $responseItem['item_id'];
                    $location = $responseItem['city'];
                    $items[$name][$location]['sellPrice']= $responseItem['sell_price_min'];
                    $items[$name][$location]['orderBuyPrice']= $responseItem['buy_price_max'];
                }
                break;    
        }
        
        return $items;
    }

    public function addHistoryPrices(array $historique, array $items) {
        foreach($historique as $itemHistorique) {
            if ($itemHistorique['location'] == "Black Market" && preg_match('_MEAL_',$itemHistorique['item_id'])){ continue;}
            $datas = $itemHistorique['data'];
            $quantity = 0;
            $avgSellPrice = 0;
            foreach($datas as $data) {
                $quantity+= $data['item_count'];
                $avgSellPrice+= $data['avg_price']*$data['item_count'];    
            }
            preg_match('_MEAL_',$itemHistorique['item_id']) ? $itemName = $itemHistorique['item_id'] : $itemName = $itemHistorique['item_id']."q".$itemHistorique['quality'];
            $items[$itemName][$itemHistorique['location']]['quantity'] = $quantity;
            $items[$itemName][$itemHistorique['location']]['avgSellPrice'] = $avgSellPrice/$quantity;
        }

        return $items;
    }
}
