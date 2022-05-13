<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/12/23
 * Time: 15:38
 */

namespace xing\yiiImportExcel\cache;


class CacheFactory
{


    /**
     * @param null $cacheDrive
     * @return YiiCacheRSR16
     */
    public static function getInstance($cacheDrive = null)
    {
        YiiCacheRSR16::$cacheDrive = $cacheDrive;
        return new YiiCacheRSR16;
    }
}
