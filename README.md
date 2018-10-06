# yii-improt-excel
yii导入excel表格的扩展程序

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

// 选项键值设置，比如性别表格中为女，下面设置0 => '女'，那么保存到数据库的值不是女，而是0（取键名）
$valueMap = ['sex' => [0 => '女', 1 => '男']];

// 键值默认设置，对应上面的选项值设置，没有默认值表示为必填，如果没有设置的话，将会抛出错误
$valueMapDefault = ['sex' => -1,];
            
ImportExcel::init($file, $rowsSet, 1)
    ->valueMap($valueMap)
    ->valueMapDefault($valueMapDefault)
    ->formatFields(['birthday' => 'date']) // 格式设置，如日期需要设置，否则读取到值 会有问题
    ->run(function($data) use ($type) {

        // 增加/修改
        if ($type == 'update') $m = Member::findCard($data['cardNumber']) ?: new Member();
        else $m = new Member();

        $m->load($data, '');
        $m->save();
    });
```

#### 格式设置
date  Y-m-d 为空时为 '1000-01-01'
date:int 时间戳 为空时为 0