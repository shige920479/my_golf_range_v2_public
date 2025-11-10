<?php
namespace App\Controller;

class SessionDestroyController extends BaseController
{
  public function destroy(): void
  {
    $redirectPath =  $this->request['redirect'] ?? '/';
    $this->session->forget('reservation');

    redirect(url($redirectPath));
  }
}