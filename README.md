## toy
sudoku解题小程序

#### 2016.3.11 更新
+ 1. 重新使用回溯的计算方式，可以解决 http://www.henan100.com/luoyang/tbgz/2012/12234.shtml 提到的九宫格
+ 2. 之后有时间肩回溯的过程，使用dot+graphviz 绘制图像；或者将回溯的过程并发化

#### 使用：
+ 1. 通过编辑map.xlsx录入题目
+ 2. 然后 php solve.php
+ 3. 最终和中间结果写入到 result.xlsx 中

#### 解题方法说明：
+ 主体方法是消除候选项法
  + 直接消去法：消除已经确定的项目
  + 二元消去法：对于两个单元格中同时有2个相同的候选项的一个小组中，其他单元格消除这两个候选项
  + 三元消去法：识别这样的3个单元格，比较麻烦，这个暂未实现，实际上直接消去法和二元消去法加上唯一候选项法目前没遇到过解不出的题
+ 附加方法是唯一候选项法

#### 实现说明：
核心的部分在于分组进行计算：
+ 一行是一个组
+ 一列是一个组
+ 一个小方格是一个组
+ Hyper方格也是一个组(特殊的题目使用)
+ 对于其他的题目，需要做的是增加分组的check方法


#### 计算异常：
+ 添加了计算异常的抛出，可能情况是录入的题目有问题，导致计算不出正确的结果，result.xlsx 中有多个sheet，依次是计算的过程，sheet的命名是$time-$分组-$总共确定的单元格

