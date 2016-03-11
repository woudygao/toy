<?php

require_once 'require.php';

/**
 * Created by PhpStorm.
 * @author: woudygao
 * @date: 16/2/12
 * Time: 下午9:30
 */

class sudokumap {

    private $isimprove = false; //标志位，是否取得进步，包括进一步削减候选项，或者确定位置的值
    const separator = ", ";

    // 9 x 9 map,  行列的序号都是 1~9
    public $arrBlocks = array();

    public function addToBlock($i, $j, $v) {
        $this->arrBlocks[$i][$j][$v] = true;
    }

    public function defineBlock($i, $j, $v) {
        //先设置为空
        $this->arrBlocks[$i][$j] = array();
        //然后更正值
        $this->arrBlocks[$i][$j][$v] = true;
        $this->isimprove = true;
    }

    public function removeFromBlock($i, $j, $v){
        if (array_key_exists($v, $this->arrBlocks[$i][$j])) {
            unset($this->arrBlocks[$i][$j][$v]);
            $this->isimprove = true;
        }
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

    /**
     * @desc:重置improve 标志位
     */
    public function resetImprove() {
        $this->isimprove = false;
    }

    public function isImprove() {
        return $this->isimprove;
    }

    /**
     * @desc:取得候选数最小的位置
     * @return array （$i, $j, $candis:array()）
     */
    public function getMinCandiPos() {
        $ti = 1;
        $tj = 1;
        $min = 9;
        for ($i = 1; $i < 9; $i ++) {
            $j = 1;
            for(; $j < 9; $j ++) {
                if (1 == count($this->arrBlocks[$i][$j])) {
                    continue;
                }
                else {
                    if ($min >= count($this->arrBlocks[$i][$j])) {
                        $min = count($this->arrBlocks[$i][$j]);
                        $ti = $i;
                        $tj = $j;
                    }
                }
            }
        }

        return array($ti, $tj, $this->arrBlocks[$ti][$tj]);
    }

    public function copySelf() {
        $newone = new sudokumap();
        $newone->arrBlocks = $this->arrBlocks;
        return $newone;
    }

    /**
     * @desc: 执行二元检查，如果两个位置是二元位置：即这两个位置的候选数集合是一样的，且都是两个
     * @param $i1
     * @param $j1
     * @param $i2
     * @param $j2
     * @return bool
     */
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

    /**
     * @desc: 初始化满一个位置
     * @param $i
     * @param $j
     */
    public function initFullBlock($i, $j) {
        //值的取值范围为 1~9
        for ($v = 1; $v < 10; $v ++) {
            $this->arrBlocks[$i][$j][$v] = true;
        }
    }

    /**
     * @desc: 计算确定数字的位置一共有多少个
     * @return int
     */
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

    /**
     * @desc:判断位置是否为空
     * @param $i
     * @param $j
     * @return bool
     */
    public function isBlockEmpty($i, $j) {
        return empty($this->arrBlocks[$i][$j]);
    }

    /**
     * @desc: 判断位置是否有多个候选数
     * @param $i
     * @param $j
     * @return bool
     */
    public function isBlockMuti($i, $j) {
        return 1 < count($this->arrBlocks[$i][$j]);
    }

    /**
     * @desc:初始化一个samplemap，用于输出检查
     */
    public function initSampleMap() {
        for ($i = 1; $i < 10; $i ++) {
            for ($j = 1; $j < 10; $j ++) {
                $this->arrBlocks[$i][$j] = array();
            }
        }
    }

    /**
     * @desc: 命令行输出，格式不太好看
     */
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

    /**
     * @desc: 将每个位置格式化为字符串，之后输出
     * @return mixed
     */
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

    /**
     * @desc: 从数组导入，早期调试观察用
     * @param $arrLine
     */
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

    /**
     * @desc:从xlsx解析的结果中导入
     * @param $values
     */
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