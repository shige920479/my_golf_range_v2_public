<?php
namespace App\Controller\User\Traits;

trait HandleFormValidateTrait
{
  /** 予約内容のバリデーション(send,update共通)  */
  protected function validateReservationForm(array $request, string $redirectPath): void
  {
    if (! $this->formValidator->validate($request)) {
      $this->setSessionErrorsAndOld($this->formValidator);
      redirect(url($redirectPath));
    }

    if ($this->facilityValidator->existsFacility($request)) {
      if (! $this->facilityValidator->isAvailable($request)) {
        $this->session->set('old', $this->formValidator->getOld());
        $this->session->set('errors', $this->facilityValidator->getErrors());
        redirect(url($redirectPath));
      } 
    }
    
    if (! $this->timeValidator->validate($request)) {
      $this->session->set('old', $this->formValidator->getOld());
      $this->session->set('errors', $this->timeValidator->getErrors());
      redirect(url($redirectPath));
    }

    if (! $this->reservableValidator->validate($request)) {
      $this->session->set('old', $this->formValidator->getOld());
      $this->session->set('errors', $this->reservableValidator->getErrors());
      redirect(url($redirectPath));
    }
  }
}