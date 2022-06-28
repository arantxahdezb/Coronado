<?php

namespace Drupal\coronado_recipes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

class SearchController extends ControllerBase
{
  /**
   * Display the markup.
   *
   * @return array
   */

  public function search()
  {
    $text = \Drupal::request()->request->get('keys');

    $connection = \Drupal::database();

    $query = $connection->select('coronado_recipes', 'p');
    $query->fields('p', ['name', 'url', 'time', 'product', 'level', 'image_preview']);
    $orGroup = $query->orConditionGroup()
      ->condition('name', "%" . $query->escapeLike($text) . "%", 'LIKE')
      ->condition('description', "%" . $query->escapeLike($text) . "%", 'LIKE');
    $query->condition($orGroup);
    $result = $query->execute();
    $recipes = $result->fetchAll();

    foreach ($recipes as $recipe) {
      $file = File::load($recipe->image_preview);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $recipe->image_preview = $imageSRC;
      }
    }

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
              '#value' => 'Búsqueda | Coronado',
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
        ],
      ],
    ];
  }

  function profile()
  {
    $product = \Drupal::request()->request->get('product');
    $level = \Drupal::request()->request->get('level');
    $temporality = \Drupal::request()->request->get('temporality');
    $time = \Drupal::request()->request->get('time');
    $connection = \Drupal::database();

    $query = $connection->select('coronado_recipes', 'p');
    $query->fields('p', ['name', 'url', 'time', 'product', 'level', 'image_preview']);
    $query->orderBy('p.created_at', 'DESC');
    $query->innerJoin('coronado_product', 'pt', 'pt.name = p.product');

    $orGroup = $query->orConditionGroup()
      ->condition('pt.url', $product)
      ->condition('p.temporality', "%" . $query->escapeLike('s:' . strlen($temporality) . ':"' . $temporality . '";s:' . strlen($temporality) . ':"' . $temporality . '";') . "%", 'LIKE');

    if (!is_null($level)) {
      $orGroup = $query->orConditionGroup()
        ->condition($orGroup)
        ->condition('p.level', $level);
    }

    if (!is_null($time)) {
      if ($time == "10 a 30 minutos") {
        $orGroup = $query->orConditionGroup()
          ->condition($orGroup)
          ->condition('p.time', [10, 30], 'BETWEEN');
      } elseif ($time == "30 a 60 minutos") {
        $orGroup = $query->orConditionGroup()
          ->condition($orGroup)
          ->condition('p.time', [30, 60], 'BETWEEN');
      } elseif ($time == "90 a 180 minutos") {
        $orGroup = $query->orConditionGroup()
          ->condition($orGroup)
          ->condition('p.time', [90, 180], 'BETWEEN');
      }
    }

    $query->condition($orGroup);

    $result = $query->execute();
    $recipes = $result->fetchAll();

    foreach ($recipes as $recipe) {
      $file = File::load($recipe->image_preview);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $recipe->image_preview = $imageSRC;
      }
    }

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
              '#value' => 'Perfilador | Coronado',
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
        ],
      ],
    ];
  }
}
