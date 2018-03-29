<?php
/**
 * This trait provides functionality common to all classes dependant on the \Maleficarum\Rabbitmq namespace
 */
declare (strict_types=1);

namespace Maleficarum\Rabbitmq;

trait Dependant {
    /* ------------------------------------ Class Property START --------------------------------------- */

    /**
     * Internal storage for the rabbitmq manager object.
     *
     * @var \Maleficarum\Rabbitmq\Manager\Manager
     */
    protected $rabbitmqStorage = null;

    /* ------------------------------------ Class Property END ----------------------------------------- */

    /* ------------------------------------ Class Methods START ---------------------------------------- */

    /**
     * Inject a new rabbitmq manager object.
     *
     * @param \Maleficarum\Rabbitmq\Manager\Manager $connection
     * @return \Maleficarum\Rabbitmq\Dependant
     */
    public function setQueue(\Maleficarum\Rabbitmq\Manager\Manager $manager) {
        $this->rabbitmqStorage = $manager;

        return $this;
    }

    /**
     * Fetch the currently assigned rabbitmq manager object.
     *
     * @return \Maleficarum\Rabbitmq\Manager\Manager|null
     */
    public function getQueue() : ?\Maleficarum\Rabbitmq\Manager\Manager {
        return $this->rabbitmqStorage;
    }

    /**
     * Detach the currently assigned rabbitmq manager object.
     *
     * @return \Maleficarum\Rabbitmq\Dependant
     */
    public function detachQueue() {
        $this->rabbitmqStorage = null;

        return $this;
    }

    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
