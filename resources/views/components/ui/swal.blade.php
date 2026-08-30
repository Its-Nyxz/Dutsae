<div>
    <!-- SweetAlert2 Script CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Helper function for Swal Toast Notification (Top-Center, Compact, Won't overlap modals)
            window.showSwalToast = function(message, icon = 'success') {
                const isDark = document.documentElement.classList.contains('dark');
                
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top', // Di atas tengah layar
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: isDark ? '#1e293b' : '#ffffff',
                    color: isDark ? '#f8fafc' : '#0f172a',
                    customClass: {
                        container: '!z-[999999]',
                        popup: 'rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl py-2.5 px-5 text-xs sm:text-sm font-bold font-sans !top-4 !m-0 !w-auto max-w-md backdrop-blur-md',
                        title: '!text-xs sm:!text-sm !font-bold !m-0 !p-0',
                        timerProgressBar: '!bg-amber-500'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });

                Toast.fire({
                    icon: icon,
                    title: message
                });
            };

            // Helper function for Swal Alert (Routed to Top-Center compact toast so it never covers modals)
            window.showSwalAlert = function(title, text, icon = 'info') {
                let msg = '';
                if (title && text && title !== text) {
                    msg = title + ': ' + text;
                } else {
                    msg = title || text || '';
                }
                showSwalToast(msg, icon);
            };

            // Helper function for Swal Confirmation Dialog (Only for critical deletions with confirm/cancel buttons)
            window.confirmSwal = function(title, text, confirmCallback) {
                const isDark = document.documentElement.classList.contains('dark');
                Swal.fire({
                    title: title || 'Konfirmasi Hapus',
                    text: text || 'Apakah Anda yakin ingin menghapus data ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Lanjutkan!',
                    cancelButtonText: 'Batal',
                    background: isDark ? '#1e293b' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f172a',
                    customClass: {
                        container: '!z-[999999]',
                        popup: 'rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl font-sans max-w-sm p-6',
                        title: '!text-lg !font-black',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 cursor-pointer',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5 cursor-pointer'
                    }
                }).then((result) => {
                    if (result.isConfirmed && typeof confirmCallback === 'function') {
                        confirmCallback();
                    }
                });
            };

            // Session Flash Listeners
            @if (session('success'))
                showSwalToast(@js(session('success')), 'success');
            @endif

            @if (session('error'))
                showSwalToast(@js(session('error')), 'error');
            @endif

            @if (session('warning'))
                showSwalToast(@js(session('warning')), 'warning');
            @endif

            @if (session('info'))
                showSwalToast(@js(session('info')), 'info');
            @endif

            // Livewire Event Listeners
            window.addEventListener('swal', event => {
                const data = event.detail[0] || event.detail;
                const text = data.text || data.message || '';
                const title = data.title ? data.title : '';
                const fullMsg = (title && text && title !== text) ? `${title}: ${text}` : (title || text || '');
                showSwalToast(fullMsg, data.icon || 'info');
            });

            window.addEventListener('swal-toast', event => {
                const data = event.detail[0] || event.detail;
                showSwalToast(data.message || data.title || '', data.icon || 'success');
            });

            window.addEventListener('swal-confirm', event => {
                const data = event.detail[0] || event.detail;
                confirmSwal(data.title, data.text, () => {
                    if (data.action) {
                        Livewire.dispatch(data.action, data.params || []);
                    }
                });
            });
        });
    </script>
</div>
