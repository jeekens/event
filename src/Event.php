<?php declare(strict_types=1);


namespace Jeekens\Event;

use Exception;

/**
 * Class Event
 *
 * @package Jeekens\Event
 */
class Event implements EventInterface
{

    /**
     * 事件是否可以取消
     *
     * @var bool
     */
    protected $cancelable;

    /**
     * Event data
     *
     * @var mixed
     */
    protected $data;

    /**
     * 事件触发源
     *
     * @var mixed
     */
    protected $source;

    /**
     * 事件是否停止
     *
     * @var bool
     */
    protected $stopped = false;

    /**
     * 事件名称
     *
     * @var string
     */
    protected $name;

    /**
     * Event constructor.
     *
     * @param string $type
     * @param $source
     * @param null $data
     * @param bool $cancelable
     */
    public function __construct(string $name, $source, $data = null, bool $cancelable = true)
    {
        $this->name = $name;
        $this->source = $source;
        $this->data = $data;
        $this->cancelable = $cancelable;
    }

    /**
     * 判断事件是否可以停止
     *
     * if ($event->isCancelable()) {
     *     $event->stop();
     * }
     *
     * @return bool
     */
    public function isCancelable(): bool
    {
        return $this->cancelable;
    }

    /**
     * 判断事件是否已经停止
     *
     * @return bool
     */
    public function isStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * 设置事件数据
     *
     * @param null $data
     *
     * @return EventInterface
     */
    public function setData($data = null): EventInterface
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return EventInterface
     */
    public function setName(string $name): EventInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     *
     * 停止事件
     *
     * if ($event->isCancelable()) {
     *     $event->stop();
     * }
     *
     * @return EventInterface
     *
     * @throws Exception
     */
    public function stop(): EventInterface
    {
        if (!$this->cancelable) {
            throw new Exception('Trying to cancel a non-cancelable event');
        }

        $this->stopped = true;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

}