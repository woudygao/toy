<?php

require_once 'require.php';

/**
 * Created by PhpStorm.
 * @author: gaoweikang
 * @date: 16/2/12
 * Time: 下午9:41
 */

class fileParse {

    public $execl;
    public $index = 0;

    public function outputSample() {
        $sudoku = new sudokumap();
        $sudoku->initSampleMap();
        $sudoku->output();
    }

    public function basicRead($filePath) {
        $arrLines = file($filePath);
        $sudoku = new sudokumap();
        $sudoku->inputFromArray($arrLines);
        return $sudoku;
    }

    public function readFromExcel($filePath) {
        $excel = PHPExcel_IOFactory::load($filePath);
        $sheet = $excel->getSheet(0);

        $values = array();

        $arrRow = range(1, 9);
        $arrColume = range(1, 9);
        $xlsxColumn = explode(',', 'A,B,C,D,E,F,G,H,I');

        foreach ($arrRow as $i) {
            foreach ($arrColume as $j) {
                $xlsxValue = $sheet->getCell($xlsxColumn[$j - 1] . $i)->getValue();
                $values[$i][$j] = strval($xlsxValue);
            }
        }

        $sudoku = new sudokumap();
        $sudoku->inputFromXlsxArr($values);

        return $sudoku;
    }

    public function writeToXlsx(sudokumap &$sudoku, $name) {

        $result = $sudoku->outputToArray();

        $fontType = array(
            'name'        => 'SimSun (正文)',
            'bold'        => TRUE,
            'italic'    => FALSE,
            'size' => 24,
             );

        if (81 == $sudoku->getDefined()) {
            $fontType['size'] = 30;
        }

        if (null == $this->execl) {
            $execl = new PHPExcel();
            $this->execl = $execl;
        }

        $sheet = new PHPExcel_Worksheet($this->execl);
        $sheet->setTitle($name);
        $this->execl->addSheet($sheet, $this->index);
        $this->index ++;

        $xlsxColumn = explode(',', 'A,B,C,D,E,F,G,H,I');

        $arrRow = range(1, 9);
        $arrColume = range(1, 9);

        foreach ($arrRow as $i) {
            foreach ($arrColume as $j) {
                $p = $xlsxColumn[$j - 1] . $i;
                $sheet->setCellValue($p, $result[$i][$j]);
                $sheet->getColumnDimension($xlsxColumn[$j - 1])->setWidth(5);
                $sheet->getColumnDimension($xlsxColumn[$j - 1])->setAutoSize(true);
//                $sheet->getCell($p)->getStyle()->getAlignment()->setHorizontal();
                $sheet->getCell($p)->getStyle()->getFont()->applyFromArray($fontType);
            }
        }

    }

    public function dumpToXlsx($filePath) {
        if (! null == $this->execl) {
            $objWriter = PHPExcel_IOFactory::createWriter($this->execl, 'Excel2007');
            $objWriter->save($filePath);
        }
    }
}
