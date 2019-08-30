<?php declare(strict_types=1);


namespace Jeekens\Event;


use Closure;
use Exception;
use RuntimeException;
use SplPriorityQueue;
use function explode;
use function function_exists;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function method_exists;
use function strpos;

/**
 * Class Manager
 *
 * @package Jeekens\Event
 */
class Manager implements ManagerInterface
{

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
     * @param string $eventName
     * @param string $subName
     * @param $handler
     * @param int $priority
     *
     * @return mixed|void
     *
     * @throws Exception
     */
    public function subscribe(string $eventName, $handler, int $priority = self::DEFAULT_PRIORITY)
    {
        if (!(is_object($handler) && !method_exists($handler, 'handler')) && !is_callable($handler)) {
            throw new Exception('Event handler error.');
        }

        if (!isset($this->events[$eventName])) {

            $priorityQueue = new SplPriorityQueue();
            $priorityQueue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
            $this->events[$eventName] = $priorityQueue;

        } else {
            $priorityQueue = $this->events[$eventName];
        }

        $priorityQueue->insert($handler, $priority);
    }

    /**
     * 埋点
     *
     * @param string $eventName
     * @param $source
     * @param null $data
     * @param bool $cancelable
     *
     * @return mixed|null
     *
     * @throws Exception
     */
    public function trigger(string $eventName, $source = null, $data = null,  bool $cancelable = true)
    {
        $events = $this->events;

        if (empty($events)) {
            return null;
        }

        $status = null;
        $this->responses = null;

        $event = new Event($eventName, $source, $data, $cancelable);

        if (($fireEvents = $events[$eventName] ?? null)) {
            if (is_object($fireEvents)) {
                $status = $this->fireTrigger($fireEvents, $event);
            }
        }

        return $status;
    }

    /**
     *
     * @param SplPriorityQueue $queue
     * @param EventInterface $event
     *
     * @return mixed|null
     *
     * @throws Exception
     */
    protected function fireTrigger(SplPriorityQueue $queue, EventInterface $event)
    {

        $status = null;
        $eventName = $event->getName();

        if (empty($eventName)) {
            throw new Exception('The event name not valid.');
        }

        $source = $event->getSource();
        $data = $event->getData();
        $cancelable = $event->isCancelable();
        $iterator = clone $queue;
        $iterator->top();

        while ($iterator->valid()) {

            $handler = $iterator->current();
            $iterator->next();

            $this->responses[] = $this->handle($handler, $event, $source, $data);

            if ($cancelable) {
                if ($event->isStopped()) {
                    break;
                }
            }
        }

        return $status;
    }

    /**
     *
     * @param $handler
     * @param $event
     * @param $source
     * @param $data
     *
     * @return mixed
     */
    protected function handle($handler, $event, $source, $data)
    {
        if (is_object($handler) && method_exists($handler, 'handler')) {
            return $this->call([$handler, 'handler'], $event, $source, $data);
        }

        return $this->call($handler, $event, $source, $data);
    }

    /**
     * @param $callback
     * @param mixed ...$args
     *
     * @return mixed
     */
    protected function call($callback, ...$args)
    {
        if (is_string($callback)) {
            // className::method
            if (strpos($callback, '::') > 0) {
                $callback = explode('::', $callback, 2);
                // function
            } elseif (function_exists($callback)) {
                return $callback(...$args);
            }
        } elseif ($callback instanceof Closure) {
            return $callback(...$args);
        } elseif (is_object($callback) && method_exists($callback, '__invoke')) {
            return $callback(...$args);
        }

        if (is_array($callback)) {
            [$obj, $method] = $callback;

            return is_object($obj) ? $obj->$method(...$args) : $obj::$method(...$args);
        }

        throw new RuntimeException('Callback must be callable.');
    }

    /**
     * 返回所有的事件订阅者
     *
     * @param string|null $eventName
     *
     * @return array
     */
    public function getSubscriber(?string $eventName = null): ?array
    {

        if (empty($eventName)) {
            return $this->events;
        }

        if (!isset($this->events[$eventName])) {
            return [];
        }

        $subscriber = [];
        $fireEvents = $this->events[$eventName];
        $priorityQueue = clone $fireEvents;

        $priorityQueue->top();

        while ($priorityQueue->valid()) {
            $subscriber[] = $priorityQueue->current();
            $priorityQueue->next();
        }

        return $subscriber;
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
     * @param string $eventName
     *
     * @return bool
     */
    public function hasSubscriber(string $eventName): bool
    {
        return isset($this->events[$eventName]);
    }

}