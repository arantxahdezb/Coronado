<?php

namespace Drupal\coronado\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Link;

class coronadoController extends ControllerBase
{
  /**
   * Display the markup.
   *
   * @return array
   */

  public function listBanners()
  {
    $contenido = array();

    $url = Url::fromRoute('coronado.addBanner');
    $project_link = Link::fromTextAndUrl(t('Crear nuevo banner'), $url);
    $project_link = $project_link->toRenderable();
    $project_link['#attributes'] = array('class' => array('button', 'button-action', 'button--primary', 'button--small'));

    $contenido['boton'] =  array(
      '#markup' => '<i> ' . render($project_link) . '</i><br><br>',
    );

    $rows = array();
    $rows =  $this->get_banners();

    // Build a render array which will be themed as a table with a pager.
    $contenido['table'] = [
      '#rows' => $rows,
      '#header' => [t('Id'), t('Title'), t('Editar'), t('Eliminar')],
      '#type' => 'table',
      '#empty' => t('No content available.'),
    ];
    $contenido['pager'] = [
      '#type' => 'pager',
      '#weight' => 10,
    ];

    return $contenido;
  }

  function get_banners()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_home', 'p')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->fields('p', ['id', 'title']);
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
      $editar_link = Link::fromTextAndUrl(t('Editar'), Url::fromUri($base_url . '/admin/content/home/' . $row['id'] . '/edit'))->toString();
      $row['editar'] = $editar_link;
      
      $eliminar_link = Link::fromTextAndUrl(t('Eliminar'), Url::fromUri($base_url . '/admin/content/home/' . $row['id'] . '/delete'))->toString();
      $row['eliminar'] = $eliminar_link;

      $rows[] =  $row;
    }

    return $rows;
  }

  function banners()
  {
    $connection = \Drupal::database();

    $query = $connection->select('coronado_home', 'p');
    $query->fields('p');
    $result = $query->execute();
    $banners = $result->fetchAll();

    foreach ($banners as $banner) {
      $file = File::load($banner->image_desktop);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $banner->image_desktop = $imageSRC;
      }

      $file = File::load($banner->image_mobile);
      if ($file != null) {
        $imageSRC = file_create_url($file->getFileUri());

        $banner->image_mobile = $imageSRC;
      }
    }

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
    );

    $query = $connection->select('coronado_product', 'p');
    $query->fields('p', ['name', 'url']);
    $result = $query->execute();
    $products = $result->fetchAll(\PDO::FETCH_ASSOC);

    $profile = array();
    $profile["temporalitys"] = $temporalitys;
    $profile["products"] = $products;

    return [
      '#theme' => 'coronado_home_page',
      '#banners' => $banners,
      '#profile' => $profile,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => 'Disfruta de los nuevos productos Coronado. Descubre las recetas y nuestros productos en el sitio oficial. ',
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' => 'Home | Coronado',
            ],
            'title',
          ],
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'keywords',
                'content' => 'Coronado',
              ],
            ],
            'meta_keywords',
          ],
        ],
      ],
    ];
  }

  public function notFound()
  {
    return [
      '#theme' => 'coronado_404_not_found',
    ];
  }

  public function accessForbidden()
  {
    return [
      '#theme' => 'coronado_403_forbidden',
    ];
  }

  function how_we_work()
  {
    $config = $this->config('coronado.how_we_work');

    return [
      '#theme'         => 'coronado_how_we_work',
      '#how_we_work'       => $config->get('how_we_work.value'),
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => 'Recorremos miles de kilómetros en una extensa red de distribución que lleva nuestro dulce sabor a todo México, Estados Unidos y Latinoamérica',
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' => 'Cómo trabajamos | Coronado',
            ],
            'title',
          ],
        ],
      ],
    ];
  }

  function about_us()
  {
    $config = $this->config('coronado.about_us');
    $aboutus = [];

    $aboutus["title"] = $config->get('title');
    $aboutus["description"] = $config->get('description.value');

    return [
      '#theme'         => 'coronado_about_us',
      '#about_us'       => $aboutus,
      '#attached' => [
        'html_head' => [
          [
            [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => 'description',
                'content' => 'Descubre quiénes estamos detrás de la elaboración de los productos oficiales de Coronado',
              ],
            ],
            'meta_description',
          ],
          [
            [
              '#tag' => 'title',
              '#value' => 'Quiénes Somos | Coronado',
            ],
            'title',
          ],
        ],
      ],
    ];
  }

  function notice_of_privacy()
  {
    $config = $this->config('coronado.configcoronado');

    return [
      '#theme'         => 'coronado_notice_of_privacy',
      '#notice_of_privacy'       => $config->get('notice_of_privacy.value')
    ];
  }
}
