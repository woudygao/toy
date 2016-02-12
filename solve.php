<?php

require_once 'require.php';

/**
 * Created by PhpStorm.
 * @author: gaoweikang
 * @date: 16/2/12
 * Time: 下午10:15
 */

if ($argc > 1) {
    emptyMap();
}
else {
    solve();
}

function solve() {
    $obj = new fileParse();

    $sudoku = $obj->readFromExcel('map.xlsx');


    $time = 0;

    try {
        while ($time < 18) {
            echo '--------------------------------------------' . PHP_EOL;
            echo 'Time:' . $time . PHP_EOL;
            echo 'line check' . PHP_EOL;

            $before = $sudoku->getDefined();

            echo 'line check' . PHP_EOL;
            check($obj, $sudoku, new linechecker(), $time, 'line');

            $after = $sudoku->getDefined();
            if ($after > $before) {
                $time ++;
                continue;
            }

            echo 'submap check' . PHP_EOL;
            check($obj, $sudoku, new submapchecker(), $time, 'submap');

            $after = $sudoku->getDefined();
            if ($after > $before) {
                $time ++;
                continue;
            }

            echo 'hyper check' . PHP_EOL;
            check($obj, $sudoku, new hyperchecker(), $time, 'hyper');

            $time ++;
        }
    }
    catch (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }

    $obj->dumpToXlsx('result.xlsx');
}

function check(fileParse &$obj, sudokumap &$sudoku, $checker, $time = 0, $name = 'line') {
    $before = $sudoku->getDefined();
    $checker->docheck($sudoku);
    $after = $sudoku->getDefined();
    if ($after > $before) {

        echo 'All Defined:' . $sudoku->getDefined() . PHP_EOL;
        $obj->writeToXlsx($sudoku, $time . '-' . $name . '-' . $after);
    }
}

function emptyMap() {
    $obj = new fileParse();
    ob_start();
    $obj->outputSample();
    $out = ob_get_contents();
    ob_end_clean();
    file_put_contents('map', $out);
}


// class define
class basicchecker {

    public function docheck(sudokumap &$sudoku, $arrPositions) {

        //1. 遍历找到确定的单元格的值
        $defind = array();

        $toApplyPosition = array();
        foreach ($arrPositions as $position) {
            $i = $position[0];
            $j = $position[1];

            if (! $sudoku->isBlockMuti($i, $j)) {
                $block = $sudoku->arrBlocks[$i][$j];
                $block = array_keys($block);
                foreach ($block as $v) {
                    $defind[] = $v;
                }
            }
            else {
                $toApply[0] = $i;
                $toApply[1] = $j;
                $toApplyPosition[] = $toApply;
            }
        }

        //2. 遍历将不确定单元格中的确定的候选项删除
        foreach ($toApplyPosition as $position) {
            $i = $position[0];
            $j = $position[1];

            if ($sudoku->isBlockMuti($i, $j)) {
                foreach ($defind as $v) {
                    $sudoku->removeFromBlock($i, $j, $v);
                }
            }
        }

        $this->checkException($sudoku, $arrPositions);

        $this->docheckDouble($sudoku, $arrPositions, $toApplyPosition);

        $this->checkShowOnce($sudoku, $arrPositions);
    }

    public function checkShowOnce(sudokumap &$sudoku, $arrPositions) {
        //检查某个值是否只出现了一次，如果是，则将单元格的值直接更正
        $defined = array();

        foreach ($arrPositions as $position) {
            $i = $position[0];
            $j = $position[1];

            foreach ($sudoku->arrBlocks[$i][$j] as $key => $bol) {
                $defined[$key][] = $position;
            }
        }

        foreach ($defined as $key => $positions) {
            if (1 == count($positions)) {
                $i = $positions[0][0];
                $j = $positions[0][1];
                $sudoku->defineBlock($i, $j, $key);
            }
        }

        $this->checkException($sudoku, $arrPositions);
    }

    public function docheckDouble(sudokumap &$sudoku, $arrPositions, $toApplyPosition) {
        //检查二元链, 并应用
        $candi = array();
        foreach ($toApplyPosition as $position) {
            $i = $position[0];
            $j = $position[1];

            if (2 == $sudoku->countInBlock($i, $j)) {
                $tmp[0] = $i;
                $tmp[1] = $j;
                $candi[] = $tmp;
            }
        }
        //至少有两个以上的才能构成二元关系
        $twice = array();

        if (2 <= count($candi)) {
            $size = count($candi);
            for ($a = 0; $a < $size; $a ++) {
                if (4 == count($twice)) {
                    break;
                }
                for ($b = $a + 1; $b < $size; $b ++) {
                    $i1 = $candi[$a][0];
                    $j1 = $candi[$a][1];
                    $i2 = $candi[$b][0];
                    $j2 = $candi[$b][1];

                    if ($sudoku->twoEqual($i1, $j1, $i2, $j2)) {
                        $twice = array($i1, $j1, $i2, $j2);
                        break;
                    }
                }
            }
        }

        if (4 == count($twice)) {

            error_log(json_encode($twice) . PHP_EOL, 3, 'log');

            $values = array_keys($sudoku->arrBlocks[$twice[0]][$twice[1]]);

            foreach ($toApplyPosition as $position) {
                $ti = $position[0];
                $tj = $position[1];

                if (($ti == $twice[0] && $tj == $twice[1]) || ($ti == $twice[2] && $tj == $twice[3])) {
                    continue;
                }
                $sudoku->removeFromBlockBatch($ti, $tj, $values);
            }

        }

        $this->checkException($sudoku, $arrPositions);
    }

    //检测出现重复值的问题
    public function checkException(sudokumap &$sudoku, $arrPositions) {
        $keyDefined = array();
        foreach ($arrPositions as $position) {
            $i = $position[0];
            $j = $position[1];
            if (! $sudoku->isBlockMuti($i, $j)) {
                $values = array_keys($sudoku->arrBlocks[$i][$j]);
                foreach ($values as $v) {
                    if (! array_key_exists($v, $keyDefined)) {
                        $keyDefined[$v] = 0;
                    }
                    else {
                        $keyDefined[$v] ++;
                    }
                }
            }
        }

        foreach ($keyDefined as $k => $v) {
            if (1 <= $v) {
                throw new Exception(json_encode($arrPositions));
            }
        }

    }
}


class linechecker {

    public function docheck(sudokumap &$sudoku) {
        $check = new basicchecker();

        //1. 行检查
        for ($i = 1; $i < 10; $i ++) {
            $arrPositions = array();
            for ($j = 1; $j < 10; $j ++) {
                $position = array();
                $position[0] = $i;
                $position[1] = $j;
                $arrPositions[] = $position;
            }
            $check->docheck($sudoku, $arrPositions);
        }

        //2. 列检查
        for ($j = 1; $j < 10; $j ++) {
            $arrPositions = array();
            for ($i = 1; $i < 10; $i ++) {
                $position = array();
                $position[0] = $i;
                $position[1] = $j;
                $arrPositions[] = $position;
            }

            $check->docheck($sudoku, $arrPositions);
        }

    }
}


class submapchecker {

    public function docheck(sudokumap &$sudoku) {

        $check = new basicchecker();
        //区块检查
        $all[] = range(1,3);
        $all[] = range(4,6);
        $all[] = range(7,9);

        $allRow = $all;
        $allColume = $all;
        foreach ($allRow as $arrRow) {
            foreach ($allColume as $arrColume) {
                $arrPositions = $this->getPositons($arrRow, $arrColume);
                $check->docheck($sudoku, $arrPositions);
            }
        }

    }

    public function getPositons($arrRow, $arrColume) {
        $arrPosition = array();
        foreach ($arrRow as $i) {
            foreach ($arrColume as $j) {
                $position = array();
                $position[0] = $i;
                $position[1] = $j;
                $arrPosition[] = $position;
            }
        }
        return $arrPosition;
    }
}

class hyperchecker extends submapchecker {

    public function docheck(sudokumap &$sudoku) {

        $check = new basicchecker();
        //区块检查
        $all[] = range(2,4);
        $all[] = range(6,8);

        $allRow = $all;
        $allColume = $all;
        foreach ($allRow as $arrRow) {
            foreach ($allColume as $arrColume) {
                $arrPositions = $this->getPositons($arrRow, $arrColume);
                $check->docheck($sudoku, $arrPositions);
            }
        }

    }

}
