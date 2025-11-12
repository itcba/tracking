@php
    $isCompleted = $record->current_stage == 'completed';
    $userRole = $currentUserRole;

    // Logika Badge Status
    $badgeLabel = $isCompleted ? '✓ Selesai' : 'Sedang Berlangsung';
    $badgeColor = $isCompleted ? '#10b981' : '#f59e0b';

    // Logika Tombol Update
    $canUpdate = false;
    $buttonText = 'Update Status';

    if (!$isCompleted) { // Tidak bisa update jika sudah 'completed'
        
        // =======================================================
        // ==> INI ADALAH LOGIKA BARU UNTUK SECURITY <==
        // =======================================================
        if ($userRole == 'security') {
            // Security HANYA bisa update jika:
            // 1. Pekerja Bongkar Muat SUDAH selesai (loading_end tidak kosong)
            // 2. Officer TTB SUDAH selesai (ttb_end tidak kosong)
            
            $canUpdate = !is_null($record->loading_end) && !is_null($record->ttb_end);
            $buttonText = 'Update (Kendaraan Keluar)';
        
        } elseif ($userRole == 'loading') {
            // Loading bisa update sampai mereka selesai
            $canUpdate = is_null($record->loading_end);
            $buttonText = is_null($record->loading_start) ? 'Mulai Bongkar Muat' : 'Selesai Bongkar Muat';
        
        } elseif ($userRole == 'ttb') {
            // TTB bisa update sampai mereka selesai
            $canUpdate = is_null($record->ttb_end);
            $buttonText = is_null($record->ttb_start) ? 'Mulai TTB' : 'Selesai TTB';
        }
    }
@endphp

<div class="card" style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 style="font-size: 18px; font-weight: bold; margin: 0 0 4px 0;">{{ $record->vehicle_name }}</h3>
            <p style="font-size: 14px; color: #6b7280; margin: 0;">{{ $record->plate_number }}</p>
        </div>
        <div class="stage-badge" style="background: {{ $badgeColor }}; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap;">
            {{ $badgeLabel }}
        </div>
    </div>
    <div style="background: #f3f4f6; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
        <p style="font-size: 14px; color: #1f2937; margin: 0; line-height: 1.5;">{{ $record->description }}</p>
    </div>

    <div style="position: relative; padding-left: 24px;">
        @foreach (['security', 'loading', 'ttb'] as $index => $stageKey)
            @php
                $stage = $stages[$stageKey];
                $start = $record->{$stageKey.'_start'};
                $end = $record->{$stageKey.'_end'};
                $isStageCompleted = $start && $end;
                $isActive = !$isStageCompleted && $start;
                $dotColor = $isStageCompleted ? '#10b981' : ($isActive ? '#2563eb' : '#d1d5db');
                $showLine = $index < 2;
            @endphp
            <div style="position: relative; margin-bottom: {{ $showLine ? '24px' : '0' }};">
                <div class="timeline-dot" style="background: {{ $dotColor }};"></div>
                @if ($showLine)
                    <div class="timeline-line" style="background: {{ $isStageCompleted ? '#10b981' : '#e5e7eb' }};"></div>
                @endif
                <div>
                    <p style="font-size: 14px; font-weight: 600; margin: 0 0 4px 0;">{{ $stage['label'] }}</p>
                    @if ($start)
                        <p style="font-size: 12px; color: #6b7280; margin: 0;">Mulai: {{ $start->format('d/m/Y H:i') }}</p>
                    @endif
                    @if ($end)
                        <p style="font-size: 12px; color: #6b7280; margin: 0;">Selesai: {{ $end->format('d/m/Y H:i') }}</p>
                    @endif
                    @if (!$start && !$end)
                        <p style="font-size: 12px; color: #9ca3af; margin: 0;">Belum dimulai</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if ($canUpdate)
        <button wire:click="openUpdateModal({{ $record->id }})" class="btn" style="width: 100%; background: #2563eb; color: white; padding: 12px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 16px;">
            {{ $buttonText }}
        </button>
    @endif
</div>