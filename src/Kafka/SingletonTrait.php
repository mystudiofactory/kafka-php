<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
// +---------------------------------------------------------------------------
// | SWAN [ $_SWANBR_SLOGAN_$ ]
// +---------------------------------------------------------------------------
// | Copyright $_SWANBR_COPYRIGHT_$
// +---------------------------------------------------------------------------
// | Version  $_SWANBR_VERSION_$
// +---------------------------------------------------------------------------
// | Licensed ( $_SWANBR_LICENSED_URL_$ )
// +---------------------------------------------------------------------------
// | $_SWANBR_WEB_DOMAIN_$
// +---------------------------------------------------------------------------

namespace Kafka;

/**
+------------------------------------------------------------------------------
* Kafka Singleton
+------------------------------------------------------------------------------
*
* @package
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$
+------------------------------------------------------------------------------
*/

trait SingletonTrait
{
    use \Psr\Log\LoggerAwareTrait;
    use \Kafka\LoggerTrait;
    // {{{ consts
    // }}}
    // {{{ members

    protected static $instances = [];

    // }}}
    // {{{ functions
    // {{{ public function static getInstance()

    public static function getInstance($key = 'default')
    {
        if (!array_key_exists($key, static::$instances)) {
            static::$instances[$key] = new static();
        }

        return static::$instances[$key];
    }

    public static function removeInstance($key = 'default')
    {
        if (array_key_exists($key, static::$instances)) {
            unset(static::$instances[$key]);
        }
    }

    // }}}
    // {{{ private function __construct()

    /**
     * __construct
     *
     * @access public
     * @param $hostList
     * @param null $timeout
     */
    private function __construct()
    {
    }

    // }}}
    // }}}
}
