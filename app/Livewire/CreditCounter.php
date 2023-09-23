<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class CreditCounter extends Component
{
    public $credits;

    public function mount(): void
    {
        $this->updateCredits();
    }

    #[On('shop-item-purchased')]
    public function updateCredits(): void
    {
        $this->credits = auth()->user()->getFormattedCredits();
    }

    public function render()
    {
        return view('livewire.credit-counter');
    }
}
