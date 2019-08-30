<?php declare(strict_types=1);


namespace Jeekens\Event;

/**
 * Trait EventsAwareTrait
 *
 * @package Jeekens\Event
 */
trait EventsAwareTrait
{

    /**
     * @var ManagerInterface
     */
    protected $eventsManager;

    /**
     * 返回一个事件管理器
     *
     * @return ManagerInterface|null
     */
    public function getEventsManager(): ?ManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * 设置事件管理器
     *
     * @param ManagerInterface $eventsManager
     */
    public function setEventsManager(ManagerInterface $eventsManager)
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * 触发事件
     *
     * @param string $eventName
     * @param null $source
     * @param null $data
     *
     * @return mixed
     */
    public function trigger(string $eventName, $source = null, $data = null)
    {
        return tolerant_null($this->getEventsManager(), function (ManagerInterface $manager) use ($eventName, $source, $data) {
            return $manager->trigger($eventName, $source, $data);
        });
    }

}