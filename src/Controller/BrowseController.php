<?php

namespace Drupal\tvmaze\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\tvmaze\API\ShowRepository;
use Drupal\tvmaze\Form\SearchForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Search for and view details of TV shows.
 */
class BrowseController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tvmaze.repository'),
      $container->get('request_stack')
    );
  }

  /**
   * The API repository.
   *
   * @var \Drupal\tvmaze\API\ShowRepository
   */
  protected $repository;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * Controller ctor.
   *
   * @param \Drupal\tvmaze\API\ShowRepository $repository
   *   The API repository.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The current request.
   */
  public function __construct(
    ShowRepository $repository,
    RequestStack $requestStack
  ) {
    $this->repository = $repository;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * Renders the search form.
   *
   * If keywords have been provided, calls the API and returns a list of
   * results.
   *
   * @return array
   *   The render array.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function index() {
    $build = [
      $this->formBuilder()->getForm(SearchForm::class),
    ];
    $keywords = $this->request->get('keywords');
    if ($keywords) {
      $build[] = [
        '#type' => 'table',
        '#header' => [
          'thumbnail' => $this->t('Thumbnail'),
          'id' => $this->t('ID'),
          'title' => $this->t('Title'),
        ],
        '#empty' => $this->t('No results.'),
        '#rows' => array_map(function ($show) {
          return [
            'thumbnail' => [
              'data' => [
                '#type' => 'html_tag',
                '#tag' => 'img',
                '#attributes' => [
                  'src' => $show['show']['image']['medium'],
                ],
              ],
            ],
            'id' => $show['show']['id'],
            'name' => [
              'data' => [
                '#type' => 'link',
                '#title' => $show['show']['name'],
                '#url' => Url::fromRoute('tvmaze.show', ['id' => $show['show']['id']]),
              ],
            ],
          ];
        }, $this->repository->searchShows($keywords)),
      ];
    }
    return $build;
  }

  /**
   * Displays a specific show by ID.
   *
   * @param int $id
   *   The show ID.
   *
   * @return array
   *   The render array.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function show(int $id) {
    $show = $this->repository->getShow($id);
    // Safely render HTML in summary.
    $show['summary'] = [
      '#markup' => $show['summary'],
    ];
    return [
      '#theme' => 'tvmaze_show',
      '#show' => $show,
    ];
  }

  /**
   * The _title_callback implementation.
   *
   * @param int $id
   *   The show id.
   *
   * @return string
   *   The show title.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function showTitle(int $id): string {
    $show = $this->repository->getShow($id);
    return $show['name'];
  }

}
