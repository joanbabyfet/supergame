<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Backstage;

/**
 * rpc service for config
 */
class ConfigServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * ChangeConfig use when backstage changed any config related to 
     * table config or game config, should inform to lobby via this rpc
     * @param \Backstage\ChangeConfigRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ChangeConfig(\Backstage\ChangeConfigRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.ConfigService/ChangeConfig',
        $argument,
        ['\Backstage\ChangeConfigResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * ChangeMaintainConfig use when backstage changed maintainance config
     * @param \Backstage\MaintainConfig $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ChangeMaintainConfig(\Backstage\MaintainConfig $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.ConfigService/ChangeMaintainConfig',
        $argument,
        ['\Backstage\ChangeConfigResponse', 'decode'],
        $metadata, $options);
    }

}
