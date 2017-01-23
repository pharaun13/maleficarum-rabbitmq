<?php
/**
 * This trait provides functionality common to all classes dependant on the \Maleficarum\Rabbitmq namespace
 */

namespace Maleficarum\Rabbitmq;

trait Dependant
{
    /**
     * Internal storage for the rabbitmq connection object.
     *
     * @var \Maleficarum\Rabbitmq\Connection|null
     */
    protected $rabbitmqStorage = null;

    /**
     * Inject a new rabbitmq connection object.
     *
     * @param \Maleficarum\Rabbitmq\Connection $connection
     *
     * @return $this
     */
    public function setQueue(\Maleficarum\Rabbitmq\Connection $connection) {
        $this->rabbitmqStorage = $connection;

        return $this;
    }

    /**
     * Fetch the currently assigned rabbitmq connection object.
     *
     * @return \Maleficarum\Rabbitmq\Connection|null
     */
    public function getQueue() {
        return $this->rabbitmqStorage;
    }

    /**
     * Detach the currently assigned rabbitmq connection object.
     *
     * @return $this
     */
    public function detachQueue() {
        $this->rabbitmqStorage = null;

        return $this;
    }
}
