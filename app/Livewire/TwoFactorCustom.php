<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;

class TwoFactorCustom extends Component
{
    public $enabled;
    public $qrCode;
    public $recoveryCodes;

    public function mount()
    {
        $this->enabled = ! is_null(Auth::user()->two_factor_secret);
        $this->load2FAInfo();
    }

    public function enable2FA()
    {
        Auth::user()->enableTwoFactorAuthentication();
        $this->enabled = true;
        $this->load2FAInfo();
        session()->flash('message', '2FA ativado com sucesso!');
    }

    public function disable2FA()
    {
        Auth::user()->disableTwoFactorAuthentication();
        $this->enabled = false;
        $this->qrCode = null;
        $this->recoveryCodes = null;
        session()->flash('message', '2FA desativado.');
    }

    private function load2FAInfo()
    {
        if ($this->enabled) {
            $this->qrCode = Auth::user()->twoFactorQrCodeSvg();
            $this->recoveryCodes = Auth::user()->recoveryCodes();
        }
    }

    public function render()
    {
        return view('livewire.two-factor-custom');
    }
}

