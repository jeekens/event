<?php


namespace Jeekens\Event;


interface EventInterface
{

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return bool
     */
    public function isCancelable(): bool;

    /**
     * @return bool
     */
    public function isStopped(): bool;

    /**
     * @param null $data
     *
     * @return EventInterface
     */
    public function setData($data = null): EventInterface;

    /**
     * @param string $type
     *
     * @return EventInterface
     */
    public function setType(string $type): EventInterface;

    /**
     * @return EventInterface
     */
    public function stop(): EventInterface;

    /**
     * @return mixed
     */
    public function getSource();

}