<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['name' => 'Ноутбук Dell XPS 15', 'price' => '129999.99', 'status' => 'active'],
            ['name' => 'Смартфон iPhone 15 Pro', 'price' => '89999.00', 'status' => 'active'],
            ['name' => 'Планшет iPad Air', 'price' => '59999.50', 'status' => 'active'],
            ['name' => 'Наушники Sony WH-1000XM5', 'price' => '24999.00', 'status' => 'active'],
            ['name' => 'Клавиатура механическая', 'price' => '8999.99', 'status' => 'active'],
            ['name' => 'Мышь беспроводная Logitech', 'price' => '3499.00', 'status' => 'active'],
            ['name' => 'Монитор 27 дюймов 4K', 'price' => '34999.00', 'status' => 'active'],
            ['name' => 'Веб-камера HD', 'price' => '4999.00', 'status' => 'active'],
            ['name' => 'Микрофон USB', 'price' => '6999.00', 'status' => 'inactive'],
            ['name' => 'Колонки Bluetooth', 'price' => '12999.00', 'status' => 'active'],
            ['name' => 'Жесткий диск SSD 1TB', 'price' => '7999.00', 'status' => 'active'],
            ['name' => 'Оперативная память 16GB', 'price' => '5999.00', 'status' => 'active'],
            ['name' => 'Видеокарта NVIDIA RTX 4070', 'price' => '89999.00', 'status' => 'active'],
            ['name' => 'Блок питания 750W', 'price' => '8999.00', 'status' => 'inactive'],
            ['name' => 'Материнская плата ASUS', 'price' => '19999.00', 'status' => 'active'],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setPrice($productData['price']);
            $product->setStatus($productData['status']);
            
            $manager->persist($product);
        }

        $manager->flush();
    }
}
