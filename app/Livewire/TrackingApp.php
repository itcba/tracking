<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tracking;
use App\Models\User;
use App\Exports\TrackingsExport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TrackingApp extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // --- PROPERTI LOGIN ---
    public Collection $allUsers;
    public $login_user_id = '', $login_pin = '', $loginError = '';

    // --- PROPERTI FORM UTAMA ---
    public $showModal = false;
    public $modalAction = 'create'; // 'create', 'update', 'public_create'
    public $editingRecord;

    // Data Kendaraan (Input Security / Admin / Supir)
    public $vehicle_name, $plate_number, $description, $keterangan, $ttb_notes;
    public $driver_name; // Input Nama Supir
    public $type = ''; 

    // Tambahan field template bongkar/muat
    public $vehicle_kind;        // Jenis Kendaraan
    public $company_name;        // Nama Instansi
    public $destination;         // Tujuan
    public $driver_phone;        // Nomor HP Sopir
    public $driver_phone_local = ''; 
    public $driver_identity;     // Identitas (KTP/SIM)
    public $sj_number;           // No. Surat Jalan (khusus bongkar)
    public $item_name;           // Nama Barang (khusus bongkar)
    public $item_quantity;       // Jumlah Barang (khusus bongkar)

    // Data Transaksi (Input Manual Petugas)
    public $officer_name;     

    // Distribusi ke supir (Officer TTB)
    public $distribution_officer;

    // --- DEFINISI STAGES (Agar tidak error di view) ---
    public $stages = [
        'security' => 'Security',
        'loading'  => 'Bongkar/Muat',
        'ttb'      => 'Officer TTB'
    ];

    // --- PROPERTI TABEL ADMIN ---
    public $search = '';
    public $perPage = 10;

    // di atas, barisan properti lain:
    public $dateFrom = null;   // YYYY-MM-DD
    public $dateTo   = null;   // YYYY-MM-DD

    // opsional: saat user mengganti filter tanggal, reset halaman tabel
    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingDateTo()   { $this->resetPage(); }

    public function mount()
    {
        $this->allUsers = User::orderBy('name')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // --- AUTHENTICATION ---

    public function login()
    {
        $credentials = [
            'id'       => $this->login_user_id,
            'password' => $this->login_pin,
        ];

        if (Auth::attempt($credentials)) {
            session()->regenerate();
            return redirect('/');
        } else {
            $this->loginError = 'PIN salah! Silakan coba lagi.';
            $this->login_pin  = '';
        }
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    }

    // --- MODAL LOGIC ---

    public function openPublicInputModal()
    {
        $this->resetForm();
        $this->modalAction = 'public_create'; // Mode input supir
        $this->showModal   = true;
    }

    public function openNewEntryModal()
    {
        $this->openModal('create');
    }

    public function openUpdateModal($recordId)
    {
        $this->openModal('update', $recordId);
    }

    public function openModal($action, $id = null)
    {
        $this->resetValidation();
        $this->modalAction = $action;
        $this->showModal   = true;
        
        // Reset officer_name agar wajib diisi manual setiap update
        $this->officer_name = ''; 

        if ($action === 'update' && $id) {
            $this->editingRecord = Tracking::find($id);
            
            // Khusus Admin: Load data lama untuk diedit
            if (Auth::user()->role === 'admin' && $this->editingRecord) {
                $this->vehicle_name        = $this->editingRecord->vehicle_name;
                $this->company_name        = $this->editingRecord->company_name;
                $this->plate_number        = $this->editingRecord->plate_number;
                $this->vehicle_kind        = $this->editingRecord->vehicle_kind;
                $this->destination         = $this->editingRecord->destination;
                $this->description         = $this->editingRecord->description;
                $this->keterangan          = $this->editingRecord->keterangan;
                $this->ttb_notes           = $this->editingRecord->ttb_notes;
                $this->type                = $this->editingRecord->type;
                $this->driver_name         = $this->editingRecord->driver_name;
                $this->driver_phone        = $this->editingRecord->driver_phone;
                $this->driver_identity     = $this->editingRecord->driver_identity;
                $this->sj_number           = $this->editingRecord->sj_number;
                $this->item_name           = $this->editingRecord->item_name;
                $this->item_quantity       = $this->editingRecord->item_quantity;
                $this->distribution_officer= $this->editingRecord->distribution_officer;
            }
        } else {
            // Reset form untuk input baru
            $this->resetForm();
            $this->type = ''; 
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'vehicle_name',
            'company_name',
            'plate_number',
            'vehicle_kind',
            'destination',
            'description',
            'keterangan',
            'ttb_notes',
            'type',
            'officer_name',
            'driver_name',
            'driver_phone',
            'driver_identity',
            'editingRecord',
            'sj_number',
            'item_name',
            'item_quantity',
            'distribution_officer',
        ]);
    }

    // --- CORE LOGIC (HANDLE SUBMIT) ---

    public function handleSubmit()
    {
        $now = now();

        // 1. LOGIKA INPUT PUBLIK (SUPIR TANPA LOGIN)
        if ($this->modalAction === 'public_create') {
            $this->validate([
                'vehicle_name' => 'required',
                'company_name' => 'required',
                'plate_number' => 'required',
                'driver_name'  => 'required',
                'type'         => 'required',
            ]);

            Tracking::create([
                'vehicle_name'        => $this->vehicle_name,
                'company_name'        => $this->company_name,
                'plate_number'        => $this->plate_number,
                'vehicle_kind'        => $this->vehicle_kind,
                'destination'         => $this->destination,
                'driver_name'         => $this->driver_name,
                'driver_phone'        => $this->driver_phone,
                'driver_identity'     => $this->driver_identity,
                'description'         => $this->description,
                'keterangan'          => $this->keterangan,
                'type'                => $this->type,
                'sj_number'           => $this->sj_number,
                'item_name'           => $this->item_name,
                'item_quantity'       => $this->item_quantity,
                'security_start'      => $now,
                'security_in_officer' => 'Input Mandiri (Supir)',
                'current_stage'       => 'security_in',
            ]);

            $this->closeModal();
            session()->flash('message', 'Data berhasil disimpan! Silakan lapor ke Security.');
            return;
        }

        // Cek Login untuk aksi selanjutnya
        if (!Auth::check()) return;
        $user = Auth::user();

        // 2. VALIDASI INPUT PETUGAS (Wajib kecuali Admin Edit Master)
        if ($user->role !== 'admin') {
            $this->validate([
                'officer_name' => 'required|string|min:3',
            ], ['officer_name.required' => 'Nama Petugas wajib diisi manual!']);
        }

        // 3. LOGIKA ADMIN (Edit Data Master)
        if ($user->role === 'admin' && $this->editingRecord) {
            $this->validate([
                'vehicle_name' => 'required',
                'company_name' => 'required',
                'plate_number' => 'required',
                'type'         => 'required',
                'custom_time'  => 'nullable|date',
            ]);
            
            $this->editingRecord->update([
                'vehicle_name'        => $this->vehicle_name,
                'company_name'        => $this->company_name,
                'plate_number'        => $this->plate_number,
                'vehicle_kind'        => $this->vehicle_kind,
                'destination'         => $this->destination,
                'driver_name'         => $this->driver_name,
                'driver_phone'        => $this->driver_phone,
                'driver_identity'     => $this->driver_identity,
                'type'                => $this->type,
                'description'         => $this->description,
                'keterangan'          => $this->keterangan,
                'sj_number'           => $this->sj_number,
                'item_name'           => $this->item_name,
                'item_quantity'       => $this->item_quantity,
                'distribution_officer'=> $this->distribution_officer,
            ]);
            
            $this->closeModal();
            return;
        }

        // 4. LOGIKA SECURITY (INPUT BARU MANUAL)
        if ($this->modalAction === 'create' && $user->role === 'security') {
            $this->validate([
                //'vehicle_name' => 'required',
                'company_name' => 'required',
                'plate_number' => 'required',
                'type'         => 'required',
            ]);

            Tracking::create([
                'vehicle_name'        => $this->company_name,
                'company_name'        => $this->company_name,
                'plate_number'        => $this->plate_number,
                'vehicle_kind'        => $this->vehicle_kind,
                'destination'         => $this->destination,
                'driver_name'         => $this->driver_name,
                'driver_phone'        => $this->driver_phone,
                'driver_identity'     => $this->driver_identity,
                'description'         => $this->description,
                'keterangan'          => $this->keterangan,
                'type'                => $this->type,
                'sj_number'           => $this->sj_number,
                'item_name'           => $this->item_name,
                'item_quantity'       => $this->item_quantity,
                'security_start'      => $now,
                'security_in_officer' => $this->officer_name,
                'current_stage'       => 'security_in',
            ]);

        } 
        
        // 5. LOGIKA UPDATE BERURUTAN (ROLE PETUGAS)
        elseif ($this->modalAction === 'update') {
            $this->validate([
                'officer_name' => 'required',
            ]);
            $record = $this->editingRecord;

            // A. LOADING (Bongkar/Muat)
            if ($user->role === 'loading') {
                if ($record->current_stage == 'security_in') {
                    // Mulai Bongkar/Muat
                    $record->update([
                        'loading_start'        => $now,
                        'loading_start_officer'=> $this->officer_name,
                        'current_stage'        => 'loading_started',
                    ]);
                } elseif ($record->current_stage == 'loading_started') {
                    // Selesai Bongkar/Muat
                    $record->update([
                        'loading_end'          => $now,
                        'loading_end_officer'  => $this->officer_name,
                        'current_stage'        => 'loading_ended',
                        'keterangan'           => $this->keterangan,
                    ]);
                } else {
                    session()->flash('error', 'Urutan salah! Tunggu Security Masuk atau proses sudah selesai.');
                    return;
                }
            }

            // B. OFFICER TTB + DISTRIBUSI
            elseif ($user->role === 'ttb') {
                if ($record->current_stage == 'loading_ended') {
                    // 1) Mulai TTB
                    $record->update([
                        'ttb_start'        => $now,
                        'ttb_start_officer'=> $this->officer_name,
                        'current_stage'    => 'ttb_started',
                    ]);
                } elseif ($record->current_stage == 'ttb_started') {
                    // 2) Selesai TTB
                    $record->update([
                        'ttb_end'          => $now,
                        'ttb_end_officer'  => $this->officer_name,
                        'current_stage'    => 'ttb_ended',
                        'ttb_notes'        => $this->ttb_notes,
                    ]);
                } elseif ($record->current_stage == 'ttb_ended') {
                    // 3) Distribusi ke Supir
                    $distData = [
                        'distribution_officer' => $this->officer_name,
                        'distribution_at'    => $now,
                        'current_stage'        => 'ttb_distributed',
                    ];

                    // If the TTB officer provided SJ/item/quantity (for MUAT), save to same columns
                    if (!empty($this->sj_number)) {
                        $distData['sj_number'] = $this->sj_number;
                    }
                    if (!empty($this->item_name)) {
                        $distData['item_name'] = $this->item_name;
                    }
                    if (!empty($this->item_quantity)) {
                        $distData['item_quantity'] = $this->item_quantity;
                    }

                    $record->update($distData);
                } else {
                    session()->flash('error', 'Urutan salah! Tunggu proses Bongkar/Muat selesai.');
                    return;
                }
            }

            // C. SECURITY (VERIFIKASI MASUK & KELUAR)
            elseif ($user->role === 'security') {

                // Verifikasi masuk untuk data input mandiri supir
                if (
                    $record->current_stage == 'security_in' &&
                    $record->security_in_officer === 'Input Mandiri (Supir)'
                ) {
                    $record->update([
                        'security_start'      => $now,
                        'security_in_officer' => $this->officer_name,
                    ]);
                }
                // Proses keluar setelah distribusi selesai
                elseif ($record->current_stage == 'ttb_distributed') {
                        // If there are additional fields (SJ / item / qty) filled by the officer,
                        // persist them as part of the finalization. Only include if provided.
                        $updateData = [
                            'security_end'        => $now,
                            'security_out_officer'=> $this->officer_name,
                            'current_stage'       => 'completed',
                        ];

                        if (!empty($this->sj_number)) {
                            $updateData['sj_number'] = $this->sj_number;
                        }
                        if (!empty($this->item_name)) {
                            $updateData['item_name'] = $this->item_name;
                        }
                        if (!empty($this->item_quantity)) {
                            $updateData['item_quantity'] = $this->item_quantity;
                        }

                        $record->update($updateData);
                } else {
                    session()->flash('error', 'Proses belum selesai sepenuhnya (Tunggu distribusi ke supir selesai).');
                    return;
                }
            }
        }

        $this->closeModal();
    }

    // --- ADMIN ACTIONS (CANCEL & DELETE) ---

    public function cancelTracking($id)
    {
        if (Auth::user()->role === 'admin') {
            Tracking::where('id', $id)->update(['current_stage' => 'canceled']);
            $this->closeModal();
        }
    }

    public function deleteTracking($id)
    {
        if (Auth::user()->role === 'admin') {
            $record = Tracking::find($id);
            if ($record) {
                $record->delete();
                session()->flash('message', 'Data berhasil dihapus permanen.');
            }
        }
    }

    public function exportExcel()
    {
        $export = new \App\Exports\TrackingsExport($this->search);

        // sesuaikan dengan versi lama (pakai properti start_date & end_date)
        $export->start_date = $this->start_date ?? null;
        $export->end_date   = $this->end_date ?? null;

        return $export->download('Laporan_Tracking_'.now()->format('Ymd_His').'.xlsx');
    }


    public $start_date = null, $end_date = null;
    
    public function resetDates()
    {
        $this->start_date = null;
        $this->end_date   = null;
        $this->resetPage(); // optional: supaya paging balik ke halaman 1
    }
    // --- RENDER ---

    public function render()
    {
        $userRecords = collect();

        if (Auth::check()) {
            $userRole = Auth::user()->role;
            
            if ($userRole == 'admin') {
                // Admin lihat semua + Pagination + Search
                $query = Tracking::query();

                if ($this->start_date) {
                    $query->whereDate('security_start', '>=', $this->start_date);
                }

                if ($this->end_date) {
                    $query->whereDate('security_start', '<=', $this->end_date);
                }

                if (!empty($this->search)) {
                    $query->where(function ($q) {
                        $q->where('vehicle_name', 'like', '%' . $this->search . '%')
                          ->orWhere('plate_number', 'like', '%' . $this->search . '%')
                          ->orWhere('driver_name', 'like', '%' . $this->search . '%')
                          ->orWhere('type', 'like', '%' . $this->search . '%');
                    });
                }
                $userRecords = $query->latest()->paginate($this->perPage);

            } else {
                // User lain lihat list card aktif (with optional search)
                $query = Tracking::where('current_stage', '!=', 'completed')
                                 ->where('current_stage', '!=', 'canceled');

                if (!empty($this->search)) {
                    $query->where(function ($q) {
                        $q->where('vehicle_name', 'like', '%' . $this->search . '%')
                          ->orWhere('plate_number', 'like', '%' . $this->search . '%')
                          ->orWhere('driver_name', 'like', '%' . $this->search . '%')
                          ->orWhere('company_name', 'like', '%' . $this->search . '%');
                    });
                }

                $userRecords = $query->latest()->get();
            }
        } 
        
        return view('livewire.tracking-app', [
            'userRecords' => $userRecords,
            'stages'      => $this->stages,
            'user'        => Auth::user(),
        ])->layout('layouts.app');
    }
}
