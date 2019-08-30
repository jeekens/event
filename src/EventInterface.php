<?php declare(strict_types=1);


namespace Jeekens\Event;

/**
 * Interface EventInterface
 *
 * @package Jeekens\Event
 */
interface EventInterface
{

    /**
     * 获取事件数据
     *
     * @return mixed
     */
    public function getData();

    /**
     * 获取事件名称
     *
     * @return string
     */
    public function getName();

    /**
     * 返回事件是否可关闭
     *
     * @return bool
     */
    public function isCancelable(): bool;

    /**
     * 判断事件是否已关闭
     *
     * @return bool
     */
    public function isStopped(): bool;

    /**
     * 设置事件数据
     *
     * @param null $data
     *
     * @return EventInterface
     */
    public function setData($data = null): EventInterface;

    /**
     * 设置事件名称
     *
     * @param string $type
     *
     * @return EventInterface
     */
    public function setName(string $type): EventInterface;

    /**
     * 停止事件
     *
     * @return EventInterface
     */
    public function stop(): EventInterface;

    /**
     * 获取事件触发源
     *
     * @return mixed
     */
    public function getSource();

}