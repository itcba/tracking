<?php
// app/Livewire/LiveUpdateWidget.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tracking;
use Illuminate\Support\Collection;

class LiveUpdateWidget extends Component
{
    public Collection $liveRecords;
    public $liveLimit = 2;
    public $liveTotal = 0;
    // search input for filtering live records
    public string $search = '';

    public function mount()
    {
        $this->loadDataForLiveUpdate();
    }

    public function loadDataForLiveUpdate()
    {
        // 1. Ambil data dengan urutan:
        //    - "active" dulu (karena 'completed' = 1, 'active' = 0)
        //    - Lalu urutkan berdasarkan data terbaru
        $query = Tracking::query();

        // jika ada search, filter beberapa kolom yang relevan
        if (!empty(trim($this->search))) {
            $s = "%" . trim($this->search) . "%";
            $query->where(function ($q) use ($s) {
                $q->where('plate_number', 'like', $s)
                  ->orWhere('vehicle_name', 'like', $s)
                  ->orWhere('driver_name', 'like', $s)
                  ->orWhere('description', 'like', $s);
            });
        }

        $this->liveRecords = $query->orderByRaw("CASE WHEN current_stage = 'completed' THEN 1 ELSE 0 END")
                                  ->latest() // 'latest()' adalah 'created_at DESC'
                                  ->take($this->liveLimit)
                                  ->get();
        
        // 2. Hitung total data untuk tombol "Muat Lebih Banyak"
        $this->liveTotal = Tracking::count();
    }

    /**
     * Fungsi baru untuk tombol "Muat Lebih Banyak".
     */
    public function loadMoreLive()
    {
        // Tambah 3 data lagi setiap kali diklik
        $this->liveLimit += 3;
    }

    public function render()
    {
        // Panggil fungsi ini di render agar wire:poll berfungsi
        $this->loadDataForLiveUpdate();
        return view('livewire.live-update-widget');
    }
}