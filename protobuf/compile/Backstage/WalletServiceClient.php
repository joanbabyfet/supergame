<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Backstage;

/**
 * rpc service for wallet
 */
class WalletServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * Deposit use to recharge the player's balance
     * @param \Backstage\DepositRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Deposit(\Backstage\DepositRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.WalletService/Deposit',
        $argument,
        ['\Backstage\WalletResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Withdraw use to withdraw the player's balance
     * @param \Backstage\WithdrawRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Withdraw(\Backstage\WithdrawRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.WalletService/Withdraw',
        $argument,
        ['\Backstage\WalletResponse', 'decode'],
        $metadata, $options);
    }

}
