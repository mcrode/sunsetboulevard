<?php

namespace Drupal\import_sunset_boulevard_products\EventSubscriber;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cart Event Subscriber.
 */
class CartEventSubscriber implements EventSubscriberInterface {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(MessengerInterface $messenger, CartManagerInterface $cart_manager) {
    $this->messenger = $messenger;
    $this->cartManager = $cart_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      CartEvents::CART_ENTITY_ADD => [['addToCart', 100]],
      CartEvents::CART_ORDER_ITEM_UPDATE => [['updateQty', 100]],
      CartEvents::CART_ORDER_ITEM_REMOVE => [['removeOrderItem', 100]],
    ];
  }

  /**
   * Add a related product automatically
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *   The cart add event.
   *
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function addToCart(CartEntityAddEvent $event) {
    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $product_variation */
    $product_id = $event->getEntity()->getProductId();
    $product = Product::load( $product_id );
    $added_to_cart = $product->get('field_added_to_cart')->getValue()[0]['value'] + 1;
    $product->set('field_added_to_cart', $added_to_cart);
    $product->save();
    
  }

  public function updateQty(CartEntityAddEvent $event) {
      //Update based on qty
  }

  public function removeOrderItem(CartEntityAddEvent $event) {
      //Update based on qty
  }

}
