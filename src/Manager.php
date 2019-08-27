<?php declare(strict_types=1);


namespace Jeekens\Event;


use Closure;
use Exception;
use SplPriorityQueue;
use function is_array;
use function is_object;
use function is_string;
use function strpos;

/**
 * Class Manager
 *
 * @package Jeekens\Event
 */
class Manager implements ManagerInterface
{

    /**
     * 默认优先级
     */
    const DEFAULT_PRIORITY = 100;

    /**
     * @var bool
     */
    protected $collect = false;

    /**
     * @var bool
     */
    protected $enablePriorities = false;

    /**
     * @var SplPriorityQueue[]
     */
    protected $events = null;

    /**
     * @var array
     */
    protected $responses;

    /**
     * 订阅事件
     *
     * @param string $eventType
     * @param $handler
     * @param int $priority
     *
     * @return mixed|void
     *
     * @throws Exception
     */
    public function attach(string $eventType, $handler, int $priority = self::DEFAULT_PRIORITY)
    {
        if (!is_object($handler)) {
            throw new Exception('Event handler must be an Object');
        }

        if (!isset($this->events[$eventType])) {

            $priorityQueue = new SplPriorityQueue();
            $priorityQueue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
            $this->events[$eventType] = $priorityQueue;

        } else {
            $priorityQueue = $this->events[$eventType];
        }

        if (!$this->enablePriorities) {
            $priority = self::DEFAULT_PRIORITY;
        }

        $priorityQueue->insert($handler, $priority);
    }

    /**
     * 返回事件订阅优先级的开启状态
     *
     * @return bool
     */
    public function arePrioritiesEnabled(): bool
    {
        return $this->enablePriorities;
    }

    /**
     * 设置是否收集事件执行结果
     *
     * @param bool $collect
     */
    public function collectResponses(bool $collect)
    {
        $this->collect = $collect;
    }

    /**
     *
     * @param string $eventType
     * @param $handler
     *
     * @return mixed|void
     *
     * @throws Exception
     */
    public function detach(string $eventType, $handler)
    {

        if (!is_object($handler)) {
            throw new Exception('Event handler must be an Object');
        }

        if (isset($this->events[$eventType]) && $priorityQueue = $this->events[$eventType]) {

            $newPriorityQueue = new SplPriorityQueue();
            $newPriorityQueue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
            $priorityQueue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
            $priorityQueue->top();

            while ($priorityQueue->valid()) {
                $data = $priorityQueue->current();

                $priorityQueue->next();

                if ($data['data'] !== $handler) {
                    $newPriorityQueue->insert($data['data'], $data['priority']);
                }
            }

            $this->events[$eventType] = $newPriorityQueue;
        }
    }

    /**
     * @param string|null $type
     *
     * @return mixed|void
     */
    public function detachAll(?string $type = null)
    {
        if ($type === null) {
            $this->events = null;
        } else {
            if (isset($this->events[$type])) {
                unset($this->events[$type]);
            }
        }
    }

    /**
     * 是否开启订阅优先级
     *
     * @param bool $enablePriorities
     */
    public function enablePriorities(bool $enablePriorities)
    {
        $this->enablePriorities = $enablePriorities;
    }

    /**
     * 埋点
     *
     * @param string $eventType
     * @param $source
     * @param null $data
     * @param bool $cancelable
     *
     * @return mixed|null
     *
     * @throws Exception
     */
    public function fire(string $eventType, $source, $data = null, bool $cancelable = true)
    {

        $events = $this->events;

        if (!is_array($events)) {
            return null;
        }

        if (strpos($eventType, ':')) {
            throw new Exception('Invalid event type ' . $eventType);
        }

        $eventParts = explode(':', $eventType);
        $type = $eventParts[0];
        $eventName = $eventParts[1];
        $status = null;

        if ($this->collect) {
            $this->responses = null;
        }

        $event = new Event($eventName, $source, $data, $cancelable);

        if (($fireEvents = $events[$type] ?? null)) {
            if (is_object($fireEvents)) {
                $status = $this->fireQueue($fireEvents, $event);
            }
        }

        if (($fireEvents = $events[$eventType] ?? null)) {
            if (is_object($fireEvents)) {
                $status = $this->fireQueue($fireEvents, $event);
            }
        }

        return $status;
    }

    /**
     * 埋点队列，订阅者广播
     *
     * @param SplPriorityQueue $queue
     * @param EventInterface $event
     *
     * @return mixed|null
     *
     * @throws Exception
     */
    protected function fireQueue(SplPriorityQueue $queue, EventInterface $event)
    {

        $status = null;
        $eventName = $event->getType();

        if (is_string($eventName)) {
            throw new Exception('The event type not valid');
        }

        $source = $event->getSource();
        $data = $event->getData();
        $cancelable = $event->isCancelable();
        $collect = $this->collect;


        $iterator = clone $queue;
        $iterator->top();

        while ($iterator->valid()) {

            $handler = $iterator->current();
            $iterator->next();

            if (!is_object($handler)) {
                continue;
            }


            if ($handler instanceof Closure) {
                $status = call_user_func_array($handler, [$event, $source, $data]);
            } else {
                if (!method_exists($handler, $eventName)) {
                    continue;
                }
                $status = $handler->{$eventName}($event, $source, $data);
            }

            if ($collect) {
                $this->responses[] = $status;
            }

            if ($cancelable) {
                if ($event->isStopped()) {
                    break;
                }
            }
        }

        return $status;
    }

    /**
     * 返回所有的时间监听者
     *
     * @param string $type
     *
     * @return array
     */
    public function getListeners(string $type): array
    {

        if (!isset($this->events[$type])) {
            return [];
        }

        $listeners = [];
        $fireEvents = $this->events[$type];
        $priorityQueue = clone $fireEvents;

        $priorityQueue->top();

        while ($priorityQueue->valid()) {
            $listeners[] = $priorityQueue->current();
            $priorityQueue->next();
        }

        return $listeners;
    }

    /**
     * 返回事件的处理结果数组
     *
     * @return array
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * 判断事件是否存在订阅者
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasListeners(string $type): bool
    {
        return isset($this->events[$type]);
    }

    /**
     * 是否收集事件处理结果
     *
     * @return bool
     */
    public function isCollecting(): bool
    {
        return $this->collect;
    }

}