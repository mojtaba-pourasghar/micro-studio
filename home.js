(function () {
  "use strict";
  var reduce = matchMedia("(prefers-reduced-motion: reduce)").matches;

  // هدر: پررنگ‌تر بعد از عبور از صحنه‌ی اول
  var hdr = document.querySelector(".site-header"), stage = document.querySelector(".stage");
  if (hdr && stage) {
    new IntersectionObserver(function (es) {
      hdr.classList.toggle("solid", !es[0].isIntersecting);
    }, { rootMargin: "-70px 0px 0px 0px" }).observe(stage);
  }

  // نمایان شدن بخش‌ها
  var items = document.querySelectorAll(".reveal");
  if (reduce) { items.forEach(function (e) { e.classList.add("in"); }); }
  else {
    var ro = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add("in"); ro.unobserve(e.target); } });
    }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
    items.forEach(function (e) { ro.observe(e); });
  }

  // شمارنده‌ی آمار با رقم فارسی
  var fa = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
  function toFa(n) { return String(n).replace(/\d/g, function (d) { return fa[+d]; }); }
  var nums = document.querySelectorAll(".stats strong[data-to]");
  if (nums.length && !reduce) {
    var no = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        no.unobserve(e.target);
        var el = e.target, to = +el.getAttribute("data-to"), t0 = 0;
        if (!to) return;
        requestAnimationFrame(function step(t) {
          if (!t0) t0 = t;
          var k = Math.min((t - t0) / 1100, 1), v = Math.round(to * (1 - Math.pow(1 - k, 3)));
          el.textContent = toFa(v);
          if (k < 1) requestAnimationFrame(step);
        });
      });
    }, { threshold: 0.6 });
    nums.forEach(function (e) { no.observe(e); });
  }

  // پارالاکس ملایم گوشی در صحنه‌ی اول
  var art = document.querySelector("[data-par] .hero-dev");
  if (art && !reduce) {
    var tick = false;
    addEventListener("scroll", function () {
      if (tick) return; tick = true;
      requestAnimationFrame(function () {
        tick = false;
        var y = Math.min(scrollY, 700);
        art.style.setProperty("--py", (-y * 0.06).toFixed(1) + "px");
      });
    }, { passive: true });
  }
})();
