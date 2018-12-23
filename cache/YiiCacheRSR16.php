<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2018/12/23
 * Time: 15:23
 */

namespace xing\yiiImportExcel\cache;

use Yii;

class YiiCacheRSR16 implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @return \yii\caching\CacheInterface
     */
    public static function getInstance()
    {
        return Yii::$app->cache;
    }

    public function get($key, $default = null)
    {
        return self::getInstance()->get($key);
    }

    public function set($key, $value, $ttl = null)
    {

        return self::getInstance()->set($key, $value, $ttl);
    }


    public function delete($key)
    {
        return self::getInstance()->delete($key);
    }


    public function clear()
    {
        return self::getInstance()->flush();
    }


    public function getMultiple($keys, $default = null)
    {
        return self::getInstance()->multiGet($keys);
    }


    public function setMultiple($values, $ttl = null)
    {
        return self::getInstance()->multiSet($keys, $ttl);
    }

    public function deleteMultiple($keys)
    {
        $list = self::getInstance()->multiSet($keys, $ttl);
        foreach ($list as $k => $v) {
            $this->delete($k);
        }
        return true;
    }

    public function has($key)
    {
        return self::getInstance()->exists($key);
    }
}