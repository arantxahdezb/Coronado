<?php

namespace Drupal\coronado_recipes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class coronado_recipesController extends ControllerBase
{
  /**
   * Display the markup.
   *
   * @return array
   */

  public function listRecipes()
  {
    $contenido = array();

    $url = Url::fromRoute('coronado_recipes.addRecipe');
    $project_link = Link::fromTextAndUrl(t('Crear nueva receta'), $url);
    $project_link = $project_link->toRenderable();
    $project_link['#attributes'] = array('class' => array('button', 'button-action', 'button--primary', 'button--small'));

    $contenido['boton'] =  array(
      '#markup' => '<i> ' . render($project_link) . '</i><br><br>',
    );

    $rows = array();
    $rows =  $this->get_recipes();

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

  function get_recipes()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_recipes', 'p')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->fields('p', ['id', 'name']);
    $query->orderBy('created_at', 'DESC');
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

      $editar_link = Link::fromTextAndUrl(t('Editar'), Url::fromUri($base_url . '/admin/content/recipe/' . $row['id'] . '/edit'))->toString();
      $row['editar'] = $editar_link;

      $eliminar_link = Link::fromTextAndUrl(t('Eliminar'), Url::fromUri($base_url . '/admin/content/recipe/' . $row['id'] . '/delete'))->toString();
      $row['eliminar'] = $eliminar_link;

      $rows[] =  $row;
    }

    return $rows;
  }

  public function home()
  {

    $temporalitys = array(
      array("name" => "Desayuno", "url" => "desayuno"),
      array("name" => "Compartir con la pareja", "url" => "compartir-con-pareja"),
      array("name" => "Compartir con amigos", "url" => "compartir-con-amigos"),
      array("name" => "Compartir con la familia", "url" => "compartir-con-la-familia"),
      array("name" => "Para los más pequeños", "url" => "para-los-mas-pequenos"),
      array("name" => "Fiestas Infantiles", "url" => "fiestas-infantiles"),
      array("name" => "Ocasiones especiales", "url" => "ocasion-especiales"),
      array("name" => "Para el calor", "url" => "para-el-calor"),
      array("name" => "Para el frío", "url" => "para-el-frio"),

      array("name" => "San Valentín", "url" => "san-valentin"),
      array("name" => "Mes Patrio", "url" => "mes-patrio"),
      array("name" => "Halloween", "url" => "halloween"),
      array("name" => "Navidad", "url" => "navidad"),

      // array("name" => "Comida Familiar", "url" => "comida-familiar"),
      // array("name" => "Cena Romántica", "url" => "cena-romantica"),
      // array("name" => "Reunión", "url" => "reunion"),

    );
    $connection = \Drupal::database();
    $query = $connection->select('coronado_product', 'p');
    $query->fields('p', ['name', 'url']);
    $result = $query->execute();
    $products = $result->fetchAll(\PDO::FETCH_ASSOC);

    $filters = array();
    $filters["temporalitys"] = $temporalitys;
    $filters["products"] = $products;
    global $base_url;
    return [
      '#theme' => 'coronado_recipess_home_page',
      '#filters' => $filters,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => 'Conoce todas las recetas que Coronado tiene para ti.',
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' => 'Recetas | Coronado',
            ],
            'title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'keywords',
                'content' => 'Recetas Coronado ',
              ],
            ],
            'meta_keywords',
          ],
          [
            [
              '#tag' => 'link',
              '#attributes' => [
                'rel' => 'canonical',
                'href' => $base_url . '/recetas-coronado'
              ],
            ],
            'canonical',
          ],
        ],
      ],
    ];
  }

  function recipes()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_recipes', 'p');
    $query->fields('p', ['name', 'url', 'time', 'product', 'level', 'image_preview','temporality']);
    $query->orderBy('created_at', 'DESC');
    $result = $query->execute();
    $recipes = $result->fetchAll();

    foreach ($recipes as $recipe) {
      $file = File::load($recipe->image_preview);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $recipe->image_preview = $imageSRC;
      }
      $recipe->temporality = unserialize($recipe->temporality);
    }
    global $base_url;
    return [
      '#theme'  => 'coronado_recipes_grid',
      '#recipes' => $recipes,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => 'Conoce todas las recetas que Coronado tiene para ti.',
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' => 'Recetas | Coronado',
            ],
            'title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'keywords',
                'content' => 'Recetas Coronado ',
              ],
            ],
            'meta_keywords',
          ],
          [
            [
              '#tag' => 'link',
              '#attributes' => [
                'rel' => 'canonical',
                'href' => $base_url . '/recetas-coronado'
              ],
            ],
            'canonical',
          ],
        ],
      ],
    ];
  }

  function filter($arg = null, $arg1 = null)
  {
    $connection = \Drupal::database();
    $query = $connection->select('coronado_recipes', 'r')->fields('r', ['name', 'url', 'time', 'product', 'level', 'image_preview']);
    $query->orderBy('r.created_at', 'DESC');
    switch ($arg) {
      case "producto":
        $query->innerJoin('coronado_product', 'p', 'p.name = r.product');
        $query->condition('p.url', $arg1);
        break;
      case "ocasion":
        $query->condition('temporality', "%" . $query->escapeLike('s:' . strlen($arg1) . ':"' . $arg1 . '";s:' . strlen($arg1) . ':"' . $arg1 . '";') . "%", 'LIKE');
        break;
      case "tiempo":
        if ($arg1 == "10-30min") {
          $query->condition('time', [10, 30], 'BETWEEN');
        } elseif ($arg1 == "30-60min") {
          $query->condition('time', [30, 60], 'BETWEEN');
        } elseif ($arg1 == "90-180min") {
          $query->condition('time', [90, 180], 'BETWEEN');
        } else {
          throw new NotFoundHttpException();
        }
        break;
      case "habilidad":
        $query->condition('level', $arg1);
        break;
      default:
        throw new NotFoundHttpException();
        break;
    }
    $result = $query->execute();
    $recipes = $result->fetchAll();

    foreach ($recipes as $recipe) {

      $file = File::load($recipe->image_preview);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $recipe->image_preview = $imageSRC;
      }
    }
    global $base_url;
    return [
      '#theme' => 'coronado_recipes_grid',
      '#recipes' => $recipes,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => 'Conoce todas las recetas que Coronado tiene para ti.',
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' => 'Recetas | Coronado',
            ],
            'title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'keywords',
                'content' => 'Recetas Coronado ',
              ],
            ],
            'meta_keywords',
          ],
          [
            [
              '#tag' => 'link',
              '#attributes' => [
                'rel' => 'canonical',
                'href' => $base_url . '/recetas-coronado'
              ],
            ],
            'canonical',
          ],
        ],
      ],
    ];
  }

  function recipe_detail($arg = null)
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_recipes', 'p');
    $query->fields('p');
    $query->condition('url', $arg);
    $result = $query->execute();
    $recipe = $result->fetchAssoc();
    $recipe["steps"] = unserialize($recipe["steps"]);
    $recipe["temporality"] = unserialize($recipe["temporality"]);
    $recipe["ingredients"] = unserialize($recipe["ingredients"]);
    $recipe["title"] = str_replace('Coronado®', "<span class='label-coronado'></span>", $recipe["name"]);

    $file = File::load($recipe['image_preview']);

    if ($file != null) {
      $imageSRC = file_create_url($file->getFileUri());
      $recipe['image_preview'] = $imageSRC;
    }

    $file = File::load($recipe['image']);

    if ($file != null) {
      $imageSRC = file_create_url($file->getFileUri());
      $recipe['image'] = $imageSRC;
    }

    $file = File::load($recipe['PDF']);
    if ($file != null) {
      $PDFSRC = file_create_url($file->getFileUri());

      $recipe['PDF'] = $PDFSRC;
    }

    $query = $connection->query("SELECT name, url, time, product, level, image_preview FROM coronado_recipes WHERE product IN( SELECT NAME FROM coronado_product WHERE product_type_id = ( SELECT product_type_id FROM coronado_product WHERE NAME = :product) ) and id != :id order by rand() limit 2", [
      ':product' => $recipe['product'],
      ':id' => $recipe['id'],
    ]);

    $recipe["recipes"] = $query->fetchAll();

    foreach ($recipe["recipes"] as $recipes) {
      $file = File::load($recipes->image_preview);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $recipes->image_preview = $imageSRC;
      }
    }

    $steps = null;

    foreach ($recipe["steps"] as $step) {
      $steps = $steps . '{"@type": "HowToStep", "text": "' . $step . '"},';
    }

    $ingredients = null;

    foreach ($recipe["ingredients"] as $ingredient) {
      $ingredients = $ingredients . '"' . $ingredient . '",';
    }

    $script = '{"@context": "https://schema.org/",
    "@type": "Recipe",
    "name": "' . $recipe["name"] . '",
    "image": [
      "' . $recipe["image_preview"] . '"
    ],
    "author": {
      "@type": "Organization",
      "name": "Coronado®"
    },
    "description": "' . $recipe["description"] . '",
    "totalTime": "PT' . $recipe["time"] . 'M",
    "keywords": "' . $recipe["meta_keywords"] . '",
    "recipeIngredient": [
      ' . substr($ingredients, 0, -1) . '
      ],
    "recipeInstructions": [
        ' . substr($steps, 0, -1) . '
    ]}';

    return [
      '#theme' => 'coronado_recipes_detail',
      '#recipe' => $recipe,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'title',
              '#value' => $recipe['meta_title'],
            ],
            'title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => $recipe['meta_description'],
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'keywords',
                'content' => $recipe['meta_keywords'],
              ],
            ],
            'meta_keywords',
          ],
          [
            [
              '#type'  => 'html_tag',
              '#tag'   => 'script',
              '#attributes' => [
                'type' => 'application/ld+json',
              ],
              '#value' =>  $script,
            ],
            'rich_result',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:title',
                'content' => $recipe['meta_title'],
              ],
            ],
            'meta_og_title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:description',
                'content' => $recipe['meta_description'],
              ],
            ],
            'meta_og_description',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:image',
                'content' => $recipe['image_preview'],
              ],
            ],
            'meta_og_image',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:site_name',
                'content' => "https://www.coronado.com.mx/",
              ],
            ],
            'meta_og_site_name',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:type',
                'content' => "article",
              ],
            ],
            'meta_og_type',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'twitter:card',
                'content' => "summary_large_image",
              ],
            ],
            'meta_twitter_card',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'twitter:site',
                'content' => "https://www.coronado.com.mx/"
              ],
            ],
            'meta_twitter_site',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'twitter:title',
                'content' => $recipe['meta_title'],
              ],
            ],
            'meta_twitter_title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'twitter:description',
                'content' => $recipe['meta_description'],
              ],
            ],
            'meta_twitter_description',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'twitter:image',
                'content' => $recipe['image_preview'],
              ],
            ],
            'meta_twitter_image',
          ],
        ],
      ],
    ];
  }
}
