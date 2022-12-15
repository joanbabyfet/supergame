<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: lobby/backstage/wallet.proto

namespace Backstage;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>backstage.WithdrawRequest</code>
 */
class WithdrawRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * is userId
     *
     * Generated from protobuf field <code>string holderId = 1;</code>
     */
    protected $holderId = '';
    /**
     * balance to withdraw must be positive number
     *
     * Generated from protobuf field <code>double balance = 2;</code>
     */
    protected $balance = 0.0;
    /**
     * expected caller will generate it
     *
     * Generated from protobuf field <code>string transactionId = 3;</code>
     */
    protected $transactionId = '';
    /**
     * 1: player (玩家下单)
     * 2: backstage (后台下单)
     * 3: payout (結算派彩)
     *
     * Generated from protobuf field <code>uint32 origin = 4;</code>
     */
    protected $origin = 0;
    /**
     * use for saving in `pt_order_transfer`
     *
     * Generated from protobuf field <code>string remark = 5;</code>
     */
    protected $remark = '';
    /**
     * currency will get from Redis, if client not pass the value
     *
     * Generated from protobuf field <code>string currency = 6;</code>
     */
    protected $currency = '';
    /**
     * agentId will get from Redis, if client not pass the value
     *
     * Generated from protobuf field <code>string agentId = 7;</code>
     */
    protected $agentId = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $holderId
     *           is userId
     *     @type float $balance
     *           balance to withdraw must be positive number
     *     @type string $transactionId
     *           expected caller will generate it
     *     @type int $origin
     *           1: player (玩家下单)
     *           2: backstage (后台下单)
     *           3: payout (結算派彩)
     *     @type string $remark
     *           use for saving in `pt_order_transfer`
     *     @type string $currency
     *           currency will get from Redis, if client not pass the value
     *     @type string $agentId
     *           agentId will get from Redis, if client not pass the value
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Lobby\Backstage\Wallet::initOnce();
        parent::__construct($data);
    }

    /**
     * is userId
     *
     * Generated from protobuf field <code>string holderId = 1;</code>
     * @return string
     */
    public function getHolderId()
    {
        return $this->holderId;
    }

    /**
     * is userId
     *
     * Generated from protobuf field <code>string holderId = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setHolderId($var)
    {
        GPBUtil::checkString($var, True);
        $this->holderId = $var;

        return $this;
    }

    /**
     * balance to withdraw must be positive number
     *
     * Generated from protobuf field <code>double balance = 2;</code>
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * balance to withdraw must be positive number
     *
     * Generated from protobuf field <code>double balance = 2;</code>
     * @param float $var
     * @return $this
     */
    public function setBalance($var)
    {
        GPBUtil::checkDouble($var);
        $this->balance = $var;

        return $this;
    }

    /**
     * expected caller will generate it
     *
     * Generated from protobuf field <code>string transactionId = 3;</code>
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * expected caller will generate it
     *
     * Generated from protobuf field <code>string transactionId = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setTransactionId($var)
    {
        GPBUtil::checkString($var, True);
        $this->transactionId = $var;

        return $this;
    }

    /**
     * 1: player (玩家下单)
     * 2: backstage (后台下单)
     * 3: payout (結算派彩)
     *
     * Generated from protobuf field <code>uint32 origin = 4;</code>
     * @return int
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * 1: player (玩家下单)
     * 2: backstage (后台下单)
     * 3: payout (結算派彩)
     *
     * Generated from protobuf field <code>uint32 origin = 4;</code>
     * @param int $var
     * @return $this
     */
    public function setOrigin($var)
    {
        GPBUtil::checkUint32($var);
        $this->origin = $var;

        return $this;
    }

    /**
     * use for saving in `pt_order_transfer`
     *
     * Generated from protobuf field <code>string remark = 5;</code>
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * use for saving in `pt_order_transfer`
     *
     * Generated from protobuf field <code>string remark = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setRemark($var)
    {
        GPBUtil::checkString($var, True);
        $this->remark = $var;

        return $this;
    }

    /**
     * currency will get from Redis, if client not pass the value
     *
     * Generated from protobuf field <code>string currency = 6;</code>
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * currency will get from Redis, if client not pass the value
     *
     * Generated from protobuf field <code>string currency = 6;</code>
     * @param string $var
     * @return $this
     */
    public function setCurrency($var)
    {
        GPBUtil::checkString($var, True);
        $this->currency = $var;

        return $this;
    }

    /**
     * agentId will get from Redis, if client not pass the value
     *
     * Generated from protobuf field <code>string agentId = 7;</code>
     * @return string
     */
    public function getAgentId()
    {
        return $this->agentId;
    }

    /**
     * agentId will get from Redis, if client not pass the value
     *
     * Generated from protobuf field <code>string agentId = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setAgentId($var)
    {
        GPBUtil::checkString($var, True);
        $this->agentId = $var;

        return $this;
    }

}
