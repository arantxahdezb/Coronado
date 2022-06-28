<?php

namespace Drupal\coronado_products\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class coronado_productsController extends ControllerBase
{
  /**
   * Display the markup.
   *
   * @return array
   */

  public function listProduct()
  {
    $contenido = array();

    $url = Url::fromRoute('coronado_products.addProduct');
    $project_link = Link::fromTextAndUrl(t('Crear nuevo producto'), $url);
    $project_link = $project_link->toRenderable();
    $project_link['#attributes'] = array('class' => array('button', 'button-action', 'button--primary', 'button--small'));

    $contenido['boton'] =  array(
      '#markup' => '<i> ' . render($project_link) . '</i><br><br>',
    );

    $rows = array();
    $rows =  $this->get_products();

    // Build a render array which will be themed as a table with a pager.
    $contenido['table'] = [
      '#rows' => $rows,
      '#header' => [t('Id'), t('Nombre'), t('Editar'), t('Eliminar')],
      '#type' => 'table',
      '#empty' => t('No content available.'),
    ];
    $contenido['pager'] = [
      '#type' => 'pager',
      '#weight' => 10,
    ];

    return $contenido;
  }

  public function listProductType()
  {

    $contenido = array();

    $rows = array();
    $rows = $this->get_productTypes();

    // Build a render array which will be themed as a table with a pager.
    $contenido['table'] = [
      '#rows' => $rows,
      '#header' => [t('Id'), t('Nombre'), t('URL'), t('Editar')],
      '#type' => 'table',
      '#empty' => t('No content available.'),
    ];
    $contenido['pager'] = [
      '#type' => 'pager',
      '#weight' => 10,
    ];

    return $contenido;
  }

  function get_productTypes()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product_type', 'p')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->fields('p', ['id', 'name', 'url']);
    $result = $query->execute();

    $rows = [];

    global $base_url;
    foreach ($result as $row) {
      // Normally we would add some nice formatting to our rows
      // but for our purpose we are simply going to add our row
      // to the array.

      $row = (array) $row;


      // External Uri.
      //use Drupal\Core\Url;
      $editar_link = Link::fromTextAndUrl(t('Editar'), Url::fromUri($base_url . '/admin/content/product_type/' . $row['id'] . '/edit'))->toString();
      $row['editar'] = $editar_link;

      $rows[] =  $row;
    }

    return $rows;
  }

  function get_products()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product', 'p')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->fields('p', ['id', 'name']);
    $result = $query->execute();

    $rows = [];

    global $base_url;

    foreach ($result as $row) {
      // Normally we would add some nice formatting to our rows
      // but for our purpose we are simply going to add our row
      // to the array.

      $row = (array) $row;


      // External Uri.
      //use Drupal\Core\Url;
      $editar_link = Link::fromTextAndUrl(t('Editar'), Url::fromUri($base_url . '/admin/content/product/' . $row['id'] . '/edit'))->toString();
      $row['editar'] = $editar_link;

      $eliminar_link = Link::fromTextAndUrl(t('Eliminar'), Url::fromUri($base_url . '/admin/content/product/' . $row['id'] . '/delete'))->toString();
      $row['eliminar'] = $eliminar_link;

      $rows[] =  $row;
    }

    return $rows;
  }

  function categorie()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product_type', 'p');
    $query->fields('p');
    $result = $query->execute();
    $categories = $result->fetchAll();

    global $base_url;

    return [
      '#theme'         => 'coronado_products_home_page',
      '#categories'       => $categories,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => 'Conoce todos los productos untables y dulces que Coronado tiene para ti.',
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' => 'Productos | Coronado',
            ],
            'title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'keywords',
                'content' => 'Productos Coronado ',
              ],
            ],
            'meta_keywords',
          ],
          [
            [
              '#tag' => 'link',
              '#attributes' => [
                'rel' => 'canonical',
                'href' => $base_url . '/productos'
              ],
            ],
            'canonical',
          ],
        ],
      ],
    ];
  }

  function products_categorie($arg = null)
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product_type', 'p');
    $query->fields('p', ['id', 'meta_title', 'meta_description', 'meta_keywords']);
    $query->condition('url', $arg);
    $result = $query->execute();
    $producttype = $result->fetchAssoc();

    $query = $connection->select('coronado_product', 'p');
    $query->fields('p', ['name', 'url', 'image_preview', 'description_preview', 'code']);
    $query->condition('product_type_id', $producttype['id']);
    $result = $query->execute();
    $products = $result->fetchAll();

    foreach ($products as $product) {

      $file = File::load($product->image_preview);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $product->image_preview = $imageSRC;
      }
      $product->product_type_url = $arg;
    }

    return [
      '#theme'         => 'coronado_products_categorie',
      '#products'       => $products,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => $producttype['meta_description'],
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' => $producttype['meta_title'],
            ],
            'title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'keywords',
                'content' => $producttype['meta_keywords'],
              ],
            ],
            'meta_keywords',
          ],
        ]
      ],
    ];
  }

  function all_products()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product', 'p');
    $query->fields('p', ['name', 'url', 'image_preview', 'description_preview', 'code', 'product_type_id']);
    $result = $query->execute();
    $products = $result->fetchAll();

    foreach ($products as $product) {

      $file = File::load($product->image_preview);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $product->image_preview = $imageSRC;
      }

      $query = $connection->select('coronado_product_type', 'p');
      $query->fields('p', ['url']);
      $query->condition('id', $product->product_type_id);
      $result = $query->execute();
      $product_type_url = $result->fetchAssoc();
      $product->product_type_url = $product_type_url['url'];
    }

    global $base_url;

    return [
      '#theme'         => 'coronado_products_categorie',
      '#products'       => $products,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => 'Conoce todos los productos untables y dulces que Coronado tiene para ti.',
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' => 'Productos | Coronado',
            ],
            'title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'keywords',
                'content' => 'Productos Coronado ',
              ],
            ],
            'meta_keywords',
          ],
          [
            [
              '#tag' => 'link',
              '#attributes' => [
                'rel' => 'canonical',
                'href' => $base_url . '/productos'
              ],
            ],
            'canonical',
          ],
        ],
      ],
    ];
  }

  function product_detail($arg = null, $arg1 = null)
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_product', 'p');
    $query->fields('p');
    $query->condition('url', $arg1);
    $result = $query->execute();
    $product = $result->fetchAssoc();

    $query = $connection->select('coronado_product_type', 'p');
    $query->fields('p', ['name', 'url']);
    $query->condition('id', $product['product_type_id']);
    $result = $query->execute();
    $product_type = $result->fetchAssoc();

    $query = $connection->select('coronado_product', 'p');
    $query->fields('p', ['name', 'url', 'image_preview', 'description_preview', 'code']);
    
    $result = $query->execute();
    $products = $result->fetchAll();

    foreach ($products as $product_) {

      $file = File::load($product_->image_preview);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $product_->image_preview = $imageSRC;
      }
      $product_->product_type_url = $arg;
    }




    if ($product_type["url"] != $arg) {
      throw new NotFoundHttpException();
    } else {
      $product["categoriename"] = $product_type["name"];
    }

    $product["ranges"] = unserialize($product["ranges"]);

    foreach ($product["ranges"] as $index => $image) {
      $file = File::load($image["image"][$index][0]);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $product["ranges"][$index]["image"][$index][1] = $imageSRC;
      }
    }

    $product["name"] = str_replace('Coronado®', "<span class='label-coronado'></span>", $product["name"]);

    return [
      '#theme' => 'coronado_products_detail',
      '#product' => $product,
      '#products1' => 'hola',
      '#products' => $products,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => $product['meta_description'],
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' =>  $product['meta_title'],
            ],
            'title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'keywords',
                'content' => $product['meta_keywords'],
              ],
            ],
            'meta_keywords',
          ],
        ]
      ],
    ];
  }
}
