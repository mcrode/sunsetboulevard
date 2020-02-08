<?php  
/**  
 * @file  
 * Contains Drupal\import_sunset_boulevard_products\Form\ImportSunsetProductsForm.  
 */  

namespace Drupal\import_sunset_boulevard_products\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\commerce_store\Entity\Store;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_price\Price;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

class ImportSunsetProductsForm extends FormBase {

  public function getFormId() 
  {
    return 'sunset_bouledvard_import_products_form'; 
  }

  public function buildForm( array $form, FormStateInterface $form_state ) 
  {

    $last_import_timestamp = \Drupal::database()->query("SELECT data FROM {config} WHERE name = 'sunset_last_import'")->fetch()->data;

    $form['last_import'] = [  
      '#type' => 'hidden',  
      '#title' => $this->t('Last import'),
      '#description' => $this->t('Last import of products'),
      '#value' => time(),
    ];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import products'),
      '#button_type' => 'primary',
    ];

    if( !empty( $last_import_timestamp ) ) {
      
      $form['actions']['submit']['#prefix'] = $this->t('Last import: ') . date('Y-m-d H:i:s', $last_import_timestamp) . '<br><br>';

    }

    return $form;

  }

  public function submitForm( array &$form, FormStateInterface $form_state ) {

    $created_products = $this->import_products();

    $new_timestamp = $form_state->getValue('last_import');

    $last_import_timestamp = \Drupal::database()->query("SELECT data FROM {config} WHERE name = 'sunset_last_import'")->fetch()->data;

    if( empty( $last_import_timestamp ) ) {

      $result = \Drupal::database()->insert('config')
      ->fields([
        'name' => 'sunset_last_import',
        'data' => $new_timestamp,
      ])
      ->execute();

    }
    else {

      $result = \Drupal::database()->update('config')
      ->fields(['data' => $new_timestamp])
      ->condition('name', 'sunset_last_import', '=')
      ->execute();

    }

    drupal_set_message( t('Products imported!') );
  
  }

  public function import_products() {

    $products = json_decode( file_get_contents('https://api.punkapi.com/v2/beers') );
    
    foreach( $products as $product ) {

      if( $product_id = $this->product_exists( $product->name )->product_id ) {

        $product = Product::load($product_id);
        //Update product...

      }
      else {

        $file = File::create([
          'uri' => $product->image_url,
        ]);
        $file->save();

        $product = Product::create([
          'uid' => 1,
          'type' => 'default',
          'title' => $product->name,
          'body' => $product->tagline . ' ' . $product->description,
          'stores' => [Store::load(1)],
          'variations' => [
            ProductVariation::create([
                'type' => 'default',
                'sku' => 'product-' . $product->name,
                'status' => TRUE,
                'price' => new Price('12', 'DKK'),
              ])
          ],
          'field_images' => [
            ['target_id' => $file->id()]
          ]
        ]);
        $product->save();

      }

    }

  }

  public function product_exists( $product_name ) {

    $product_id = \Drupal::database()->query("SELECT product_id FROM {commerce_product_field_data} WHERE title = '{$product_name}'")->fetch();

    return $product_id;

  }

}