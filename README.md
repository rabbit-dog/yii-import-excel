# yii-improt-excel
yii导入excel表格的扩展程序，程序对常用导入套路进行了封装，可大大简化编写代码的工作量。

### 特点
1、可以进行简单的空值检查，重复检查。

2、自动将数据提取出来。

3、使用Yii缓存和配置，可读取大表格

4、可对时间日期进行处理，提取日期为相应数值

5、可将字符串转换为数组配置对应的键

6、可设置空单元格时的默认值


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
    ->valueMap($valueMap)// 值映射设置：如[ 0 => '女', 1 => '男']，表格值为男时，实际值将被转换为1，为女时，转为0。以上都不是时，如果没有默认值设置，则抛出错误
    
    ->valueMapDefault($valueMapDefault) // 字段默认值设置（值为空时）
    
    ->setUnique(['name', 'type']) // 要检查重复的字段数组
    
    ->formatFields(['birthday' => 'date']) // 格式设置，比如日期就需要设置，否则读取到值会有问题
    
    // 以下是事务回滚设置（YII）
    ->setTransactionRollBack(function($e) {
        exit('出错了，错误消息:' . $e->getMessage());
    })
    ->run(function($data) {
        // 保存数据的代码
        
        $m = new User();
        $m->load($data, '');
        $m->save();
    });
```

#### 方法 run($saveFunction) 参数说明
参数 $saveFunction 为匿名函数，用于执行保存的过程。
上面例子的use ($type) 表示传递$type进去
这个匿名函数是必须自己写的，否则本类完全没有作用。

#### formatFields 格式设置
date类型 说明：转换为Y-m-d，如果为空时将转换为 '1000-01-01'

date:int类型 说明：转换为时间戳，如果为空则转换为 0