<?php
namespace ValuePad\Core\Customer\Objects;

class PayoffPurchase
{
    /**
     * @var float
     */
    private $price;
    public function setPrice($price) { $this->price = $price; }
    public function getPrice() { return $this->price; }

    /**
     * @var object
     */
    private $product;
    public function setProduct($product)  { $this->product = $product; }
    public function getProduct() { return $this->product; }
}
