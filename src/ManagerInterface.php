<?php


namespace Jeekens\Event;


interface ManagerInterface
{
    /**
     * @param string $eventType
     * @param $handler
     *
     * @return mixed
     */
    public function attach(string $eventType, $handler);

    /**
     * @param string $eventType
     * @param $handler
     *
     * @return mixed
     */
    public function detach(string $eventType, $handler);

    /**
     * @param string|null $type
     *
     * @return mixed
     */
    public function detachAll(?string $type = null);

    /**
     * @param string $eventType
     * @param $source
     * @param null $data
     *
     * @return mixed
     */
    public function fire(string $eventType, $source, $data = null);

    /**
     * @param string $type
     *
     * @return array
     */
    public function getListeners(string $type): array;

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasListeners(string $type): bool;
}