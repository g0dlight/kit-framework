<?php

namespace Kit\Core;

use ReflectionClass;
use ReflectionMethod;
use Kit\Exception\CoreException;

/**
 * Class Container
 * @package Kit\Core
 */
class Container
{
    /**
     * @var
     */
    protected static $instance;

    /**
     * @var array
     */
    protected static $bindings = [];

    /**
     * @var array
     */
    protected static $singletons = [];

    /**
     * @var array
     */
    protected $resolveStack = [];

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @return Container
     */
    public static function instance() : Container
    {
        if( ! self::$instance )
            self::$instance = new self();

        return self::$instance;
    }

    /**
     * @param $pointer
     * @param $target
     * @throws CoreException
     */
    public static function bind($pointer, $target)
    {
        if( isset(self::$bindings[$pointer]) )
            throw new CoreException('Can not bind [' . $target . '] to [' . $pointer . ' => ' . self::$bindings[$pointer] . ']');

        self::$bindings[$pointer] = $target;
    }

    /**
     * @param $pointer
     * @param null $target
     */
    public static function singleton($pointer, $target = NULL)
    {
        $singletonIndex = $pointer;

        if( $target ){
            self::bind($pointer, $target);
            $singletonIndex = $target;
        }

        self::$singletons[$singletonIndex] = TRUE;
    }

    /**
     * Container constructor.
     */
    private function __construct()
    {}

    /**
     * @param $class
     * @param array $additionalParameters
     * @return mixed|object
     */
    public function resolve($class, array $additionalParameters = [])
    {
        $class = self::$bindings[$class] ?? $class;

        if( isset( $this->instances[$class] ) )
            return $this->instances[$class];

        $instance = $this->resolveClass($class, $additionalParameters);

        if( isset( self::$singletons[$class] ) )
            $this->instances[$class] = $instance;

        return $instance;
    }

    /**
     * @param $class
     * @param $method
     * @param array $additionalParameters
     * @return mixed
     */
    public function resolveMethod($class, $method, array $additionalParameters = [])
    {
        $object = ( is_object( $class ) )? $class : $this->resolve( $class, $additionalParameters );

        $reflector = new ReflectionMethod($object, $method);

        if( ! $reflector->isPublic() )
            $this->resolveFailure($method);

        $dependencies = $this->resolveDependencies($reflector, $additionalParameters);

        return $reflector->invokeArgs($object, $dependencies);
    }

    /**
     * @param $class
     * @param array $additionalParameters
     * @return object
     */
    protected function resolveClass($class, array $additionalParameters)
    {
        if( isset( $this->resolveStack[$class] ) )
            $this->resolveFailure($class);

        $reflector = new ReflectionClass($class);

        if( ! $reflector->isInstantiable() )
            $this->resolveFailure($class);

        $this->resolveStack[$class] = $class;

        $instance = $this->resolveReflectionClass($reflector, $additionalParameters);

        unset( $this->resolveStack[$class] );

        return $instance;
    }

    /**
     * @param ReflectionClass $reflector
     * @param array $additionalParameters
     * @return object
     */
    protected function resolveReflectionClass(ReflectionClass $reflector, array $additionalParameters)
    {
        $constructor = $reflector->getConstructor();

        if( is_null( $constructor ) )
            return $reflector->newInstanceWithoutConstructor();

        $dependencies = $this->resolveDependencies($constructor, $additionalParameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * @param ReflectionMethod $reflector
     * @param array $additionalParameters
     * @return array
     */
    protected function resolveDependencies(ReflectionMethod $reflector, array $additionalParameters)
    {
        $dependencies = [];

        foreach( $reflector->getParameters() as $dependency ){
            $dependencyClass = $dependency->getClass();

            if( $dependencyClass ){
                $dependencies[] = $this->resolve( $dependencyClass->name );
            }
            else{
                $dependencies[] = array_shift( $additionalParameters );
            }
        }

        return $dependencies;
    }

    /**
     * @param $target
     * @throws CoreException
     */
    protected function resolveFailure($target)
    {
        $message = 'Can not resolve [' . $target . ']';

        if( ! empty($this->resolveStack) ){
            $previous = implode(', ', $this->resolveStack);

            $message .= ' while building [' . $previous . ']';
        }

        throw new CoreException($message);
    }
}