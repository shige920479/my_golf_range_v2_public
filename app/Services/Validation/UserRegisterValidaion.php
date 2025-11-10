<?php
namespace App\Services\Validation;

class UserRegisterValidaion extends BaseValidation
{
  public function tempInputValidate(array $request): bool
  {
    if ($this->required('email', $request['email'] ?? '')) {
      if ($this->email('email', $request['email'])) {
       $this->confirmed('email', $request['email'], $request['email_confirmation'] ?? '');
      }
    }
    $this->required('email_confirmation', $request['email_confirmation'] ?? '');
    
    return empty($this->errors);
  }

  public function registerValidate(array $request): bool
  {
    if ($this->required('firstname', $request['firstname'] ?? '')) {
      $this->maxLength('firstname', $request['firstname'], 50);
    }

    if ($this->required('lastname', $request['lastname'] ?? '')) {
      $this->maxLength('lastname', $request['lastname'], 50);
    }

    if ($this->required('firstnamekana', $request['firstnamekana'] ?? '')) {
      $this->maxLength('firstnamekana', $request['firstnamekana'], 50);
    }

    if ($this->required('lastnamekana', $request['lastnamekana'] ?? '')) {
      $this->maxLength('lastnamekana', $request['lastnamekana'], 50);
    }

    if ($this->required('phone', $request['phone'] ?? '')) {
      $this->phoneNumber('phone', $request['phone']);
    }

    if ($request['gender'] !== '') {
      $this->gender('gender', $request['gender']);
    }

    if ($this->required('password', $request['password'] ?? '')) {
      if ($this->password('password', $request['password'])) {
        $this->confirmed('password', $request['password'], $request['password_confirmation'] ?? '');
      }
    }
  
    $this->required('password_confirmation', $request['password_confirmation'] ?? '');

    return empty($this->errors);
  }


}