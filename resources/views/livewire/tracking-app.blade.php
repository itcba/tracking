<div> <!-- Div pembungkus utama untuk mengatasi error "Multiple root elements" -->

    {{-- TAMPILAN JIKA SUDAH LOGIN (DASHBOARD) --}}
    @if (Auth::check())
        @php $user = Auth::user(); @endphp
        
        <div class="flex flex-col min-h-screen" style="background: #f3f4f6;">
            
            <main class="flex-grow">
                <!-- Header -->
                <div style="background: #2563eb; color: white; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h1 style="font-size: 24px; font-weight: bold; margin: 0 0 4px 0;">Tracking Bongkar Muat</h1>
                            <p style="font-size: 14px; margin: 0; opacity: 0.9;">PT. CBA Chemical Industry</p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 14px; opacity: 0.9;">👤 {{ $user->name }}</span>
                            <button wire:click="logout" class="btn" style="background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border: none; border-radius: 6px; font-size: 14px; cursor: pointer;">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Isi Konten Dashboard -->
                <div style="max-width: 1200px; margin: 0 auto; padding: 16px;">
                    
                    @if ($user->role === 'security')
                        <button wire:click="openNewEntryModal" class="btn" style="width: 100%; background: #2563eb; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            + Tambah Kendaraan Baru
                        </button>
                    @endif
                    
                    @if ($user->role === 'admin')
                        <!-- TAMPILAN TABEL ADMIN -->
                        <div style="margin-bottom: 16px;">
                            <button wire:click="exportExcel" class="btn" style="width: 100%; background: #10b981; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                📥 Export ke Excel (sesuai pencarian)
                            </button>
                        </div>
                        <div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <select wire:model.live="perPage" style="padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px;">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <span style="font-size: 14px; color: #6b7280;">entries per page</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <label for="search" style="font-size: 14px; color: #6b7280;">Search:</label>
                                    <input 
                                        wire:model.live.debounce.500ms="search" 
                                        id="search" 
                                        type="text" 
                                        placeholder="Cari..."
                                        style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px;">
                                </div>
                            </div>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; min-width: 1200px; border-collapse: collapse; white-space: nowrap;">
                                    <thead style="background: #f3f4f6;">
                                        <tr>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">Kendaraan</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">Plat Nomor</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">Security (Mulai / Selesai)</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">Bongkar Muat (Mulai / Selesai)</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">Officer TTB (Mulai / Selesai)</th>
                                            <th style="padding: 12px 16px; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($userRecords as $record)
                                            <tr style="border-top: 1px solid #e5e7eb;">
                                                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937;">
                                                    <div style="font-weight: 600;">{{ $record->vehicle_name }}</div>
                                                    <div style="font-size: 12px; color: #6b7280; white-space: normal;">{{ $record->description }}</div>
                                                </td>
                                                <td style="padding: 12px 16px; font-size: 14px; color: #1f2937;">{{ $record->plate_number }}</td>
                                                <td style="padding: 12px 16px; font-size: 12px; color: #1f2937; line-height: 1.6;">
                                                    <div>{{ $record->security_start ? $record->security_start->format('d/m/Y H:i') : '-' }}</div>
                                                    <div>{{ $record->security_end ? $record->security_end->format('d/m/Y H:i') : '-' }}</div>
                                                </td>
                                                <td style="padding: 12px 16px; font-size: 12px; color: #1f2937; line-height: 1.6;">
                                                    <div>{{ $record->loading_start ? $record->loading_start->format('d/m/Y H:i') : '-' }}</div>
                                                    <div>{{ $record->loading_end ? $record->loading_end->format('d/m/Y H:i') : '-' }}</div>
                                                </td>
                                                <td style="padding: 12px 16px; font-size: 12px; color: #1f2937; line-height: 1.6;">
                                                    <div>{{ $record->ttb_start ? $record->ttb_start->format('d/m/Y H:i') : '-' }}</div>
                                                    <div>{{ $record->ttb_end ? $record->ttb_end->format('d/m/Y H:i') : '-' }}</div>
                                                </td>
                                                <td style="padding: 12px 16px; font-size: 14px;">
                                                    @if ($record->current_stage == 'completed')
                                                        <span style="background: #10b981; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">✓ Selesai</span>
                                                    @else
                                                        <span style="background: #f59e0b; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">Sedang Berlangsung</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="padding: 32px; text-align: center; color: #9ca3af;">
                                                    @if (empty($search))
                                                        Tidak ada data.
                                                    @else
                                                        Tidak ada data yang cocok dengan pencarian "{{ $search }}".
                                                    @endif
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
                        <!-- TAMPILAN KARTU UNTUK ROLE LAIN -->
                        <div id="recordsList">
                            @forelse ($userRecords as $record)
                                @include('livewire.partials.record-card', ['record' => $record, 'currentUserRole' => $user->role])
                            @empty
                                <div style="background: white; border-radius: 12px; padding: 32px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                    <p style="font-size: 16px; color: #9ca3af; margin: 0;">Tidak ada data untuk tahap ini</p>
                                </div>
                            @endforelse
                        </div>
                    @endif
                    
                </div>
            </main>

            <!-- FOOTER UNTUK DASHBOARD -->
            @include('partials.dashboard-footer')
        </div>

    {{-- TAMPILAN JIKA BELUM LOGIN (HALAMAN LOGIN) --}}
    @else
        
        <!-- Layout Sticky Footer untuk Login -->
        <div class="flex flex-col min-h-screen" style="background: linear-gradient(135deg, #2563eb 0%, #10b981 100%);">
            
            <main class="flex-grow flex items-center justify-center p-4">
                
                <div style="width: 100%; max-width: 480px;">
                    <!-- Header -->
                    <div style="text-align: center; margin-bottom: 32px;">
                        <h1 style="font-size: 32px; font-weight: bold; color: white; margin: 0 0 8px 0;">Tracking Bongkar Muat</h1>
                        <p style="font-size: 18px; color: rgba(255,255,255,0.9); margin: 0;">PT. CBA Chemical Industry</p>
                    </div>
                    
                    <!-- ============================================== -->
                    <!-- == INI PERBAIKANNYA == -->
                    <!-- ============================================== -->
                    <!-- 
                      Blok "Live Update" yang lama sudah dihapus.
                      Sekarang kita memanggil widget baru secara "lazy".
                    -->
                    <div style="margin-bottom: 24px;">
                        <livewire:live-update-widget lazy />
                    </div>
                    
                    <!-- Login Section -->
                    <div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
                        <h2 style="font-size: 24px; font-weight: bold; margin: 0 0 24px 0; color: #1f2937; text-align: center;">Login</h2>
                        <form wire:submit.prevent="login">
                            @if ($loginError)
                                <div style="background: #fef2f2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 16px; text-align: center;">
                                    {{ $loginError }}
                                </div>
                            @endif
                            <div style="margin-bottom: 20px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 8px; color: #1f2937;">Pilih User:</label>
                                <select wire:model="login_user_id" required style="width: 100%; padding: 14px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px; color: #1f2937; background: white;">
                                    <option value="">-- Pilih Role Anda --</option>
                                    @foreach ($allUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="margin-bottom: 24px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 8px; color: #1f2937;">PIN/Password (4 digit):</label>
                                <input wire:model="login_pin" type="password" required maxlength="4" pattern="[0-9]{4}" placeholder="Masukkan PIN 4 digit" 
                                       style="width: 100%; padding: 14px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px; color: #1f2937; background: white;">
                            </div>
                            <button type="submit" class="btn" style="width: 100%; background: #2563eb; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                🔐 Login
                            </button>
                        </form>
                    </div>
                </div>
            </main>

            <!-- FOOTER UNTUK LOGIN -->
            @include('partials.login-footer')
        </div>
    @endif


    {{-- MODAL (Create / Update) --}}
    @if ($showModal)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; padding: 16px; overflow-y: auto;">
            <div style="max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; padding: 24px; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
                <form wire:submit.prevent="handleSubmit">
                    <h2 style="font-size: 20px; font-weight: bold; margin: 0 0 20px 0;">
                        @if ($modalAction === 'create')
                            Tambah Kendaraan Baru
                        @else
                            Update Status: {{ $editingRecord->vehicle_name }}
                        @endif
                    </h2>
                    <div id="formFields">
                        @if ($modalAction === 'create')
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">Nama Kendaraan</label>
                                <input wire:model="vehicle_name" type="text" required style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">Plat Nomor</label>
                                <input wire:model="plate_number" type="text" required style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">Keterangan</label>
                                <textarea wire:model="description" required rows="3" style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px; resize: vertical;"></textarea>
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">Waktu Mulai (Masuk)</label>
                                <input wire:model="start_time" type="datetime-local" required style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                            </div>
                        
                        @else
                            @php
                                $userRole = Auth::user()->role;
                                $labelText = '';
                                if ($userRole == 'security') {
                                    $labelText = 'Waktu Selesai (Kendaraan Keluar)';
                                } elseif ($userRole == 'loading') {
                                    $hasStarted = !is_null($editingRecord->loading_start);
                                    $labelText = $hasStarted ? 'Waktu Selesai Bongkar Muat' : 'Waktu Mulai Bongkar Muat';
                                } elseif ($userRole == 'ttb') {
                                    $hasStarted = !is_null($editingRecord->ttb_start);
                                    $labelText = $hasStarted ? 'Waktu Selesai TTB' : 'Waktu Mulai TTB';
                                }
                            @endphp
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 6px;">
                                    {!! $labelText !!}
                                </label>
                                <input wire:model="start_time" type="datetime-local" required style="width: 100%; padding: 10px; border: 2px solid #f3f4f6; border-radius: 8px; font-size: 16px;">
                            </div>
                        @endif
                    </div>
                    <div style="display: flex; gap: 12px; margin-top: 24px;">
                        <button type="submit" class="btn" style="flex: 1; background: #2563eb; color: white; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                            Simpan
                        </button>
                        <button wire:click="closeModal" type="button" class="btn" style="flex: 1; background: #f3f4f6; color: #1f2937; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div> <!-- == AKHIR DARI DIV PEMBUNGKUS TUNGGAL == -->