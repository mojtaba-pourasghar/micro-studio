<style>
  @font-face{font-family:"Vazirmatn";src:url("{{ asset('assets/fonts/Vazirmatn-var.woff2') }}") format("woff2");font-weight:100 900;font-display:swap}
  /* logo variants */
  .ms-logo-dark{display:none}
  .dark .ms-logo-light, .fi-sidebar .ms-logo-light{display:none}
  .dark .ms-logo-dark, .fi-sidebar .ms-logo-dark{display:inline-block}
  /* dark sidebar (also in light mode) */
  .fi-sidebar, .fi-sidebar-header, .fi-sidebar-nav, .fi-sidebar-footer{background:#15162a !important;border-color:rgba(171,141,255,.14) !important}
  .fi-sidebar{box-shadow:inset -1px 0 0 rgba(171,141,255,.14)}
  .fi-sidebar .fi-sidebar-group-label{color:#8b8fb0 !important}
  .fi-sidebar .fi-sidebar-group-btn .fi-icon, .fi-sidebar .fi-sidebar-group-collapse-btn{color:#8b8fb0 !important}
  .fi-sidebar .fi-sidebar-item-label{color:#d6d8ea !important}
  .fi-sidebar .fi-sidebar-item-icon{color:#8f92b8 !important}
  .fi-sidebar .fi-sidebar-item-btn:hover, .fi-sidebar .fi-sidebar-item-btn:focus-visible{background:rgba(255,255,255,.06) !important}
  .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn{background:linear-gradient(270deg,rgba(116,87,255,.32),rgba(116,87,255,.12)) !important}
  .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-label{color:#fff !important}
  .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-icon{color:#ab8dff !important}
  .fi-sidebar .fi-sidebar-item-grouped-border-part{background:rgba(171,141,255,.25) !important}
  .fi-sidebar .fi-sidebar-close-collapse-sidebar-btn, .fi-sidebar .fi-icon-btn{color:#c3c5dc !important}
  .fi-sidebar ::-webkit-scrollbar{width:8px}.fi-sidebar ::-webkit-scrollbar-thumb{background:rgba(171,141,255,.25);border-radius:8px}
</style>
