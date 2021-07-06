<?php

namespace core_fw\di_container;
use core_fw\di_container\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

class Container implements ContainerInterface
{
    private $services = [];

    /**
     * Container constructor.
     * @param array $services
     */
    public function __construct()
    {
        $this->services['db_service'] = '\core_fw\DatabaseService';
    }


    /**
     * set an instances in services
     * @param string $key
     * @param $value
     * @return $this
     */
    public function set(string $key, $value)
    {
        $this->services[$key] = $value;
        return $this;
    }

    /**
     * returns an instance of the class
     * @param string $id
     * @return object|ReflectionClass
     * @throws ReflectionException|NotFoundException
     */
    public function get(string $id)
    {
        // TODO: Implement get() method.
        $item = $this->resolve($id);
        if (!($item instanceof ReflectionClass)) {
            return $item;
        }
        return $this->getInstance($item);
    }

    /**
     * check if a class available and instantiable
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        try {
            $item = $this->resolve($id);
        } catch (NotFoundException $e) {
            return false;
        }
        return $item->isInstantiable();
    }

    /**
     * resolve instance of a class
     * @param string $id
     * @return ReflectionClass
     * @throws NotFoundException
     */
    private function resolve(string $id)
    {
        try {
            $name = $id;
            if (isset($this->services[$id])) {
                echo 'Trieddddddd';
                $name = $this->services[$id];
                if (is_callable($name)) {
                    return $name();
                }
            }
            return (new ReflectionClass($name));
        } catch (ReflectionException $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * create the instance of the class
     * @param ReflectionClass $item
     * @return object
     * @throws ReflectionException|NotFoundException
     */
    private function getInstance(ReflectionClass $item): object
    {
        $constructor = $item->getConstructor();

        if (is_null($constructor) || $constructor->getNumberOfRequiredParameters() == 0){
            return $item->newInstance();
        }
        $params = [];
        foreach ($constructor->getParameters() as $param) {
            if ($type = $param->getType()) {
                $params[] = $this->get($type->getName());
            }
        }
        return $item->newInstanceArgs($params);
    }
}