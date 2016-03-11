<?php

require_once 'require.php';

/**
 * Created by PhpStorm.
 * @author: woudygao
 * @date: 16/2/12
 * Time: 下午10:15
 */

if ($argc > 1) {
    emptyMap();
}
else {
    $obj = new AdvancedSolve();
    $obj->init();
    $obj->beginsolve();
}


class AdvancedSolve {
    //猜测栈
    private $guessRecord = array();
    //sudoku栈
    private $sudokuStack = array();

    const maxdepth = 18;

    private $currentSudoku = null;
    private $xmlHandle = null;

    private $linechecker = null;
    private $submapchecker = null;

    //初始化
    public function init() {
        $obj = new fileParse();
        $sudoku = $obj->readFromExcel('map.xlsx');
        $this->currentSudoku = $sudoku;
        $this->xmlHandle = $obj;
        $this->linechecker = new linechecker();
        $this->submapchecker = new submapchecker();
    }

    public function beginsolve() {

        $depth = count($this->sudokuStack);
        if (self::maxdepth < $depth) {
            //触底了直接false
            return false;
        }

        $flag = true;
        $time = 1;
        while ($flag) {
            try {
                $flag = $this->check($depth, $time);
            }
            catch (Exception $e) {
                //如果出现异常，说明一定是guess出错了
                return false;
            }
            $time ++;
        }

        $definedcount = $this->currentSudoku->getDefined();
        if (81 == $definedcount) {
            //成功了，直接输出结果，然后返回，todo 这里也是可以直接结束运行的
            $this->xmlHandle->dumpToXlsx('result.xlsx');
            return true;
        }

        $guess = $this->currentSudoku->getMinCandiPos();
        $i = $guess[0];
        $j = $guess[1];

        foreach ($guess[2] as $key => $bol) {
            $copy = $this->currentSudoku->copySelf();
            $copy->defineBlock($i, $j, $key);
            array_push($this->sudokuStack, $this->currentSudoku);
            $this->currentSudoku = $copy;

            //完成一次guess 之后再开始下一层
            $result = $this->beginsolve();
            if ($result) {
                return true;
            }
            else {
                $this->currentSudoku = array_pop($this->sudokuStack);
                continue;
            }
        }

    }

    public function check($depth, $time) {
        $bolline = $this->linechecker->docheck($this->currentSudoku);
        $this->xmlHandle->writeToXlsx($this->currentSudoku, $depth . '-' . $time . '-' . 'line' . '-' . $this->currentSudoku->getDefined());

        $bolsubmap = $this->submapchecker->docheck($this->currentSudoku);
        $this->xmlHandle->writeToXlsx($this->currentSudoku, $depth . '-' . $time . '-' . 'submap' . '-' . $this->currentSudoku->getDefined());
        return ($bolline || $bolsubmap);
    }
}
