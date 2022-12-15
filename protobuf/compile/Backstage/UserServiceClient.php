<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Backstage;

/**
 */
class UserServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * ChangeStatus, this function should be used by admin/backstage, either they want to Delete or Enable or Disable
     * Lobby will publish this user to MQ for other service subcribe base on demand
     * @param \Backstage\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ChangeStatus(\Backstage\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.UserService/ChangeStatus',
        $argument,
        ['\Backstage\User', 'decode'],
        $metadata, $options);
    }

    /**
     * ChangeChannelStatus, this function should be used by admin/backstage, either they want to Delete or Enable or Disable
     * Lobby will publish this user to MQ for other service subcribe base on demand
     * @param \Backstage\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ChangeChannelStatus(\Backstage\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.UserService/ChangeChannelStatus',
        $argument,
        ['\Backstage\User', 'decode'],
        $metadata, $options);
    }

    /**
     * CreateLoginLog for create login log from user login via api
     * @param \Backstage\UserLoginLogRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function CreateLoginLog(\Backstage\UserLoginLogRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.UserService/CreateLoginLog',
        $argument,
        ['\Backstage\UserLoginLogResponse', 'decode'],
        $metadata, $options);
    }

}
