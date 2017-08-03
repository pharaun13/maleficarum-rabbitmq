<?php
/**
 * This trait provides functionality common to all classes dependant on the \Maleficarum\Rabbitmq namespace
 */
declare (strict_types=1);

namespace Maleficarum\Rabbitmq;

trait Dependant {
    /* ------------------------------------ Class Property START --------------------------------------- */

    /**
     * Internal storage for the rabbitmq connection object.
     *
     * @var \Maleficarum\Rabbitmq\Connection\Connection
     */
    protected $rabbitmqStorage = null;

    /* ------------------------------------ Class Property END ----------------------------------------- */

    /* ------------------------------------ Class Methods START ---------------------------------------- */

    /**
     * Inject a new rabbitmq connection object.
     *
     * @param \Maleficarum\Rabbitmq\Connection\Connection $connection
     *
     * @return \Maleficarum\Rabbitmq\Dependant
     */
    public function setQueue(\Maleficarum\Rabbitmq\Connection\Connection $connection) {
        $this->rabbitmqStorage = $connection;

        return $this;
    }

    /**
     * Fetch the currently assigned rabbitmq connection object.
     *
     * @return \Maleficarum\Rabbitmq\Connection\Connection|null
     */
    public function getQueue(): ?\Maleficarum\Rabbitmq\Connection\Connection {
        return $this->rabbitmqStorage;
    }

    /**
     * Detach the currently assigned rabbitmq connection object.
     *
     * @return \Maleficarum\Rabbitmq\Dependant
     */
    public function detachQueue() {
        $this->rabbitmqStorage = null;

        return $this;
    }

    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
