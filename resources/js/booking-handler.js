export default () => ({
    async confirmSubmit() {
        // Run basic client-side validation check
        if (!this.$wire.name || !this.$wire.whatsapp_number || !this.$wire.service || !this.$wire.location_id) {
            this.$wire.submit(); // Let Livewire show validation errors
            return;
        }

        const result = await Swal.fire({
            title: '<div class=\'flex flex-col items-center gap-2\'><div class=\'p-3 rounded-full\'><svg class=\'w-8 h-8 text-indigo-500\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2\'></path></svg></div><span class=\'text-2xl font-black text-gray-900 dark:text-white mt-2 tracking-tight\'>Konfirmasi Data</span></div>',
            html: `
                <div class='mt-6 text-left overflow-hidden rounded-[2rem] border border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-800'>
                    <div class='p-6 space-y-5'>
                        <div class='group'>
                            <label class='text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.2em] mb-1 block'>Informasi Personal</label>
                            <div class='flex flex-col gap-1 pl-1'>
                                <span class='text-lg font-bold text-gray-900 dark:text-white'>${this.$wire.name}</span>
                                <span class='text-sm text-gray-600 dark:text-gray-400 font-bold'>${this.$wire.whatsapp_number}</span>
                            </div>
                        </div>
                        
                        <div class='h-px bg-gray-200 dark:bg-gray-700'></div>

                        <div class='grid grid-cols-2 gap-4'>
                            <div>
                                <label class='text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-[0.2em] mb-1 block'>Layanan</label>
                                <span class='block font-black text-indigo-600 dark:text-indigo-400'>${this.$wire.service}</span>
                            </div>
                            <div>
                                <label class='text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-[0.2em] mb-1 block'>Lokasi Kantor</label>
                                <span class='block font-bold text-gray-900 dark:text-white text-sm'>${document.querySelector('[wire\\:model="location_id"] option:checked').text}</span>
                            </div>
                        </div>

                        <div class='p-4 bg-indigo-600 rounded-2xl flex items-center gap-3 shadow-lg shadow-indigo-600/20'>
                            <div class='p-2 bg-white/20 rounded-xl text-white'>
                                <svg class='w-4 h-4 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'></path></svg>
                            </div>
                            <span class='text-sm font-black text-white'>${this.$wire.booking_date} • ${this.$wire.booking_time} WIB</span>
                        </div>
                    </div>
                </div>
                <p class='text-[12px] text-gray-500 dark:text-gray-400 mt-6 text-center leading-relaxed px-4 font-medium'>Pastikan data di atas sudah benar. Kami akan mengirimkan notifikasi konfirmasi melalui WhatsApp.</p>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ya, Submit Booking',
            cancelButtonText: 'Perbaiki Data',
            reverseButtons: true,
            background: '#374151',
            color: '#ffffff',
            customClass: {
                popup: 'rounded-[3rem] border border-gray-600 shadow-2xl p-8',
                confirmButton: 'rounded-2xl px-8 py-4 font-black text-sm tracking-wide bg-indigo-600 hover:bg-indigo-700 transition !ring-0',
                cancelButton: 'rounded-2xl px-8 py-4 font-black text-sm tracking-wide bg-gray-600 text-white hover:bg-gray-500 transition !ring-0'
            }
        });

        if (result.isConfirmed) {
            this.$wire.submit();
        }
    }
})
