<?php

namespace Maleficarum\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Internal storage for context
     *
     * @var string|null
     */
    private static $context;

    /* ------------------------------------ Setup START ------------------------------------------------ */
    protected function setUp() {
        parent::setUp();

        $this->setContext($this->getName());

        $file = SRC_PATH . DIRECTORY_SEPARATOR . 'Ioc' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, str_replace('Tests\\', '', static::class)) . '.ioc.php';

        if (is_readable($file)) {
            require $file;
        }
    }

    protected function tearDown() {
        parent::tearDown();

        $namespaces = new \ReflectionProperty('Maleficarum\Ioc\Container', 'namespaces');
        $namespaces->setAccessible(true);
        $namespaces->setValue([]);
        $namespaces->setAccessible(false);

        $initializers = new \ReflectionProperty('Maleficarum\Ioc\Container', 'builders');
        $initializers->setAccessible(true);
        $initializers->setValue([]);
        $initializers->setAccessible(false);

        $dependencies = new \ReflectionProperty('Maleficarum\Ioc\Container', 'shares');
        $dependencies->setAccessible(true);
        $dependencies->setValue([]);
        $dependencies->setAccessible(false);

        $loadedDefinitions = new \ReflectionProperty('Maleficarum\Ioc\Container', 'loadedDefinitions');
        $loadedDefinitions->setAccessible(true);
        $loadedDefinitions->setValue([]);
        $loadedDefinitions->setAccessible(false);
    }
    /* ------------------------------------ Setup END -------------------------------------------------- */

    /* ------------------------------------ Helper methods START --------------------------------------- */
    /**
     * Set object property value
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    protected function setProperty($object, string $property, $value) {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
        $reflection->setAccessible(false);
    }

    /**
     * Get object property value
     *
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    protected function getProperty($object, string $property) {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $value = $reflection->getValue($object);
        $reflection->setAccessible(false);

        return $value;
    }
    /* ------------------------------------ Helper methods END ----------------------------------------- */

    /* ------------------------------------ Setters & Getters START ------------------------------------ */
    /**
     * Get context
     *
     * @return string|null
     */
    public function getContext() {
        return self::$context;
    }

    /**
     * Set context
     *
     * @param string $context
     *
     * @return $this
     */
    public function setContext($context) {
        self::$context = $context;

        return $this;
    }
    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
