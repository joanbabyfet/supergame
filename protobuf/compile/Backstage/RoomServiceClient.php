<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Backstage;

/**
 * rpc serice for room
 */
class RoomServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * CreateRoom use when admin/backstage create new room,
     * should pass room data to lobby for lobby create own room cache
     * @param \Backstage\RoomRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function CreateRoom(\Backstage\RoomRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.RoomService/CreateRoom',
        $argument,
        ['\Backstage\RoomResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * UpdateRoom use when admin/backstage update room,
     * Should pass room data to lobby for lobby replace own room cache,
     * @param \Backstage\RoomRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UpdateRoom(\Backstage\RoomRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.RoomService/UpdateRoom',
        $argument,
        ['\Backstage\RoomResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * DeleteRoom use when admin/backstage delete a room,
     * should pass room id to lobby for lobby delete from own room cache
     * @param \Backstage\DeleteRoomRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function DeleteRoom(\Backstage\DeleteRoomRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backstage.RoomService/DeleteRoom',
        $argument,
        ['\Backstage\RoomResponse', 'decode'],
        $metadata, $options);
    }

}
