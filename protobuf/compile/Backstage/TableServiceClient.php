<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Backstage;

/**
 * rpc table service
 */
class TableServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * DeleteTable use when admin/backstage delete table
     * NOTE: admin/backstage call to delete table via this rpc only, do not directly to MYSQL
     * @param \Backstage\DeleteTableBackstageRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function DeleteTable(\Backstage\DeleteTableBackstageRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.TableService/DeleteTable',
        $argument,
        ['\Backstage\DeleteTableBackstageResponse', 'decode'],
        $metadata, $options);
    }

}
