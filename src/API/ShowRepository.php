<?php

namespace Drupal\tvmaze\API;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\ClientInterface;

/**
 * Simplified interface for executing commands against the TVmaze public API.
 *
 * Allows for searching for shows based on title, and retrieval of
 * details on specific shows.
 */
class ShowRepository {

  const BASE_URL = 'https://api.tvmaze.com';

  /**
   * The Guzzle HTTP Client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * Our cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Repository ctor.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The Guzzle HTTP client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Our cache bin.
   */
  public function __construct(
    ClientInterface $client,
    CacheBackendInterface $cache
  ) {
    $this->client = $client;
    $this->cache = $cache;
  }

  /**
   * Sends a GET request to the API.
   *
   * @param string $path
   *   The API command to run.
   * @param array $params
   *   Query string parameters.
   *
   * @return mixed
   *   The decoded JSON response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function request(string $path, array $params = []) {
    $response = $this->client->request(
      'GET',
      static::BASE_URL . $path,
      [
        'query' => $params,
      ]
    );
    return Json::decode($response->getBody()->getContents());
  }

  /**
   * Returns a list of matching shows.
   *
   * @param string $keywords
   *   Search keywords.
   *
   * @return mixed
   *   The list of results.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function searchShows(string $keywords) {
    return $this->request('/search/shows', [
      'q' => $keywords,
    ]);
  }

  /**
   * Returns details on a show specified by its ID.
   *
   * Caches show details to prevent unnecessary API calls.
   *
   * @param int $id
   *   The show ID.
   *
   * @return mixed
   *   The show details.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getShow(int $id) {
    $cid = 'show:' . $id;
    if ($cache = $this->cache->get($cid)) {
      $show = $cache->data;
    }
    else {
      $show = $this->request('/shows/' . $id);
      $show['cast'] = $this->request('/shows/' . $id . '/cast');
      $this->cache->set($cid, $show);
    }
    return $show;
  }

}
