<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: lobby/backstage/table.proto

namespace GPBMetadata\Lobby\Backstage;

class Table
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            '
�
lobby/backstage/table.proto	backstage"9
DeleteTableBackstageRequest

id (
userId (	"*
DeleteTableBackstageResponse

id (2p
TableService`
DeleteTable&.backstage.DeleteTableBackstageRequest\'.backstage.DeleteTableBackstageResponse" B@Z>gitlab.initcapp.com/gaming/ptgame_protobuf/gen/lobby/backstagebproto3'
        , true);

        static::$is_initialized = true;
    }
}

