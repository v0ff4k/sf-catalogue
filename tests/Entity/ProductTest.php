<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ProductTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testProductCreation(): void
    {
        $product = new Product();
        $product->setName('Test Product');
        $product->setPrice('99.99');
        $product->setStatus('active');

        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals('99.99', $product->getPrice());
        $this->assertEquals('active', $product->getStatus());
        $this->assertNull($product->getId());
    }

    public function testProductValidation(): void
    {
        $product = new Product();
        $product->setName('');
        $product->setPrice('-10');
        $product->setStatus('invalid');

        $errors = $this->validator->validate($product);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testProductStatusValidation(): void
    {
        $product = new Product();
        $product->setName('Test');
        $product->setPrice('10');
        $product->setStatus('active');

        $errors = $this->validator->validate($product);
        $this->assertEquals(0, count($errors));

        $product->setStatus('inactive');
        $errors = $this->validator->validate($product);
        $this->assertEquals(0, count($errors));

        $product->setStatus('invalid');
        $errors = $this->validator->validate($product);
        $this->assertGreaterThan(0, count($errors));
    }
}
