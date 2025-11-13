<?php
// app/Livewire/TrackingApp.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tracking;
use App\Models\User;
use App\Exports\TrackingsExport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination; // <-- 1. IMPORT FUNGSI PAGINASI

class TrackingApp extends Component
{
    use WithPagination; // <-- 2. GUNAKAN FUNGSI PAGINASI

    // Tentukan tema paginasi agar sesuai style
    protected $paginationTheme = 'tailwind';

    // Properti untuk 'Live Update'
    // Logika ini SEKARANG ada di LiveUpdateWidget.php
    // public Collection $liveRecords;
    // public $liveLimit = 2;
    // public $liveTotal = 0;

    // Properti untuk Login
    public Collection $allUsers;
    public $login_user_id = '', $login_pin = '', $loginError = '';

    // Properti untuk Modal
    public $showModal = false, $modalAction = '', $editingRecord;
    public $vehicle_name, $plate_number, $description, $start_time;

    // Properti untuk Admin Tabel
    public $search = ''; // Untuk kotak pencarian
    public $perPage = 10; // Untuk dropdown "entries per page"

    // Daftar stages (tetap sama)
    public $stages = [
        'security' => ['label' => 'Security', 'next' => 'loading'],
        'loading' => ['label' => 'Bongkar Muat', 'next' => 'ttb'],
        'ttb' => ['label' => 'Officer TTB', 'next' => 'completed'],
    ];

    /**
     * 'mount()' berjalan sekali saat komponen dimuat.
     */
    public function mount()
    {
        $this->allUsers = User::orderBy('name')->get();
    }

    /**
     * Hook ini otomatis berjalan saat $search diubah
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Hook ini otomatis berjalan saat $perPage diubah
     */
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // --- FUNGSI LOGIN / LOGOUT ---

    public function login()
    {
        $credentials = [
            'id' => $this->login_user_id,
            'password' => $this->login_pin,
        ];

        if (Auth::attempt($credentials)) {
            session()->regenerate();
            return redirect('/');
        } else {
            $this->loginError = 'PIN salah! Silakan coba lagi.';
            $this->login_pin = '';
        }
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    }

    // --- FUNGSI MODAL ---

    public function openNewEntryModal()
    {
        $this->resetForm();
        $this->modalAction = 'create';
        $this->start_time = now()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function openUpdateModal($recordId)
    {
        $this->resetForm();
        $this->editingRecord = Tracking::find($recordId);
        $this->modalAction = 'update';
        $this->start_time = now()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->vehicle_name = '';
        $this->plate_number = '';
        $this->description = '';
        $this->start_time = '';
        $this->editingRecord = null;
    }

    // --- FUNGSI SIMPAN & UPDATE DATA ---

    public function handleSubmit()
    {
        if ($this->modalAction === 'create') {
            $this->createNewRecord();
        } else {
            $this->updateRecord();
        }
    }

    public function createNewRecord()
    {
        if (Auth::user()->role != 'security') return;

        $this->validate([
            'vehicle_name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => 'required|date',
        ]);

        Tracking::create([
            'vehicle_name' => $this->vehicle_name,
            'plate_number' => $this->plate_number,
            'description' => $this->description,
            'security_start' => $this->start_time,
            'current_stage' => 'active',
        ]);

        $this->closeModal();
    }

    public function updateRecord()
    {
        $record = Tracking::find($this->editingRecord->id);
        if (!$record || $record->current_stage == 'completed') {
            $this->closeModal();
            return;
        }

        $userRole = Auth::user()->role;

        switch ($userRole) {
            case 'security':
                if (!is_null($record->loading_end) && !is_null($record->ttb_end)) {
                    $record->security_end = $this->start_time;
                    $record->current_stage = 'completed';
                }
                break;
            case 'loading':
                if (is_null($record->loading_start)) {
                    $record->loading_start = $this->start_time;
                } else {
                    $record->loading_end = $this->start_time;
                }
                break;
            case 'ttb':
                if (is_null($record->ttb_start)) {
                    $record->ttb_start = $this->start_time;
                } else {
                    $record->ttb_end = $this->start_time;
                }
                break;
        }

        $record->save();
        $this->closeModal();
    }

    // --- FUNGSI EXPORT ---

    public function exportExcel()
    {
        if (Auth::user()->role != 'admin') return;
        return Excel::download(new TrackingsExport($this->search), 'Laporan_Bongkar_Muat_'.now()->format('Ymd').'.xlsx');
    }

    /**
     * 'render()' adalah fungsi yang menampilkan view.
     */
    public function render()
    {
        $userRecords = collect();

        if (Auth::check()) {
            $userRole = Auth::user()->role;
            if ($userRole == 'admin') {
                $query = Tracking::query();
                if (!empty($this->search)) {
                    $query->where(function ($q) {
                        $q->where('vehicle_name', 'like', '%' . $this->search . '%')
                          ->orWhere('plate_number', 'like', '%' . $this->search . '%');
                    });
                }
                $userRecords = $query->latest()->paginate($this->perPage);

            } else {
                $userRecords = Tracking::where('current_stage', '!=', 'completed')
                                        ->latest()
                                        ->get();
            }
        } 
        // Blok 'else' (untuk live update) sudah tidak ada di sini,
        // karena sudah dipindah ke LiveUpdateWidget.php
        
        return view('livewire.tracking-app', [
            'userRecords' => $userRecords,
        ])->layout('layouts.app');
    }
}