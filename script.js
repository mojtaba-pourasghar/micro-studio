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

  // شمارنده
  var fa = ["۰","۱","۲","۳","۴","۵","۶","۷","۸","۹"];
  var toFa = function (n) { return String(n).replace(/\d/g, function (d) { return fa[+d]; }); };
  var nums = document.querySelectorAll("[data-to]");
  if (nums.length && !reduce) {
    var no = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        no.unobserve(e.target);
        var el = e.target, to = +el.getAttribute("data-to"), t0 = 0;
        requestAnimationFrame(function step(t) {
          if (!t0) t0 = t;
          var k = Math.min((t - t0) / 1100, 1);
          el.textContent = toFa(Math.round(to * (1 - Math.pow(1 - k, 3))));
          if (k < 1) requestAnimationFrame(step);
        });
      });
    }, { threshold: 0.6 });
    nums.forEach(function (e) { no.observe(e); });
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
    document.addEventListener("keydown", function (e) { if (e.key === "Escape" && !lb.hidden) close(); });
  }
})();
