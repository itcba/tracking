<div class="max-w-2xl mx-auto mt-10">
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
            <div class="flex items-center justify-center space-x-3">
                <div class="bg-white/20 p-2 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold">Input Kendaraan (Supir)</h1>
            </div>
            <p class="text-center text-blue-100 mt-2">Formulir pendaftaran kendaraan keluar/masuk</p>
        </div>

        <div class="p-6">
            @if (session()->has('message'))
                <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-green-800 font-medium">{{ session('message') }}</p>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="submit" class="space-y-6">
                
                {{-- JENIS KEGIATAN --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <span class="text-blue-600 font-bold text-sm">1</span>
                        </div>
                        <label class="block text-sm font-bold text-gray-800">
                            Jenis Kegiatan <span class="text-red-500">*</span>
                        </label>
                    </div>

                    <select
                        wire:model="type"
                        class="w-full border-2 border-gray-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Pilih jenis kegiatan...</option>
                        <option value="bongkar">BONGKAR</option>
                        <option value="muat">MUAT</option>
                    </select>

                    <p class="text-xs text-gray-500 mt-3 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pilih dulu jenis kegiatan. Form di bawah akan menyesuaikan (BONGKAR / MUAT).
                    </p>

                    @error('type') 
                        <p class="text-xs text-red-600 mt-1 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $message }}
                        </p> 
                    @enderror
                </div>

                {{-- DATA KENDARAAN --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <span class="text-blue-600 font-bold text-sm">2</span>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">
                            Data Kendaraan & Supir
                            <span class="inline-block ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">
                                @if($type === 'bongkar') BONGKAR
                                @elseif($type === 'muat') MUAT
                                @else PILIH JENIS
                                @endif
                            </span>
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Company / Vendor --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Instansi / Vendor <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    wire:model="company_name"
                                    class="w-full border-2 border-gray-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                    placeholder="PT. INDAH KIAT / MCL / dll"
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('company_name') 
                                <p class="text-xs text-red-600 mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p> 
                            @enderror
                        </div>

                        {{-- Plat Nomor --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nomor Polisi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    wire:model="plate_number"
                                    class="w-full border-2 border-gray-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                    placeholder="B 9070 NYK"
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('plate_number') 
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p> 
                            @enderror
                        </div>

                        {{-- Jenis Kendaraan --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Jenis Kendaraan
                            </label>
                            <input
                                type="text"
                                wire:model="vehicle_kind"
                                class="w-full border-2 border-gray-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                placeholder="KONTAINER 20 FT / TRONTON / DLL"
                            >
                        </div>

                        {{-- Tujuan --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tujuan</label>
                            <input
                                type="text"
                                wire:model="destination"
                                class="w-full border-2 border-gray-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                placeholder="SAMARINDA / CBA / dll"
                            >
                        </div>

                        {{-- Nama Sopir --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Sopir <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="driver_name"
                                class="w-full border-2 border-gray-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                placeholder="Nama lengkap sopir"
                            >
                            @error('driver_name') 
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p> 
                            @enderror
                        </div>

                        {{-- No HP Sopir --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP Sopir</label>
                            <input
                                type="text"
                                wire:model="driver_phone"
                                class="w-full border-2 border-gray-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                placeholder="0812xxxxx"
                            >
                        </div>

                        {{-- Identitas --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Identitas</label>
                            <input
                                type="text"
                                wire:model="driver_identity"
                                class="w-full border-2 border-gray-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                placeholder="KTP / SIM / dll"
                            >
                        </div>

                        {{-- FIELD KHUSUS BONGKAR --}}
                        <div
                            x-data
                            x-show="$wire.type === 'bongkar'"
                            x-cloak
                            class="md:col-span-2 mt-4 p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200"
                        >
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-bold text-blue-800 uppercase tracking-wide">Field Tambahan Proses Bongkar</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-2">No. Surat Jalan</label>
                                    <input
                                        type="text"
                                        wire:model="sj_number"
                                        class="w-full border-2 border-blue-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                        placeholder="97892736-9900"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-2">Nama Barang</label>
                                    <input
                                        type="text"
                                        wire:model="item_name"
                                        class="w-full border-2 border-blue-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                        placeholder="KARTON BOX"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-2">Jumlah Barang</label>
                                    <input
                                        type="text"
                                        wire:model="item_quantity"
                                        class="w-full border-2 border-blue-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                        placeholder="10.000 PCS"
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                            <textarea
                                wire:model="description"
                                rows="3"
                                class="w-full border-2 border-gray-200 rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none"
                                placeholder="{{ $type === 'bongkar'
                                    ? 'BONGKAR'
                                    : ($type === 'muat' ? 'MUAT' : '') }}"
                            ></textarea>
                        </div>
                    </div>
                </div>

                {{-- SUBMIT --}}
                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-bold py-4 px-6 rounded-xl text-sm tracking-wide shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Kirim Data Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>