<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\{WithHeadings,WithTitle,WithCustomValueBinder,WithStyles,WithEvents};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\{DefaultValueBinder,Cell,DataType};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
abstract class BaseSheet extends DefaultValueBinder implements WithHeadings,WithTitle,WithCustomValueBinder,WithStyles,WithEvents {
 public function bindValue(Cell $cell,$value){if(is_string($value)){$cell->setValueExplicit($value,DataType::TYPE_STRING);return true;}return parent::bindValue($cell,$value);}
 public function styles(Worksheet $sheet){return [1=>['font'=>['bold'=>true],'fill'=>['fillType'=>'solid','startColor'=>['rgb'=>'F1F5F9']]]];}
 public function registerEvents():array{return [AfterSheet::class=>function(AfterSheet $e){$s=$e->sheet->getDelegate();$s->freezePane('D2');$s->setAutoFilter($s->calculateWorksheetDimension());$s->getDefaultColumnDimension()->setWidth(20);$s->getColumnDimension('A')->setWidth(10);}];}
}
