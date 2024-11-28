<?php

namespace App\Entity;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelTransport
{
    private ?int $id = null;
    public Worksheet $workSheet;
    public Spreadsheet $spreadsheet;
    private int $nbSheet = 1;
    private float $taxe_rate = 0.08;

    public function __construct(string $title = ""){
        // $new ? $this->spreadsheet= new Spreadsheet() : $this->spreadsheet = IOFactory::load($filePath);
        $this->spreadsheet= new Spreadsheet();
        $this->workSheet = $this->spreadsheet->getActiveSheet();
        $this->workSheet->setTitle($title);
        $this->setHeaders();
    }

    public function setHeaders() {
        $this->workSheet->setCellValue('A1','nom_item');
        $this->workSheet->getColumnDimension('A')->setWidth(27);
        $this->workSheet->setCellValue('B1','quantité');
        $this->workSheet->getColumnDimension('B')->setWidth(8);
        $this->workSheet->setCellValue('C1','benef/U ordre');
        $this->workSheet->getColumnDimension('C')->setWidth(13);
        $this->workSheet->setCellValue('D1','benef/Kg ordre');
        $this->workSheet->getColumnDimension('D')->setWidth(15);
        $this->workSheet->setCellValue('E1','prix_ordre_achat');
        $this->workSheet->getColumnDimension('E')->setWidth(16);
        $this->workSheet->setCellValue('F1','prix_ordre_vente');
        $this->workSheet->getColumnDimension('F')->setWidth(16);
        $this->workSheet->setCellValue('G1','benef/U');
        $this->workSheet->getColumnDimension('G')->setWidth(9);
        $this->workSheet->setCellValue('H1','benef/Kg');
        $this->workSheet->getColumnDimension('H')->setWidth(9);
        $this->workSheet->setCellValue('I1','prix_achat_direct');
        $this->workSheet->getColumnDimension('I')->setWidth(17);
        $this->workSheet->setCellValue('J1','prix_vente_direct');
        $this->workSheet->getColumnDimension('J')->setWidth(17);
    }

    public function setValuesSheet(string $sheetName, array $items){
        $this->workSheet = $this->spreadsheet->setActiveSheetIndexByName($sheetName);
        foreach(array_keys($items) as $index => $itemName) {
            $item = $items[$itemName];
            $locations = str_replace(' ','',preg_split('/to/',$sheetName));
            $start =  $locations[0];
            $end = $locations[1];
            $end=="BlackMarket" ? $end = "Black Market": null;
            isset($item[$end]['quantity']) ? $quantity = $item[$end]['quantity'] : $quantity = 0; 
            isset($item[$end]['avgSellPrice']) ? $orderSellPrice = $item[$end]['avgSellPrice'] : $orderSellPrice = 0;
            $priceBuyOrder = $item[$start]['orderBuyPrice'];
            $priceBuy = $item[$start]['sellPrice'];
            isset($item[$end]['sellPrice']) && !empty($item[$end]['sellPrice']) ? $sellPrice = $item[$end]['sellPrice'] : $sellPrice = 0;
            $formuleBenefUOrdre = '=F'.($index+2).'*'. (1-$this->taxe_rate).'-E'.($index+2).'*1.025';
            $formuleBenefArrache = '=J'.($index+2).'*'.(1-$this->taxe_rate).'-I'.($index+2);
            $this->workSheet->setCellValue('A'.$index+2,$itemName);
            $this->workSheet->setCellValue('B'.$index+2,$quantity);
            $this->workSheet->setCellValue('C'.$index+2,$formuleBenefUOrdre);
            $this->workSheet->setCellValue('D'.$index+2,0);
            $this->workSheet->setCellValue('E'.$index+2,$priceBuyOrder);
            $this->workSheet->setCellValue('F'.$index+2,$orderSellPrice);
            $this->workSheet->setCellValue('G'.$index+2,$formuleBenefArrache);
            $this->workSheet->setCellValue('H'.$index+2,0);
            $this->workSheet->setCellValue('I'.$index+2,$priceBuy);
            $this->workSheet->setCellValue('J'.$index+2,$sellPrice);

            // coloration des cases si il y a un manque de données
            if ( $priceBuyOrder == 0){
                $this->workSheet->getStyle('C'.$index+2)->getFont()->getColor()->setARGB('FFFF0000');
                $this->workSheet->getStyle('E'.$index+2)->getFont()->getColor()->setARGB('FFFF0000');
            }
            elseif($orderSellPrice == 0) {
                $this->workSheet->getStyle('C'.$index+2)->getFont()->getColor()->setARGB('FFFF0000');
                $this->workSheet->getStyle('F'.$index+2)->getFont()->getColor()->setARGB('FFFF0000');              
            }
            else {
                $this->workSheet->getStyle('C'.$index+2)->getFont()->getColor()->setARGB('FF0000FF');
            }

            if ( $priceBuy == 0){
                $this->workSheet->getStyle('G'.$index+2)->getFont()->getColor()->setARGB('FFFF0000');
                $this->workSheet->getStyle('I'.$index+2)->getFont()->getColor()->setARGB('FFFF0000');
            }
            elseif($sellPrice == 0) {
                $this->workSheet->getStyle('G'.$index+2)->getFont()->getColor()->setARGB('FFFF0000');
                $this->workSheet->getStyle('J'.$index+2)->getFont()->getColor()->setARGB('FFFF0000');
            }
            else {
                $this->workSheet->getStyle('G'.$index+2)->getFont()->getColor()->setARGB('FF0000FF');
            }          
        }
    }

    public function createSheet(string $name) {    
        $this->spreadsheet->createSheet($this->nbSheet);
        $this->workSheet = $this->spreadsheet->setActiveSheetIndex($this->nbSheet);
        $this->workSheet->setTitle($name);
        $this->nbSheet+=1;
    }

    public function createSheets(array $names) {
        foreach($names as $i => $name) {
            if ($i==0) {continue;}
            $this->createSheet($name);
            $this->setHeaders();
        }
    }

    public function save(string $path){
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save($path);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
