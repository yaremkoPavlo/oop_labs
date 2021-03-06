<?php
namespace models;
use product\Product;
use delivery\iDelivery;
use services\ServiceInterface;

class Order {
  private $total_price;
  private $user;
  private $goodsList;

  public function __construct(User $user)
  {
    $this->total_price  = 0;
    $this->user         = $user;
    $this->goodsList    = Array();
  }

  public function chooseProduct(Shop $shop, Product $product, int $number):array
  {
    if ($number <= $shop->viewGoods()[$product->getArticul()][1])
    {
      $this->goodsList[$product->getArticul()] = Array($product, $number);
    }
    return $this->goodsList;
  }

  public function removeGoods(Product $product, int $number):array
  {
    $key = $product->getArticul();
    if ($number < $this->goodsList[$key][1])
    {
      $this->goodsList[$key][1] -= $number;
    }
    else
    {
      unset($this->goodsList[$key]);
    }
    return $this->goodsList;
  }

  public function calculateTotalPrice():int
  {
    foreach ($this->goodsList as $key => $value) {
      $this->total_price += $this->goodsList[$key][0]->getPrice()
                          * $this->goodsList[$key][1];
    }
    return $this->total_price;
  }

  public function getDiscount(Shop $shop):float
  {
    $discaunt = $shop->setDiscount($this->user, $this->total_price);
    $this->total_price = $this->total_price * (1 - $discaunt);
    return $discaunt;
  }

  public function getTotalPrice():float
  {
    return $this->total_price;
  }

  public function getReservationForOrder(Shop $shop)
  {
    $shop->setReservation($this->goodsList);
  }

  public function setPaymentDetails (
                                Shop $shop,
                                int $numberInArrayPayment
                                    )
  {
    return $shop->setPaymentDetails($numberInArrayPayment, $this->total_price, $this->user);
  }

  public function getDeliveryDetails(
                                  Shop $shop,
                                  iDelivery $delivery,
                                  string $address
                                    ):int
  {
    $result = $shop->setDeliveryDetails($delivery, $address);
    $this->total_price += $result;
    return $result;
  }

  public function viewBasket():array
  {
    return $this->goodsList;
  }

  public function getService(ServiceInterface $service)
  {
      $this->total_price += $service->getService();
  }
}
