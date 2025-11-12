<?php
// app/Exports/TrackingsExport.php

namespace App\Exports;

use App\Models\Tracking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable; // Pastikan ini ada

class TrackingsExport implements FromCollection, WithHeadings
{
    use Exportable; // Pastikan ini ada

    // 1. Tambahkan properti untuk menampung search
    protected $search;

    // 2. Tambahkan constructor untuk MENERIMA $search dari controller
    public function __construct(string $search = null)
    {
        $this->search = $search;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // 3. Mulai query, jangan langsung ::all()
        $query = Tracking::query();

        // 4. LOGIKA FILTER: Jika $this->search TIDAK kosong, filter datanya
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('vehicle_name', 'like', '%' . $this->search . '%')
                  ->orWhere('plate_number', 'like', '%' . $this->search . '%');
            });
        }
        
        // 5. Ambil data yang sudah difilter (atau semua data jika search kosong)
        $data = $query->latest()->get();

        // 6. Ubah (map) data untuk ditampilkan
        return $data->map(function ($record) {
            
            $status = $record->current_stage;
            if ($status == 'completed') {
                $status = 'Selesai';
            } elseif (isset($this->stages[$status])) {
                $status = $this->stages[$status]['label'];
            } else {
                $status = 'Sedang Berlangsung'; // Default untuk 'active'
            }

            return [
                'vehicle_name'   => $record->vehicle_name,
                'plate_number'   => $record->plate_number,
                'description'    => $record->description,
                'security_start' => $record->security_start ? $record->security_start->format('d/m/Y H:i') : '-',
                'security_end'   => $record->security_end ? $record->security_end->format('d/m/Y H:i') : '-',
                'loading_start'  => $record->loading_start ? $record->loading_start->format('d/m/Y H:i') : '-',
                'loading_end'    => $record->loading_end ? $record->loading_end->format('d/m/Y H:i') : '-',
                'ttb_start'      => $record->ttb_start ? $record->ttb_start->format('d/m/Y H:i') : '-',
                'ttb_end'        => $record->ttb_end ? $record->ttb_end->format('d/m/Y H:i') : '-',
                'current_stage'  => $status,
                'created_at'     => $record->created_at->format('d/m/Y H:i'),
            ];
        });
    }

    /**
     * Fungsi Headings
     */
    public function headings(): array
    {
        return [
            'Nama Kendaraan', 'Plat Nomor', 'Keterangan',
            'Security Mulai', 'Security Selesai',
            'Bongkar Muat Mulai', 'Bongkar Muat Selesai',
            'Officer TTB Mulai', 'Officer TTB Selesai',
            'Status Terakhir', 'Tanggal Dibuat'
        ];
    }

    // Definisikan stages di sini agar bisa diakses oleh 'collection'
    public $stages = [
        'security' => ['label' => 'Security'],
        'loading'  => ['label' => 'Bongkar Muat'],
        'ttb'      => ['label' => 'Officer TTB'],
    ];
}