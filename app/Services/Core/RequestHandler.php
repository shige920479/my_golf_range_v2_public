<?php
namespace App\Services\Core;

class RequestHandler
{
  public function __construct(
    private RequestValidator $validator
  ) {}

  public function getRequest(): array
  {
    $post = $_POST ?? [];
    $get = $_GET ?? [];
    $files = $_FILES ?? [];

    if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
      $this->validator->validateToken($post);
    }

    $params = array_merge($get, $post);
    $normalized = RequestSanitizer::normalize($params);

    return array_merge($normalized, $files);
  }
}