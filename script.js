/* میکرو استودیو — رفتار صفحه: منوی موبایل و نشان‌دادن بخش فعال در منو */
(function () {
  "use strict";

  var btn = document.querySelector(".menu-btn");
  var nav = document.getElementById("site-nav");
  if (!btn || !nav) return;

  function setMenu(open) {
    btn.setAttribute("aria-expanded", String(open));
    nav.classList.toggle("is-open", open);
  }

  btn.addEventListener("click", function () {
    setMenu(btn.getAttribute("aria-expanded") !== "true");
  });

  nav.addEventListener("click", function (e) {
    if (e.target.closest("a")) setMenu(false);
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && btn.getAttribute("aria-expanded") === "true") {
      setMenu(false);
      btn.focus();
    }
  });

  window.matchMedia("(min-width: 900px)").addEventListener("change", function (m) {
    if (m.matches) setMenu(false);
  });

  /* بخش فعال در منو */
  if (!("IntersectionObserver" in window)) return;
  var links = {};
  nav.querySelectorAll('a[href^="#"]:not(.btn)').forEach(function (a) {
    links[a.getAttribute("href").slice(1)] = a;
  });
  var io = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (en) {
        var link = links[en.target.id];
        if (!link) return;
        if (en.isIntersecting) {
          Object.keys(links).forEach(function (k) { links[k].removeAttribute("aria-current"); });
          link.setAttribute("aria-current", "true");
        } else if (link.getAttribute("aria-current")) {
          link.removeAttribute("aria-current");
        }
      });
    },
    { rootMargin: "-45% 0px -50% 0px" }
  );
  Object.keys(links).forEach(function (id) {
    var sec = document.getElementById(id);
    if (sec) io.observe(sec);
  });
})();
