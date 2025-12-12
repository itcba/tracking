<div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <div class="pulse" style="width: 12px; height: 12px; background: #10b981; border-radius: 50%;"></div>
        <h2 style="font-size: 20px; font-weight: bold; margin: 0; color: #1f2937;">Update Terkini - Live</h2>
    </div>
    
    <div style="display:flex; gap:8px; align-items:center; margin-bottom:16px;">
        <input
            type="text"
            placeholder="Cari nomor polisi, kendaraan, sopir atau keterangan..."
            wire:model.defer="search"
            wire:keydown.enter.prevent="performSearch"
            class="" 
            style="flex:1; padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px;"
        />
            @if($search !== '')
            <button wire:click="clearSearch" style="padding:8px 12px; border-radius:8px; border:1px solid #e5e7eb; background:#fff;">Clear</button>
        @endif
    </div>
    
    <div wire:poll.30s id="liveRecordsList" style="max-height: 400px; overflow-y: auto;">
        @if($liveRecords->isEmpty())
            <div style="text-align: center; padding: 32px;">
                <p style="font-size: 16px; color: #9ca3af; margin: 0;">
                    @if(trim($search) !== '')
                        Tidak ada data yang cocok untuk "{{ $search }}"
                    @else
                        Belum ada data
                    @endif
                </p>
            </div>
        @else
            @foreach ($liveRecords as $record)
                @include('livewire.partials.live-record-card', ['record' => $record])
            @endforeach
        @endif
    </div>

    @if ($liveTotal > $liveLimit)
        <div style="text-align: center; margin-top: 16px; border-top: 1px solid #f3f4f6; padding-top: 16px;">
            <button wire:click="loadMoreLive" class="btn" style="background: #f3f4f6; color: #1f2937; padding: 8px 16px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                Muat Lebih Banyak...
            </button>
        </div>
    @endif
</div>