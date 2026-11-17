{{-- Sends the admin to the login page when the session has expired, instead of Livewire's "page expired" dialog. --}}
<script>
(() => {
  const loginUrl = @json(route('filament.admin.auth.login')) + '?expired=1';
  const aliveUrl = @json(route('session.alive'));
  const lifetimeMs = {{ (int) config('session.lifetime') }} * 60 * 1000;
  let timer;
  const toLogin = () => { window.location.href = loginUrl; };
  const check = () => fetch(aliveUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
    .then((r) => r.json()).then((d) => { if (!d.auth) toLogin(); else arm(); }).catch(arm);
  const arm = () => { clearTimeout(timer); timer = setTimeout(check, lifetimeMs + 5000); };
  arm();
  document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') check(); });
  document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail, succeed }) => {
      succeed(() => arm());
      fail(({ status, preventDefault }) => { if (status === 419 || status === 401) { preventDefault(); toLogin(); } });
    });
  });
})();
</script>
