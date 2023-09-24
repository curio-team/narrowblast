<?php

namespace App\Livewire;

use Livewire\Component;

class SlideSwitcher extends Component
{
    public $tab = 1;

    public function mount()
    {
        $this->tab = isset($_GET['tab']) ? $_GET['tab'] : 1;
    }

    public function render()
    {
        return view('livewire.slide-switcher');
    }

    public function goToTab(int $tab)
    {
        $this->tab = $tab;
    }
}
