# yii-improt-excel
yii导入excel表格的扩展程序，程序对常用导入套路进行了封装，可大大简化编写代码的工作量。

### 安装
```php
composer require xing.chen/yii-import-excel dev-master
```

### 使用示例和说明
```php
<?php

// 表格序列对应的字段名
$rowsSet = [
    'A' => 'name',
    'B' => 'sex',
    'C' => 'birthday',
];

$start = 1; // 从第几行开始处理

$type = 'update';

ImportExcel::init($file, $rowsSet, $start)
    ->run(function($data) use ($type) {

        // 保存数据的代码
        $m = new Member();
        $m->load($data, '');
        $m->type = $type;
        $m->save();
    });
```

### 进阶使用
```php
<?php

// 选项键值设置，比如性别表格中为女，下面设置0 => '女'，那么保存到数据库的值不是女，而是0（取键名）
$valueMap = ['sex' => [0 => '女', 1 => '男']];

// 键值默认设置，对应上面的选项值设置，没有默认值表示为必填，如果没有设置的话，将会抛出错误
$valueMapDefault = ['sex' => -1,];


ImportExcel::init($file, $rowsSet, $start)
    ->valueMap($valueMap) // 
    // 默认值设置：如果设置了valueMap，对应值为空或错误，就会抛出错误。比如$valueMap['sex'] => [ 0 => '女', 1 => '男']，如果值不是男|女，就会抛出错误，但是设置了这个，则会自动变成默认值，不会抛出错误
    ->setUnique(['name', 'type']) // 要检查重复的字段数组
    ->valueMapDefault($valueMapDefault) 
    ->formatFields(['birthday' => 'date']) // 格式设置，如日期需要设置，否则读取到值 会有问题
    ->setTransactionRollBack(function($e) {
        exit('出错了，错误消息:' . $e->getMessage());
    }) // 出错后，yii不仅会自动圆滚，并且可以执行这里的程序
    ->run(function($data) use ($type) {
        // 保存数据的代码
    });
```

#### 方法 run($saveFunction) 参数说明
参数 $saveFunction 为匿名函数，用于执行保存的过程。
上面例子的use ($type) 表示传递$type进去
这个匿名函数是必须自己写的，否则本类完全没有作用。

#### 格式设置
date  Y-m-d 为空时为 '1000-01-01'
date:int 时间戳 为空时为 0