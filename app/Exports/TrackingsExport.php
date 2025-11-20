<?php
// app/Exports/TrackingsExport.php

namespace App\Exports;

use App\Models\Tracking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class TrackingsExport implements FromCollection, WithHeadings
{
    use Exportable;

    // untuk filter search dari Livewire
    protected $search;

    public function __construct(string $search = null)
    {
        $this->search = $search;
    }

    /**
     * Data yang diexport
     */

    public $start_date, $end_date;

    public function collection()
    {
        $query = Tracking::query();

        if ($this->start_date) {
                    $query->whereDate('security_start', '>=', $this->start_date);
                }

                if ($this->end_date) {
                    $query->whereDate('security_start', '<=', $this->end_date);
                }

        // filter search (kalau ada)
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('vehicle_name', 'like', '%' . $this->search . '%')
                  ->orWhere('plate_number', 'like', '%' . $this->search . '%')
                  ->orWhere('driver_name', 'like', '%' . $this->search . '%')
                  ->orWhere('type', 'like', '%' . $this->search . '%');
            });
        }

        $data = $query->latest()->get();

        return $data->map(function ($record) {
            return [
                // IDENTITAS KENDARAAN & SUPIR
                'vehicle_name'        => $record->vehicle_name,
                'company_name'        => $record->company_name,
                'plate_number'        => $record->plate_number,
                'vehicle_kind'        => $record->vehicle_kind,
                'destination'         => $record->destination,
                'type'                => $record->type ? strtoupper($record->type) : null, // BONGKAR / MUAT

                'driver_name'         => $record->driver_name,
                'driver_phone'        => $record->driver_phone,
                'driver_identity'     => $record->driver_identity,

                // SECURITY MASUK / KELUAR
                'security_start'      => $this->formatDateTime($record->security_start),
                'security_in_officer' => $record->security_in_officer,
                'security_end'        => $this->formatDateTime($record->security_end),
                'security_out_officer'=> $record->security_out_officer,

                // BONGKAR / MUAT
                'loading_start'       => $this->formatDateTime($record->loading_start),
                'loading_start_officer'=> $record->loading_start_officer,
                'loading_end'         => $this->formatDateTime($record->loading_end),
                'loading_end_officer' => $record->loading_end_officer,

                // OFFICER TTB
                'ttb_start'           => $this->formatDateTime($record->ttb_start),
                'ttb_start_officer'   => $record->ttb_start_officer,
                'ttb_end'             => $this->formatDateTime($record->ttb_end),
                'ttb_end_officer'     => $record->ttb_end_officer,

                // DISTRIBUSI KE SUPIR
                'distribution_at'     => $this->formatDateTime($record->distribution_at),
                'distribution_officer'=> $record->distribution_officer,

                // KHUSUS PROSES BONGKAR
                'sj_number'           => $record->sj_number,
                'item_name'           => $record->item_name,
                'item_quantity'       => $record->item_quantity,

                // LAIN2
                'description'         => $record->description,
                'status'              => $this->statusLabel($record->current_stage),
                'created_at'          => $this->formatDateTime($record->created_at),
            ];
        });
    }

    /**
     * Judul kolom di Excel
     */
    public function headings(): array
    {
        return [
            // IDENTITAS KENDARAAN & SUPIR
            'Nama Kendaraan / Vendor',
            'Nama Instansi / Perusahaan',
            'Plat Nomor',
            'Jenis Kendaraan',
            'Tujuan',
            'Jenis Kegiatan (B/M)',

            'Nama Supir',
            'Nomor HP Supir',
            'Identitas Supir (KTP/SIM)',

            // SECURITY
            'Security Masuk - Waktu',
            'Security Masuk - Nama Petugas',
            'Security Keluar - Waktu',
            'Security Keluar - Nama Petugas',

            // BONGKAR / MUAT
            'Bongkar/Muat Mulai - Waktu',
            'Bongkar/Muat Mulai - Nama Petugas',
            'Bongkar/Muat Selesai - Waktu',
            'Bongkar/Muat Selesai - Nama Petugas',

            // OFFICER TTB
            'TTB Mulai - Waktu',
            'TTB Mulai - Nama Officer',
            'TTB Selesai - Waktu',
            'TTB Selesai - Nama Officer',

            // DISTRIBUSI
            'Distribusi ke Supir - Waktu',
            'Distribusi ke Supir - Nama Petugas',

            // DATA BONGKAR
            'No. Surat Jalan',
            'Nama Barang',
            'Jumlah Barang',

            // LAIN2
            'Keterangan',
            'Status Terakhir',
            'Tanggal Dibuat (Record)',
        ];
    }

    /**
     * Helper format tanggal
     */
    protected function formatDateTime($value): ?string
    {
        return $value ? $value->format('d/m/Y H:i') : null;
    }

    /**
     * Helper label status
     */
    protected function statusLabel(?string $stage): string
    {
        return match ($stage) {
            'security_in'      => 'Security Masuk',
            'loading_started'  => 'Proses Bongkar/Muat',
            'loading_ended'    => 'Selesai Bongkar/Muat',
            'ttb_started'      => 'Proses TTB',
            'ttb_ended'        => 'Selesai TTB',
            'ttb_distributed'  => 'Distribusi ke Supir',
            'completed'        => 'Selesai',
            'canceled'         => 'Dibatalkan',
            default            => 'Sedang Berlangsung',
        };
    }
}
