<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/12/23
 * Time: 15:38
 */

namespace xing\yiiImportExcel\cache;


class CacheFacetory
{


    /**
     * @return YiiCacheRSR16
     */
    public static function getInstance()
    {
        return new YiiCacheRSR16;
    }
}