<?php

require_once 'require.php';

/**
 * Created by PhpStorm.
 * @author: woudygao
 * @date: 16/2/12
 * Time: 下午9:30
 */

class sudokumap {

    const separator = ", ";

    // 9 x 9 map,  行列的序号都是 1~9
    public $arrBlocks = null;

    public function addToBlock($i, $j, $v) {
        $this->arrBlocks[$i][$j][$v] = true;
    }

    public function defineBlock($i, $j, $v) {
        //先设置为空
        $this->arrBlocks[$i][$j] = array();
        //然后更正值
        $this->arrBlocks[$i][$j][$v] = true;
    }

    public function removeFromBlock($i, $j, $v){
        unset($this->arrBlocks[$i][$j][$v]);
    }

    //用于二元或者多元的消除
    public function removeFromBlockBatch($i, $j, $arrValue) {
        foreach ($arrValue as $v) {
            $this->removeFromBlock($i, $j, $v);
        }
    }

    public function countInBlock($i, $j) {
        return count($this->arrBlocks[$i][$j]);
    }

    //查看是否是二元链，对称的两个九宫格
    public function twoEqual($i1, $j1, $i2, $j2) {
        $value1 = array_keys($this->arrBlocks[$i1][$j1]);
        $value2 = array_keys($this->arrBlocks[$i2][$j2]);

        if (2 == count($value1) && (2 == count($value2))) {
            $allKeys = array();
            foreach ($value1 as $v) {
                $allKeys[$v] = true;
            }
            foreach ($value2 as $v) {
                $allKeys[$v] = true;
            }

            if (2 == count($allKeys)) {
                return true;
            }
        }
        return false;
    }

    public function initFullBlock($i, $j) {
        //值的取值范围为 1~9
        for ($v = 1; $v < 10; $v ++) {
            $this->arrBlocks[$i][$j][$v] = true;
        }
    }

    public function getDefined() {
        $arrRow = range(1, 9);
        $count = 0;
        $arrColume = range(1, 9);
        foreach ($arrRow as $i) {
            foreach ($arrColume as $j) {
                if (! $this->isBlockMuti($i, $j)) {
                    $count ++;
                }
            }
        }

        return $count;
    }

    public function isBlockEmpty($i, $j) {
        return empty($this->arrBlocks[$i][$j]);
    }

    public function isBlockMuti($i, $j) {
        return 1 < count($this->arrBlocks[$i][$j]);
    }

    public function initSampleMap() {
        for ($i = 1; $i < 10; $i ++) {
            for ($j = 1; $j < 10; $j ++) {
                $this->arrBlocks[$i][$j] = array();
            }
        }
    }

    public function output() {

        for ($i = 1; $i < 10; $i ++) {
            for ($j = 1; $j < 10; $j ++) {
                $block = $this->arrBlocks[$i][$j];
                $vs = array_keys($block);
                $vs = json_encode($vs);
                echo $vs;
                if ($j != 9) {
                    echo self::separator;
                }
                else {
                    echo PHP_EOL;
                }
            }
        }
    }

    public function outputToArray() {
        $arrRow = range(1, 9);
        $arrColume = range(1, 9);

        foreach ($arrRow as $i) {
            foreach ($arrColume as $j) {
                $values = $this->arrBlocks[$i][$j];
                $values = array_keys($values);
                $str = implode(',', $values);
                $result[$i][$j] = $str;
            }
        }
        return $result;
    }

    public function inputFromArray($arrLine) {
        $i = 1;
        foreach ($arrLine as $line) {

            $line = trim($line);
            $row = explode(self::separator, $line);

            $j = 1;
            foreach ($row as $strValue) {
                $strValue = trim($strValue);
//                var_dump($strValue);
                $arrValue = json_decode($strValue, true);
//                var_dump($arrValue);
                if (is_array($arrValue) && !empty($arrValue)) {
                    foreach ($arrValue as $v) {
                        $this->addToBlock($i, $j, $v);
                    }
                }
                else {
                    $this->initFullBlock($i, $j);
//                    $this->arrBlocks[$i][$j] = array();
                }
                $j ++;
            }
//            die();
            $i ++;
        }
    }

    public function inputFromXlsxArr($values) {
        $arrRow = range(1, 9);
        $arrColume = range(1, 9);
        foreach ($arrRow as $i) {
            foreach ($arrColume as $j) {
                $v = $values[$i][$j];
                if (! empty($v)) {
                    $this->addToBlock($i, $j, $v);
                }
                else {
                    $this->initFullBlock($i, $j);
//                    $this->arrBlocks[$i][$j] = array();
                }
            }
        }
    }
}