(function () {
  "use strict";
  var reduce = matchMedia("(prefers-reduced-motion: reduce)").matches;
  var hdr = document.getElementById("hdr");
  var nav = document.getElementById("nav");
  var burger = document.querySelector(".burger");

  // هدر: پس‌زمینه بعد از اسکرول
  var onScroll = function () { hdr.classList.toggle("solid", scrollY > 40); };
  onScroll(); addEventListener("scroll", onScroll, { passive: true });

  // منوی موبایل
  if (burger && nav) {
    var setOpen = function (o) { burger.setAttribute("aria-expanded", o ? "true" : "false"); nav.classList.toggle("open", o); };
    burger.addEventListener("click", function () { setOpen(burger.getAttribute("aria-expanded") !== "true"); });
    nav.addEventListener("click", function (e) { if (e.target.tagName === "A") setOpen(false); });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && burger.getAttribute("aria-expanded") === "true") { setOpen(false); burger.focus(); }
    });
  }

  // نمایان شدن بخش‌ها
  var items = document.querySelectorAll(".rv");
  if (reduce) { items.forEach(function (e) { e.classList.add("in"); }); }
  else {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } });
    }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
    items.forEach(function (e) { io.observe(e); });
  }

  // پخش ویدیو
  var lb = document.getElementById("lb");
  var play = document.querySelector(".play");
  if (lb && play) {
    var box = lb.querySelector(".lb-in"), x = lb.querySelector(".lb-x");
    var close = function () {
      lb.hidden = true; box.innerHTML = ""; document.body.style.overflow = ""; play.focus();
    };
    play.addEventListener("click", function () {
      box.innerHTML = '<video controls autoplay playsinline src="' + play.getAttribute("data-video") + '"></video>';
      lb.hidden = false; document.body.style.overflow = "hidden"; x.focus();
    });
    x.addEventListener("click", close);
    lb.addEventListener("click", function (e) { if (e.target === lb) close(); });
    document.addEventListener("keydown", function (e) {
      if (lb.hidden) return;
      if (e.key === "Escape") { close(); return; }
      if (e.key === "Tab") {
        var f = lb.querySelectorAll("button,video[controls]");
        if (!f.length) return;
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
  }
})();
