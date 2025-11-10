<?php
namespace App\Services\Validation\Facility;

use App\Services\Validation\BaseValidation;
use App\Services\Validation\FormRulesTrait;

class InitValidation extends BaseValidation
{
  use FormRulesTrait;

  public function rangeValidation(array $request): bool
  {
    $emptyCheck = 0;
    foreach ($request['name'] as $index => $value) {
      if($this->required('name_' . $index , $value ?? '')) {
        $emptyCheck++;
        $this->maxLength('name_' . $index, $value, 20);
      }
    }
    if(count($request['name']) === $emptyCheck) {
      $this->isUnique($request['name'], 'drivingRange');
    }

    return empty($this->getErrors());
  }
  public function rentalValidation(array $request): bool
  {
    $emptyCheck = 0;
    foreach ($request['brand'] as $index => $value) {
      if($this->required('brand_' . $index , $value ?? '')) {
        $emptyCheck++;
        $this->maxLength('brand_' . $index, $value, 20);
      }
    }
    if(count($request['brand']) === $emptyCheck) {
      $this->isUnique($request['brand'], 'brand');
    }

    $emptyCheck = 0;
    foreach ($request['model'] as $index => $value) {
      if($this->required('model_' . $index , $value ?? '')) {
        $emptyCheck++;
        $this->maxLength('model_' . $index, $value, 20);
      }
    }
    if(count($request['model']) === $emptyCheck) {
      $this->isUnique($request['model'], 'model');
    }

    return empty($this->getErrors());
  }

  private function isUnique(array $data, string $section): bool
  {
    $checked = count($data) === count(array_unique($data));
    if(! $checked) {
      $this->errors[$section] = '登録名が重複しています、再度入力してください';
    }
    return $checked;
  }

}