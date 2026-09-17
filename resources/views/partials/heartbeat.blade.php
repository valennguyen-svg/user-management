@auth
    <script>
        (function () {
            const url = @json(route('heartbeat'));
            const loginUrl = @json(route('login'));
            const everyMs = 10000; 
 
            function vangRa() {
                window.location.href = loginUrl + '?ly_do=1';
            }
 
            async function kiemTra() {
                if (document.hidden) return; 
 
                try {
                    const res = await fetch(url, {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store',
                    });
 
                    if (res.status === 419 || res.status === 401) {
                        return vangRa();
                    }
 
                    const data = await res.json();
 
                    if (data.state !== 'active') {
                        vangRa();
                    }
                } catch (e) {
                }
            }
 
            setInterval(kiemTra, everyMs);
 
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) kiemTra();
            });
        })();
    </script>
@endauth