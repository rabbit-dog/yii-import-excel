<?php
/**
 * 使用说明
 * User: xing.chen
 * Date: 2018/10/6
 * Time: 21:10
 *
 *
 *
ImportExcel::init(''文件路径, $rowsSet, 1)
->valueMap($valueMap)
->valueMapDefault($valueMapDefault)
->formatFields(['birthday' => 'date', 'activistApplyDate' => 'date'])
->run(function($data) use ($type) {

// 增加/修改
if ($type == 'update') $m = PartyMember::findCard($data['cardNumber']) ?: new PartyMember();
else $m = new PartyMember();

$m->load($data, '');
if (!$m->save()) throw new ModelException($m);
});
 */

namespace xing\yiiImportExcel;

use Yii;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

/**
 * Class ImportExcel
 * @property string $file 文件路径
 * @property array $rowsSet  列对应的字段名
 * @property int $startRow 从第几行开始处理
 * @property array $valueMap 选项值配置
 * @property array $valueMapDefault 选项值不存在时的默认值
 * @property array $formatFields 格式设置，如日期需要设置，否则读取到值 会有问题
 * @package xing\helper\yii\xml
 */
class ImportExcel
{

    protected $file;

    protected $rowsSet;

    protected $valueMap;

    protected $valueMapDefault;

    protected $formatFields = [];

    // 当前行
    public static $currentRow = 0;


    /**
     * 初始化
     * @param string $file 文件所在路径
     * @param array $rowsSet 列对应字段
     * @param int $startRow 从第几行开始处理
     * @return ImportExcel
     */
    public static function init($file, $rowsSet, $startRow = 0)
    {
        $class = new self;
        $class->file = $file;
        $class->rowsSet = $rowsSet;
        $class->startRow = $startRow;
        return $class;
    }

    public function valueMap($valueMap)
    {
        $this->valueMap = $valueMap;
        return $this;
    }

    public function valueMapDefault($valueMapDefault)
    {
        $this->valueMapDefault = $valueMapDefault;
        return $this;
    }

    public function formatFields($formatFields)
    {
        $this->formatFields = $formatFields;
        return $this;
    }

    /**
     * 执行导入
     * @param callable $saveFunction 保存程序
     * @throws \PHPExcel_Reader_Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function run(callable $saveFunction)
    {

        set_time_limit(0);
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        if(!$objReader->canRead($this->file)){
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }

        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($this->file);

        $worksheet = $spreadsheet->getActiveSheet();

        // 表格序列对应的字段名
        $rowsSet = $this->rowsSet;

        // 键值设置
        $valueMap = $this->valueMap;

        // 键值默认设置，如果没有设置的话，将会抛出错误
        $valueMapDefault = $this->valueMapDefault;


        $transaction = Yii::$app->db->beginTransaction();
        $nnn = 0;
        try {
            // 检查数据（考虑内存，不另存数据）
            foreach ($worksheet->getRowIterator() as $k => $row) {
                // 跳过
                if ($k <= $this->startRow) continue;

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);
                // 检查当前一行
                foreach ($cellIterator as $col => $cell) {

                    $fieldName = $rowsSet[$col] ?? null;
                    if (empty($fieldName)) continue;

                    $value = $cell->getValue();

                    // 如果是选项
                    // 如果值为空，并且有默认值的设置，则设置为默认值，否则如果值存在，则值换为键名
                    if (isset($valueMap[$fieldName]) && !empty($value)) {
                        $value = array_keys($valueMap[$fieldName], $value)[0] ?? null;
                        if (is_null($value) && !isset($valueMapDefault[$fieldName]))
                            throw new \Exception("第{$k}行{$col}列格式错误，值为：{$value}。", 10000);
                    }
                }
            }

            foreach ($worksheet->getRowIterator() as $k => $row) {
                static::$currentRow = $k;
                // 跳过
                if ($k <= $this->startRow) continue;

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);

                // 循环赋值表格一行的数据
                $data = [];
                $nullNumber = 0;
                foreach ($cellIterator as $col => $cell) {
                    $fieldName = $rowsSet[$col] ?? null;
                    if (empty($fieldName)) continue;
                    $value = $cell->getValue();

                    // 如果值为空，并且有默认值的设置，则设置为默认值，否则如果值存在，则值换为键名
                    if (empty($value) && isset($valueMapDefault[$fieldName])) {
                        $value = $valueMapDefault[$fieldName];
                    } else if (!empty($value) && isset($valueMap[$fieldName]) && is_array($valueMap[$fieldName])) {
                        $value = array_keys($valueMap[$fieldName], $value)[0] ?? '';
                    }

                    // 格式处理
                    $value = $this->format($fieldName, $value);

                    $data[$fieldName] = (string) $value;
                    // 空值统计
                    if (empty($value)) $nullNumber ++;
                }

                // 如果空值大于等于所有的列
                if ($nullNumber >= count($this->rowsSet)) continue;

                // 执行保存程序
                $saveFunction($data);
                ++ $nnn;
            }

            $transaction->commit();
            return $nnn;
        } catch (\Exception $e) {
            $transaction->rollBack();
//            throw $e;
            throw new \Exception('<h3>操作失败，本次操作全部取消，请修正后重新上传</h3><p>错误信息：'.$e->getMessage().'</p>');
        }
    }

    /**
     * 格式处理
     * @param string $fieldName
     * @param $value
     * @return mixed
     */
    protected function format(string $fieldName, $value)
    {
        $format = $this->formatFields[$fieldName] ?? null;
            // 格式处理
        switch ($format) {
            case 'date':
                $value = $value ? date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($value)) : '1000-01-01';
                break;
            case 'date:int':
                $value = $value ? strtotime(date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($value))) : 0;
                break;
        }
        return $value;
    }
}