<?php
namespace ipocket\network;

use ipocket\Server;
use ipocket\Player;

class GetPacket{

    private $packet = null;

    public function __construct(){

    }

    /**
     * [getPacket description]
     * @return [type] [description]
     */
    public function getPacket(){
        return $this->packet;
    }

    /**
     * [readPacket description]
     * @return [type] [description]
     */
    public function readPacket(){

    }

    /**
     * [sendError description]
     * @return [type] [description]
     */
    public function sendError(){

    }

    /**
     * [getCommandPacket description]
     * @return [type] [description]
     */
    public function getCommandPacket(){

    }

    /**
     * [getCommand description]
     * @return [type] [description]
     */
    public function getCommand() : string{
        $pk = new WebCommandPacket();
        return $pk->command;
    }
}
?>