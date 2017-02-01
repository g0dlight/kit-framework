<?php

namespace ContainerTest;

interface FoodInterface
{}

class Wild
{
    public function __construct(Animal $a , Tree $b){}
}

class Animal
{
    public function getFood(FoodInterface $a){
        return get_class($a);
    }

    public function getDrink(int $amount)
    {
        if($amount > 10)
            $this->pee($amount);

        return $amount;
    }

    private function pee(int &$amount){
        $amount -= 5;
    }
}

class Tree
{
    public function __construct(Fruit $a){}
}

class Fruit implements FoodInterface
{
    public function __construct(){}
}

class Apple implements FoodInterface
{
    public function __construct(){}
}


class God
{
    private function __construct(){}
}

class Sea
{
    public function __construct(Sea $a){}
}



