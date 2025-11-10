<?php
namespace App\Services\Validation;

abstract class BaseValidation
{
  use FormRulesTrait;

  public array $errors = [];
  public array $old = [];
}