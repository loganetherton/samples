<?php
namespace ValuePad\Core\Payment\Objects;

class Purchase
{
    /**
     * @var float
     */
    private $price;
    public function setPrice($amount) { $this->price = $amount; }
    public function getPrice() { return $this->price; }

    /**
     * @var object
     */
    private $product;
    public function setProduct($product) { $this->product = $product; }
    public function getProduct() { return $this->product; }
}
