(function () {
  "use strict";
  var reduce = matchMedia("(prefers-reduced-motion: reduce)").matches;

  // هدر: شیشه‌ای روی هیرو، روشن بعد از آن
  var hdr = document.querySelector(".site-header");
  var hero = document.querySelector(".h2o");
  if (hdr && hero) {
    var io = new IntersectionObserver(function (es) {
      hdr.classList.toggle("solid", !es[0].isIntersecting);
    }, { rootMargin: "-70px 0px 0px 0px" });
    io.observe(hero);
  }

  // نمایان شدن بخش‌ها هنگام اسکرول
  var items = document.querySelectorAll(".reveal");
  if (reduce) {
    items.forEach(function (el) { el.classList.add("in"); });
  } else {
    var ro = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add("in"); ro.unobserve(e.target); }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
    items.forEach(function (el) { ro.observe(el); });
  }

  // چرخش ملایم گوشی با حرکت اشاره‌گر
  var dev = document.querySelector("[data-tilt]");
  if (dev && !reduce && matchMedia("(pointer:fine)").matches) {
    var frame = null;
    window.addEventListener("mousemove", function (e) {
      if (frame) return;
      frame = requestAnimationFrame(function () {
        frame = null;
        var r = dev.getBoundingClientRect();
        var x = (e.clientX - (r.left + r.width / 2)) / innerWidth;
        var y = (e.clientY - (r.top + r.height / 2)) / innerHeight;
        dev.style.setProperty("--ry", (x * 10).toFixed(2) + "deg");
        dev.style.setProperty("--rx", (-y * 8).toFixed(2) + "deg");
      });
    });
  }
})();
