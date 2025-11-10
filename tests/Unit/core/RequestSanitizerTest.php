<?php

use App\Services\Core\RequestSanitizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RequestSanitizerTest extends TestCase
{
  #[Test]
  public function it_trims_and_normalizes_values(): void
  {
    $data = [
      'name' => ' taro ',
      'age' => '',
      'tree' => [
        'tree1' => 1,
        'tree2' => 2
      ]
    ];

    $cleaned = RequestSanitizer::normalize($data);

    $this->assertSame([
      'name' => 'taro',
      'age' => null,
      'tree' => [
        'tree1' => 1,
        'tree2' => 2
      ]
    ], $cleaned);
  }
  
  #[Test]
  public function normalize_remain_int_bool_null(): void
  {
    $data = ['age' => 0, 'flag' => false, 'none' => null];
    
    $result = RequestSanitizer::normalize($data);

    $this->assertSame(['age' => 0, 'flag' => false, 'none' => null], $result);
  }

  #[Test]
  public function normalize_multi_level_nesting(): void
  {
    $data = [
      'nest1' => [
        'nest2' => [
          'nest3' => [
            'a' => 1,
            'b' => 2
          ]
        ]
      ]
    ];
    
    $result = RequestSanitizer::normalize($data);

    $this->assertSame(1, $result['nest1']['nest2']['nest3']['a']);
    $this->assertSame(2, $result['nest1']['nest2']['nest3']['b']);
  }

  #[Test]
  public function normalize_only_space(): void
  {
    $data = ['only_space' => '   '];

    $result = RequestSanitizer::normalize($data);

    $this->assertSame(null, $result['only_space']);
  }
  

}