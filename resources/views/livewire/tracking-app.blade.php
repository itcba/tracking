<div class="min-h-full">

    {{-- TAMPILAN JIKA SUDAH LOGIN (DASHBOARD) --}}
    @if (Auth::check())
        @php $user = Auth::user(); @endphp
        
        <div class="flex flex-col min-h-screen" style="background: #f3f4f6;">
            
            <main class="flex-grow">
                
                {{-- Header Dashboard --}}
                <div style="background: #2563eb; color: white; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h1 style="font-size: 24px; font-weight: bold; margin: 0 0 4px 0;">Tracking Bongkar Muat</h1>
                            <p style="font-size: 14px; margin: 0; opacity: 0.9;">PT CBA Chemical Industry</p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 14px; opacity: 0.9;">👤 {{ $user->name }}</span>
                            <button wire:click="logout" class="btn" style="background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border: none; border-radius: 6px; font-size: 14px; cursor: pointer;" wire:loading.attr="disabled">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>

                <div style="max-width: 1200px; margin: 0 auto; padding: 16px;">
                    
                    {{-- Flash Messages --}}
                    @if (session()->has('message'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            <strong class="font-bold">Sukses!</strong> {{ session('message') }}
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            <strong class="font-bold">Error!</strong> {{ session('error') }}
                        </div>
                    @endif

                    {{-- Tombol Tambah (Security Only) --}}
                    @if ($user->role === 'security')
                        <button wire:click="openNewEntryModal" class="btn" style="width: 100%; background: #2563eb; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            + Tambah Kendaraan Baru
                        </button>
                    @endif
                    
                    <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                    <div>
                        <label style="font-size: 12px; color: #6b7280;">Tanggal Mulai</label>
                        <input type="date" wire:model.live="start_date"
                            style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>

                    <div>
                        <label style="font-size: 12px; color: #6b7280;">Tanggal Akhir</label>
                        <input type="date" wire:model.live="end_date"
                            style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>

                    @if ($start_date || $end_date)
                        <div style="display: flex; align-items: flex-end;">
                            <button wire:click="$set('start_date', null); $set('end_date', null);"
                                style="padding: 8px 12px; background: #ef4444; color: white; border-radius: 6px;">
                                Reset
                            </button>
                        </div>
                    @endif
                </div>
                    

                    {{-- TAMPILAN ADMIN (TABEL) --}}
                    @if ($user->role === 'admin')
                        <div style="margin-bottom: 16px;">
                            <button wire:click="exportExcel" class="btn"
                                style="width: 100%; background: #10b981; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                                wire:loading.attr="disabled" wire:target="exportExcel">
                                <span wire:loading.remove wire:target="exportExcel">📥 Export ke Excel</span>
                                <span wire:loading wire:target="exportExcel">⏳ Memproses...</span>
                            </button>
                        </div>

                        <div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 20px;">
                            {{-- Search & Per Page --}}
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <select wire:model.live="perPage"
                                        style="padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px;">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                        <option value="500">500</option>
                                        <option value="750">750</option>
                                        <option value="1000">1000</option>
                                    </select>
                                    <span style="font-size: 14px; color: #6b7280;">entries per page</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <label for="search" style="font-size: 14px; color: #6b7280;">Search:</label>
                                    <input wire:model.live.debounce.500ms="search" id="search" type="text"
                                        placeholder="Cari kendaraan / plat / supir..."
                                        style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px;">
                                </div>
                            </div>

                            <div style="overflow-x: auto;" wire:loading.style="opacity: 0.5;" wire:target="search, perPage, page">
                                <table style="width: 100%; min-width: 1200px; border-collapse: collapse; white-space: nowrap;">
                                    <thead style="background: #f3f4f6;">
                                        <tr>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">
                                                Kendaraan</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">
                                                Supir</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">
                                                Jenis</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">
                                                Security</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">
                                                Bongkar / Muat</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">
                                                Officer TTB & Distribusi</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">
                                                Status</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">
                                                Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($userRecords as $record)
                                            @php
                                                // Label status lebih rinci
                                                $statusLabel = 'Proses';
                                                $statusClass = 'background: #f59e0b; color: white;';
                                                if ($record->current_stage === 'completed') {
                                                    $statusLabel = '✓ Selesai';
                                                    $statusClass = 'background: #10b981; color: white;';
                                                } elseif ($record->current_stage === 'canceled') {
                                                    $statusLabel = '✕ Dibatalkan';
                                                    $statusClass = 'background: #ef4444; color: white;';
                                                } elseif ($record->current_stage === 'ttb_distributed') {
                                                    $statusLabel = 'Menunggu Keluar (Security)';
                                                } elseif (in_array($record->current_stage, ['ttb_started', 'ttb_ended'])) {
                                                    $statusLabel = 'Proses TTB';
                                                } elseif (in_array($record->current_stage, ['loading_started', 'loading_ended'])) {
                                                    $statusLabel = 'Proses Bongkar/Muat';
                                                } elseif ($record->current_stage === 'security_in') {
                                                    $statusLabel = 'Menunggu Bongkar/Muat';
                                                }
                                            @endphp
                                            <tr style="border-top: 1px solid #e5e7eb;">
                                                {{-- Kendaraan --}}
                                                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937;">
                                                    <div style="font-weight: 600;">{{ $record->vehicle_name }}</div>
                                                    @if($record->company_name)
                                                        <div style="font-size: 12px; color: #4b5563;">
                                                            {{ $record->company_name }}
                                                        </div>
                                                    @endif
                                                    <div style="font-size: 12px; color: #6b7280;">
                                                        {{ $record->plate_number }}
                                                        @if($record->destination)
                                                            • {{ $record->destination }}
                                                        @endif
                                                    </div>
                                                </td>

                                                {{-- Supir --}}
                                                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937;">
                                                    <div style="font-weight: 600;">{{ $record->driver_name ?? '-' }}</div>
                                                    <div style="font-size: 12px; color: #6b7280;">
                                                        {{ $record->driver_phone ?? '' }}
                                                    </div>
                                                    <div style="font-size: 12px; color: #9ca3af;">
                                                        {{ $record->driver_identity ?? '' }}
                                                    </div>
                                                </td>

                                                {{-- Jenis --}}
                                                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937;">
                                                    <span
                                                        class="px-2 py-1 text-xs font-bold rounded {{ $record->type == 'bongkar' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                                        {{ strtoupper($record->type) }}
                                                    </span>
                                                </td>

                                                {{-- Security --}}
                                                <td style="padding: 12px 16px; font-size: 12px; color: #1f2937; line-height: 1.5;">
                                                    <div>
                                                        <span style="font-weight: 600;">IN:</span>
                                                        {{ $record->security_start ? $record->security_start->format('d/m/Y H:i') : '-' }}
                                                    </div>
                                                    <div style="color:#6b7280;">
                                                        👮 {{ $record->security_in_officer ?? '-' }}
                                                    </div>
                                                    <div style="margin-top:4px;">
                                                        <span style="font-weight: 600;">OUT:</span>
                                                        {{ $record->security_end ? $record->security_end->format('d/m/Y H:i') : '-' }}
                                                    </div>
                                                    <div style="color:#6b7280;">
                                                        👮 {{ $record->security_out_officer ?? '-' }}
                                                    </div>
                                                </td>

                                                {{-- Bongkar / Muat --}}
                                                <td style="padding: 12px 16px; font-size: 12px; color: #1f2937; line-height: 1.5;">
                                                    <div>
                                                        <span style="font-weight: 600;">Mulai:</span>
                                                        {{ $record->loading_start ? $record->loading_start->format('d/m/Y H:i') : '-' }}
                                                    </div>
                                                    <div style="color:#6b7280;">
                                                        👷 {{ $record->loading_start_officer ?? '-' }}
                                                    </div>
                                                    <div style="margin-top:4px;">
                                                        <span style="font-weight: 600;">Selesai:</span>
                                                        {{ $record->loading_end ? $record->loading_end->format('d/m/Y H:i') : '-' }}
                                                    </div>
                                                    <div style="color:#6b7280;">
                                                        🏁 {{ $record->loading_end_officer ?? '-' }}
                                                    </div>
                                                </td>

                                                {{-- Officer TTB & Distribusi --}}
                                                <td style="padding: 12px 16px; font-size: 12px; color: #1f2937; line-height: 1.5;">
                                                    <div>
                                                        <span style="font-weight: 600;">Mulai TTB:</span>
                                                        {{ $record->ttb_start ? $record->ttb_start->format('d/m/Y H:i') : '-' }}
                                                    </div>
                                                    <div style="color:#6b7280;">
                                                        📝 {{ $record->ttb_start_officer ?? '-' }}
                                                    </div>
                                                    <div style="margin-top:4px;">
                                                        <span style="font-weight: 600;">Selesai TTB:</span>
                                                        {{ $record->ttb_end ? $record->ttb_end->format('d/m/Y H:i') : '-' }}
                                                    </div>
                                                    <div style="color:#6b7280;">
                                                        🏁 {{ $record->ttb_end_officer ?? '-' }}
                                                    </div>

                                                    <div style="margin-top:6px; border-top:1px dashed #e5e7eb; padding-top:6px;">
                                                        <div>
                                                            <span style="font-weight: 600;">Distribusi:</span>
                                                            {{ $record->distribution_at ? $record->distribution_at->format('d/m/Y H:i') : '-' }}
                                                        </div>
                                                        <div style="color:#6b7280;">
                                                            🚚 {{ $record->distribution_officer ?? '-' }}
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Status --}}
                                                <td style="padding: 12px 16px; font-size: 14px;">
                                                    <span
                                                        style="padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; {{ $statusClass }}">
                                                        {{ $statusLabel }}
                                                    </span>
                                                </td>

                                                {{-- Aksi --}}
                                                <td style="padding: 12px 16px; font-size: 14px;">
                                                    <div style="display: flex; gap: 8px;">
                                                        <button wire:click="openUpdateModal({{ $record->id }})"
                                                            class="text-blue-600 hover:text-blue-800 font-bold">Edit</button>
                                                        <button type="button" wire:click="deleteTracking({{ $record->id }})"
                                                            onclick="confirm('⚠️ Hapus data permanen?') || event.stopImmediatePropagation()"
                                                            class="text-red-600 hover:text-red-800 font-bold">Hapus</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8"
                                                    style="padding: 32px; text-align: center; color: #9ca3af;">
                                                    Tidak ada data.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                                {{ $userRecords->links() }}
                            </div>
                        </div>
                    @else
                        {{-- (bagian card worker tetap seperti sebelumnya) --}}
                        <div id="recordsList">
                            @forelse ($userRecords as $record)
                                @include('livewire.partials.record-card', [
                                    'record' => $record,
                                    'currentUserRole' => $user->role,
                                ])
                            @empty
                                <div
                                    style="background: white; border-radius: 12px; padding: 32px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                    <p style="font-size: 16px; color: #9ca3af; margin: 0;">Tidak ada data untuk tahap ini</p>
                                </div>
                            @endforelse
                        </div>
                    @endif

                    
                </div>
            </main>

           
             @include('partials.dashboard-footer')
           
        </div>

    {{-- TAMPILAN BELUM LOGIN (MENU UTAMA SUPIR / PETUGAS) --}}
    @else
        <div class="flex flex-col min-h-screen" style="background: linear-gradient(135deg, #2563eb 0%, #10b981 100%);">
            <main class="flex-grow">
                <div style="max-width: 420px; margin: 0 auto; padding: 32px 16px 40px;">
                    
                    <div style="text-align: center; margin-bottom: 32px;">
                        <h1 style="font-size: 32px; font-weight: bold; color: white; margin: 0 0 8px 0;">Tracking Bongkar Muat</h1>
                        <p style="font-size: 18px; color: rgba(255,255,255,0.9); margin: 0;">PT CBA Chemical Industry</p>
                    </div>
                    
                    {{-- TOMBOL INPUT MANDIRI (SUPIR) --}}
                    <div style="margin-bottom: 32px;">
                        <a href="{{ route('public.input') }}"
                            style="width: 100%; background: white; color: #2563eb; font-weight: bold; padding: 20px;
                                    border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                                    display: flex; align-items: center; justify-content: center; gap: 12px;
                                    border: none; cursor: pointer;">
                                <span style="font-size: 28px;">🚚</span>
                                <span style="font-size: 18px;">Input Kendaraan (Supir)</span>
                          </a>

                        <p style="text-align: center; color: rgba(255,255,255,0.8); font-size: 13px; margin-top: 8px;">
                            Untuk Supir/Vendor: Klik tombol di atas untuk lapor masuk.
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                        <div style="height: 1px; background: rgba(255,255,255,0.3); flex: 1;"></div>
                        <span style="color: rgba(255,255,255,0.7); font-size: 12px; font-weight: bold;">ATAU LOGIN PETUGAS</span>
                        <div style="height: 1px; background: rgba(255,255,255,0.3); flex: 1;"></div>
                    </div>
                    
                    {{-- FORM LOGIN --}}
                    <div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); margin-bottom: 24px;">
                        <h2 style="font-size: 24px; font-weight: bold; margin: 0 0 24px 0; color: #1f2937; text-align: center;">Login Petugas</h2>
                        <form wire:submit.prevent="login">
                            @if ($loginError)
                                <div style="background: #fef2f2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 16px; text-align: center;">{{ $loginError }}</div>
                            @endif
                            <div style="margin-bottom: 20px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 8px; color: #1f2937;">Pilih User:</label>
                                <select wire:model="login_user_id" required style="width: 100%; padding: 14px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px; color: #1f2937; background: white;">
                                    <option value="">-- Pilih Nama Anda --</option>
                                    @foreach ($allUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 8px; color: #1f2937;">PIN/Password (4 digit):</label>
                                <input wire:model="login_pin" type="password" required maxlength="4" pattern="[0-9]{4}" placeholder="Masukkan PIN 4 digit" style="width: 100%; padding: 14px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px; color: #1f2937; background: white;">
                            </div>
                            <button type="submit" class="btn" style="width: 100%; background: #2563eb; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" wire:loading.attr="disabled" wire:target="login">
                                <span wire:loading.remove wire:target="login">🔐 Login</span>
                                <span wire:loading wire:target="login">⏳ Memproses...</span>
                            </button>
                        </form>
                    </div>
                    
                    <div style="margin-bottom: 50px;">
                        <livewire:live-update-widget lazy />
                    </div>

                </div>
            </main>
            <div style="max-width: 420px; margin: 0 auto; padding: 0 16px 16px;">
                @include('partials.login-footer')
            </div>
        </div>
    @endif


    {{-- MODAL (Shared for All Forms) --}}
    @if ($showModal)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; padding: 16px; overflow-y: auto;">
            <div style="max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; padding: 24px; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
                <form wire:submit.prevent="handleSubmit">
                    <h2 style="font-size: 20px; font-weight: bold; margin: 0 0 20px 0;">
                        @if ($modalAction === 'public_create')
                            🚚 Input Data (Supir)
                        @elseif ($modalAction === 'create')
                            Input Kendaraan Masuk (Security)
                        @elseif (Auth::check() && Auth::user()->role == 'admin')
                            Edit Data (Admin)
                        @else
                            Update Proses
                        @endif
                    </h2>

                    <div id="formFields" class="space-y-4">

                        {{-- STEP 0: PILIH JENIS KEGIATAN --}}
                        @if (in_array($modalAction, ['create', 'public_create']) || (Auth::check() && Auth::user()->role === 'admin'))
                            <div style="background: #f9fafb; padding: 12px 16px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 10px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                    Jenis Kegiatan <span style="color: red">*</span>
                                </label>
                                <select wire:model="type"
                                        style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; background: white;">
                                    <option value="">-- Pilih Jenis Kegiatan --</option>
                                    <option value="bongkar">BONGKAR</option>
                                    <option value="muat">MUAT</option>
                                </select>
                                <p style="font-size: 12px; color: #6b7280; margin-top: 6px;">
                                    Pilih dulu jenis kegiatan. Form di bawah akan menyesuaikan (BONGKAR / MUAT).
                                </p>
                                @error('type')
                                    <span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Kalau belum pilih type, jangan tampilkan form detail --}}
                        @if (empty($type))
                            <div style="padding: 12px 16px; background: #fef9c3; border-radius: 8px; border: 1px solid #facc15; font-size: 13px; color: #92400e;">
                                ⚠ Silakan pilih <strong>Jenis Kegiatan</strong> terlebih dahulu.
                            </div>
                        @else
                            {{-- 1. DATA KENDARAAN (Create / Public / Admin) --}}
                            @if (in_array($modalAction, ['create', 'public_create']) || (Auth::check() && Auth::user()->role === 'admin'))
                                <div style="background: #f9fafb; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 16px;">
                                    <h3 style="font-weight: bold; color: #374151; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;">
                                        Data Kendaraan & Supir ({{ strtoupper($type) }})
                                    </h3>
                                    <div style="display: flex; flex-direction: column; gap: 12px;">

                                        {{-- Nama Instansi / Vendor --}}
                                        <div>
                                            <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                                Nama Instansi / Vendor <span style="color: red">*</span>
                                            </label>
                                            <input wire:model="company_name" type="text" required
                                                   placeholder="PT. INDAH KIAT / MCL / dll"
                                                   style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                                        </div>

                                        {{-- Nomor Polisi --}}
                                        <div>
                                            <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                                Nomor Polisi <span style="color: red">*</span>
                                            </label>
                                            <input wire:model="plate_number" type="text" required
                                                   placeholder="B 9070 NYK"
                                                   style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                                        </div>

                                        {{-- Jenis Kendaraan --}}
                                        <div>
                                            <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                                Jenis Kendaraan
                                            </label>
                                            <input wire:model="vehicle_kind" type="text"
                                                   placeholder="KONTAINER 20 FT / TRONTON / DLL"
                                                   style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                                        </div>

                                        {{-- Tujuan --}}
                                        <div>
                                            <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                                Tujuan
                                            </label>
                                            <input wire:model="destination" type="text"
                                                   placeholder="SAMARINDA / CBA / dll."
                                                   style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                                        </div>

                                        {{-- Nama Sopir --}}
                                        <div>
                                            <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                                Nama Sopir <span style="color: red">*</span>
                                            </label>
                                            <input wire:model="driver_name" type="text" required
                                                   placeholder="Nama lengkap sopir"
                                                   style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                                        </div>

                                        {{-- Nomor HP Sopir --}}
                                        <div>
                                            <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                                Nomor HP Sopir
                                            </label>
                                            <input wire:model="driver_phone" type="text"
                                                   placeholder="0812xxxxxxx"
                                                   style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                                        </div>

                                        {{-- Identitas Sopir --}}
                                        <div>
                                            <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                                Identitas
                                            </label>
                                            <input wire:model="driver_identity" type="text"
                                                   placeholder="KTP / SIM / dll"
                                                   style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                                        </div>

                                        {{-- FIELD TAMBAHAN KHUSUS BONGKAR --}}
                                        @if ($type === 'bongkar')
                                            <div style="margin-top: 8px; padding: 10px; border-radius: 8px; background: #eff6ff; border: 1px solid #bfdbfe;">
                                                <p style="font-size: 12px; font-weight: 700; color: #1d4ed8; margin: 0 0 8px 0;">
                                                    Field Tambahan PROSES BONGKAR
                                                </p>
                                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                                    <div>
                                                        <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 4px;">
                                                            No. Surat Jalan
                                                        </label>
                                                        <input wire:model="sj_number" type="text"
                                                               placeholder="97892736-9900"
                                                               style="width: 100%; padding: 8px 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                                                    </div>
                                                    <div>
                                                        <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 4px;">
                                                            Nama Barang
                                                        </label>
                                                        <input wire:model="item_name" type="text"
                                                               placeholder="KARTON BOX"
                                                               style="width: 100%; padding: 8px 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                                                    </div>
                                                    <div>
                                                        <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 4px;">
                                                            Jumlah Barang
                                                        </label>
                                                        <input wire:model="item_quantity" type="text"
                                                               placeholder="10.000 PCS"
                                                               style="width: 100%; padding: 8px 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Keterangan --}}
                                        <div>
                                            <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                                Keterangan
                                            </label>
                                            <textarea wire:model="description" rows="2"
                                                      placeholder="{{ strtoupper($type) }}"
                                                      style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px; resize: vertical;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif {{-- end if type selected --}}

                        {{-- 2. INPUT PETUGAS (Hanya jika Login & Bukan Admin Edit) --}}
                        @if (Auth::check() && ($modalAction === 'create' || ($modalAction === 'update' && Auth::user()->role !== 'admin')))
                            
                            @if($modalAction === 'update' && isset($editingRecord))
                                <div style="background: #e0f2fe; padding: 12px; border-radius: 8px; color: #0369a1; font-size: 14px; border-left: 4px solid #3b82f6; margin-bottom: 12px;">
                                    <strong>Status Saat Ini:</strong> 
                                    @if($editingRecord->current_stage == 'security_in') Menunggu Mulai Bongkar/Muat
                                    @elseif($editingRecord->current_stage == 'loading_started') Sedang Proses Bongkar/Muat
                                    @elseif($editingRecord->current_stage == 'loading_ended') Menunggu Mulai TTB
                                    @elseif($editingRecord->current_stage == 'ttb_started') Sedang Proses TTB
                                    @elseif($editingRecord->current_stage == 'ttb_ended') Menunggu Keluar (Security)
                                    @endif
                                </div>
                            @endif

                            <div style="background: #eff6ff; padding: 16px; border-radius: 8px; border: 1px solid #dbeafe;">
                                <h3 style="font-weight: bold; color: #374151; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #d1d5db;">Input Petugas & Waktu</h3>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <div>
                                        <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px; color: #4b5563;">Waktu (Realtime)</label>
                                        {{-- Jam Realtime AlpineJS --}}
                                        <div x-data="{ now: new Date(), init() { setInterval(() => { this.now = new Date() }, 1000) } }"
                                             style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; background: #e5e7eb; color: #6b7280; cursor: not-allowed;">
                                            <span x-text="now.toLocaleDateString('id-ID') + ' ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px; color: #1f2937;">Nama Petugas <span style="color: #ef4444;">*</span></label>
                                        <input 
                                            wire:model="officer_name" 
                                            type="text" 
                                            placeholder="Ketik nama petugas..." 
                                            required
                                            style="width: 100%; padding: 10px; border: 2px solid #dbeafe; border-radius: 8px; font-size: 16px; background: white;"
                                        >
                                        @error('officer_name') <span style="color: #ef4444; font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <p style="font-size: 12px; color: #6b7280; margin-top: 8px; font-style: italic;">* Masukkan nama petugas lapangan yang melakukan tugas ini.</p>
                            </div>
                        @endif

                        {{-- 3. TOMBOL CANCEL (Admin Only) --}}
                        @if ($modalAction === 'update' && Auth::check() && Auth::user()->role === 'admin' && isset($editingRecord))
                            <div style="padding-top: 8px;">
                                <button type="button" wire:click="cancelTracking({{ $editingRecord->id }})" 
                                        onclick="confirm('Yakin ingin membatalkan transaksi ini? Status akan menjadi Canceled.') || event.stopImmediatePropagation()"
                                        style="width: 100%; background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; padding: 10px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                                    🚫 Batalkan Transaksi (Set to Canceled)
                                </button>
                            </div>
                        @endif

                    </div>
                    
                    <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                        <button type="submit" class="btn" style="flex: 1; background: #2563eb; color: white; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;" wire:loading.attr="disabled" wire:target="handleSubmit">
                            <span wire:loading.remove wire:target="handleSubmit">
                                @if($modalAction === 'public_create') 📤 Kirim Data
                                @elseif($modalAction === 'create') Simpan Masuk
                                @else Update Status
                                @endif
                            </span>
                            <span wire:loading wire:target="handleSubmit">⏳ Menyimpan...</span>
                        </button>
                        <button wire:click="closeModal" type="button" class="btn" style="flex: 1; background: #f3f4f6; color: #1f2937; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
