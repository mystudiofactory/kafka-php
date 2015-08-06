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
* Kafka protocol since Kafka v0.8
+------------------------------------------------------------------------------
*
* @package
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$
+------------------------------------------------------------------------------
*/

class Client
{
    // {{{ consts
    // }}}
    // {{{ members

    /**
     * cluster metadata
     *
     * @var \Kafka\ClusterMetaData
     * @access private
     */
    private $metadata = null;

    /**
     * broker host list
     *
     * @var array
     * @access private
     */
    private $hostList = array();

    /**
     * buffer size
     *
     * @var array
     * @access private
     */
    private $writeBufferSize = null;

    /**
     * save broker connection
     *
     * @var array
     * @access private
     */
    private static $stream = array();

    // }}}
    // {{{ functions
    // {{{ public function __construct()

    /**
     * __construct
     *
     * @access public
     * @return void
     */
    public function __construct(ClusterMetaData $metadata, $writeBufferSize = null)
    {
        $this->metadata = $metadata;
        if (method_exists($metadata, 'setClient')) {
            $this->metadata->setClient($this);
        }
        $this->writeBufferSize = $writeBufferSize;
    }

    // }}}
    // {{{ public function getBrokers()

    /**
     * get broker server
     *
     * @access public
     * @return void
     */
    public function getBrokers()
    {
        if (empty($this->hostList)) {
            $brokerList = $this->metadata->listBrokers();
            foreach ($brokerList as $brokerId => $info) {
                if (!isset($info['host']) || !isset($info['port'])) {
                    continue;
                }
                $this->hostList[$brokerId] = $info['host'] . ':' . $info['port'];
            }
        }

        return $this->hostList;
    }

    // }}}
    // {{{ public function getHostByPartition()

    /**
     * get broker host by topic partition
     *
     * @param string $topicName
     * @param int $partitionId
     * @access public
     * @return string
     */
    public function getHostByPartition($topicName, $partitionId = 0)
    {
        $partitionInfo = $this->metadata->getPartitionState($topicName, $partitionId);
        if (!$partitionInfo) {
            throw new \Kafka\Exception('topic:' . $topicName . ', partition id: ' . $partitionId . ' is not exists.');
        }

        $hostList = $this->getBrokers();
        if (isset($partitionInfo['leader']) && isset($hostList[$partitionInfo['leader']])) {
            return $hostList[$partitionInfo['leader']];
        } else {
            throw new \Kafka\Exception('can\'t find broker host.');
        }
    }

    // }}}
    // {{{ public function getZooKeeper()

    /**
     * get kafka zookeeper object
     *
     * @access public
     * @return \Kafka\ZooKeeper
     */
    public function getZooKeeper()
    {
        if ($this->metadata instanceof \Kafka\ZooKeeper) {
                return $this->metadata;
        } else {
                throw new \Kafka\Exception( 'ZooKeeper was not provided' );
        }
    }

    // }}}
    // {{{ public function getStream()

    /**
     * get broker broker connect
     *
     * @param string $host
     * @access private
     * @return void
     */
    public function getStream($host, $lockKey = null)
    {
        if (!$lockKey) {
            $lockKey = uniqid($host);
        }

        list($hostname, $port) = explode(':', $host);
        // find unlock stream
        if (isset(self::$stream[$host])) {
            foreach (self::$stream[$host] as $key => $info) {
                if ($info['locked']) {
                    continue;
                } else {
                    self::$stream[$host][$key]['locked'] = true;
                    $info['stream']->connect();
                    return array('key' => $key, 'stream' => $info['stream']);
                }
            }
        }

        // no idle stream
        $stream = new \Kafka\Socket($hostname, $port, $this->writeBufferSize);
        $stream->connect();
        self::$stream[$host][$lockKey] = array(
            'locked' => true,
            'stream' => $stream,
        );
        return array('key' => $lockKey, 'stream' => $stream);
    }

    // }}}
    // {{{ public function freeStream()

    /**
     * free stream pool
     *
     * @param string $key
     * @access public
     * @return void
     */
    public function freeStream($key)
    {
        foreach (self::$stream as $host => $values) {
            if (isset($values[$key])) {
                self::$stream[$host][$key]['locked'] = false;
            }
        }
    }

    // }}}
    // {{{ public function getTopicDetail()

    /**
     * get topic detail info
     *
     * @param  string $topicName
     * @return array
     */
    public function getTopicDetail($topicName)
    {
        return $this->metadata->getTopicDetail($topicName);
    }

    // }}}
    // }}}
}
