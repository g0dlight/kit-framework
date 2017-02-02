<?php

use Kit\Core\Container;

include BASE_PATH . '../Stubs/ContainerStub.php';

use ContainerTest\FoodInterface;
use ContainerTest\movableInterface;
use ContainerTest\Wild;
use ContainerTest\Animal;
use ContainerTest\God;
use ContainerTest\Sea;
use ContainerTest\Apple;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    public function testBindAndSingleton()
    {
        Container::singleton(FoodInterface::class, Apple::class);

        Container::singleton(Animal::class);

        $container = Container::instance();


        $apple = $container->resolve(FoodInterface::class);

        $this->assertEquals(Apple::class, get_class($apple));

        $apple2 = $container->resolve(FoodInterface::class);

        $this->assertSame($apple, $apple2);


        $animal = $container->resolve(Animal::class);

        $this->assertEquals(Animal::class, get_class($animal));

        $animal2 = $container->resolve(Animal::class);

        $this->assertSame($animal, $animal2);
    }

    public function testResolveWild()
    {
        $container = Container::instance();

        $wild = $container->resolve(Wild::class);

        $this->assertEquals(get_class($wild), Wild::class);

        $wild2 = $container->resolve(Wild::class);

        $this->assertEquals(get_class($wild2), Wild::class);

        $this->assertNotSame($wild, $wild2);
    }

    public function testResolveAnimal()
    {
        $container = Container::instance();

        $animal = $container->resolve(Animal::class);

        $this->assertEquals(get_class($animal), Animal::class);
    }

    public function testResolveGodClosure()
    {
        $test = $this;

        Container::bind(movableInterface::class, function($container, $parameters) use ($test) {
            $test->assertEquals(get_class($container), Container::class);

            $test->assertTrue( is_array($parameters) );

            return new Animal();
        });

        $container = Container::instance();

        $animal = $container->resolve(movableInterface::class);

        $this->assertEquals(get_class($animal), Animal::class);
    }

    /**
     * @depends testBindAndSingleton
     */
    public function testResolveAnimalFood()
    {
        $container = Container::instance();

        $food = $container->resolveMethod(new Animal, 'getFood');

        $this->assertEquals(Apple::class, $food);
    }

    /**
     * @depends testResolveAnimalFood
     */
    public function testResolveAnimalDrink()
    {
        $container = Container::instance();

        $amount = $container->resolveMethod(Animal::class, 'getDrink', [13]);

        $this->assertEquals(8, $amount);
    }

    /**
     * @expectedException Kit\Exception\CoreException
     */
    public function testResolveGod()
    {
        $container = Container::instance();

        $container->resolve(God::class);
    }

    /**
     * @expectedException Kit\Exception\CoreException
     */
    public function testResolveSea()
    {
        $container = Container::instance();

        $container->resolve(Sea::class);
    }

    /**
     * @expectedException Kit\Exception\CoreException
     */
    public function testBindAgain()
    {
        Container::bind(FoodInterface::class, Apple::class);
    }

    /**
     * @expectedException Kit\Exception\CoreException
     */
    public function testResolveAnimalPee()
    {
        $container = Container::instance();

        $container->resolveMethod(Animal::class, 'pee');
    }
}