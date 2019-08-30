<?php declare(strict_types=1);


namespace Jeekens\Event;

/**
 * Interface ManagerInterface
 *
 * @package Jeekens\Event
 */
interface ManagerInterface
{

    /**
     * 默认优先级
     */
    const DEFAULT_PRIORITY = 100;

    /**
     * 订阅一个事件
     *
     * @param string $eventName
     * @param $handler
     * @param int $priority
     *
     * @return mixed
     */
    public function subscribe(string $eventName, $handler, int $priority = self::DEFAULT_PRIORITY);

    /**
     * 触发一个事件
     *
     * @param string $eventName
     * @param mixed $source
     * @param null $data
     * @param bool $cancelable
     *
     * @return mixed
     */
    public function trigger(string $eventName, $source = null, $data = null,  bool $cancelable = true);

    /**
     * 获取全部订阅者
     *
     * @param string $eventName
     *
     * @return array
     */
    public function getSubscriber(?string $eventName = null): ?array;

    /**
     * 判断事件是否存在订阅者
     *
     * @param string $eventName
     *
     * @return bool
     */
    public function hasSubscriber(string $eventName): bool;

    /**
     * 返回订阅者的执行结果
     *
     * @return array
     */
    public function getResponses(): ?array;
}