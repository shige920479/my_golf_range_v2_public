<?php
namespace App\Services\Validation;

class UserLoginValidation extends BaseValidation
{
  use FormRulesTrait;

  public function loginValidate(array $request): bool
  {
    $this->required('email', $request['email'] ?? '');
    $this->required('password', $request['password'] ?? '');

    return empty($this->errors);
  }

}