<div>
    <!-- SweetAlert2 Script CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Helper function for Swal Popup Alert
            window.showSwalAlert = function(title, text, icon = 'success') {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#f59e0b',
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#0f172a',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl font-sans'
                    }
                });
            };

            // Helper function for Swal Toast Notification
            window.showSwalToast = function(message, icon = 'success') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#0f172a',
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: icon,
                    title: message
                });
            };

            // Helper function for Swal Confirmation Dialog
            window.confirmSwal = function(title, text, confirmCallback) {
                Swal.fire({
                    title: title || 'Konfirmasi Hapus',
                    text: text || 'Apakah Anda yakin ingin menghapus data ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus Data!',
                    cancelButtonText: 'Batal',
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#0f172a',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl font-sans'
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
                showSwalAlert('⚠️ Perhatian', @js(session('error')), 'error');
            @endif

            // Livewire Event Listeners
            window.addEventListener('swal', event => {
                const data = event.detail[0] || event.detail;
                showSwalAlert(data.title || 'Informasi', data.text || data.message || '', data.icon || 'info');
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
