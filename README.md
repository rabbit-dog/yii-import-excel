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

// 选项键值设置
$valueMap = [
    'sex' => [0 => '女', 1 => '男']
];

// 键值默认设置，对应上面的选项值设置，没有默认值表示为必填，如果没有设置的话，将会抛出错误
$valueMapDefault = ['sex' => -1,];


ImportExcel::init($file, $rowsSet, $start)
    ->valueMap($valueMap)// 选项键值设置：如[ 0 => '女', 1 => '男']，表格值为男时，实际值将被转换为1，为女时，转为0。以上都不是时，如果没有默认值设置，则抛出错误
    
    ->valueMapDefault($valueMapDefault) // 字段默认值设置（值为空时）
    
    ->setUnique(['name', 'type']) // 要检查重复的字段数组
    
    ->formatFields(['birthday' => 'date']) // 格式设置，比如日期就需要设置，否则读取到值会有问题
    
    // 以下是事务回滚设置（YII）
    ->setTransactionRollBack(function($e) {
        exit('出错了，错误消息:' . $e->getMessage());
        // 不需要另外写回滚代码，本程序已经执行了回滚，这里是让你执行额外的代码
    })
    ->run(function($data) {
        // 保存数据的代码，这里是逐一保存每一行表格里的数据
        
        $m = new User();
        $m->load($data, '');
        $m->save();
    });
```

#### 方法 run($saveFunction) 参数说明
参数 $saveFunction 为匿名函数，用于执行保存的过程。

#### 格式设置
date  Y-m-d 为空时为 '1000-01-01'
date:int 时间戳 为空时为 0