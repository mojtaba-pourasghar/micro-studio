(function () {
  "use strict";

  // منوی موبایل
  var btn = document.querySelector(".menu-btn");
  var nav = document.getElementById("site-nav");
  if (btn && nav) {
    var setOpen = function (open) {
      btn.setAttribute("aria-expanded", open ? "true" : "false");
      nav.classList.toggle("open", open);
    };
    btn.addEventListener("click", function () {
      setOpen(btn.getAttribute("aria-expanded") !== "true");
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && btn.getAttribute("aria-expanded") === "true") {
        setOpen(false);
        btn.focus();
      }
    });
  }

  // گالری با لایت‌باکس
  var shots = Array.prototype.slice.call(document.querySelectorAll(".shot"));
  if (!shots.length) return;

  var box = document.createElement("div");
  box.className = "lightbox";
  box.setAttribute("role", "dialog");
  box.setAttribute("aria-modal", "true");
  box.setAttribute("aria-label", "نمایش بزرگ تصویر");
  box.innerHTML =
    '<button type="button" class="lb-btn lb-close" aria-label="بستن">×</button>' +
    '<button type="button" class="lb-btn lb-prev" aria-label="تصویر قبلی">›</button>' +
    '<button type="button" class="lb-btn lb-next" aria-label="تصویر بعدی">‹</button>' +
    '<img alt=""><p></p>';
  document.body.appendChild(box);

  var img = box.querySelector("img");
  var cap = box.querySelector("p");
  var idx = 0;
  var opener = null;

  function show(i) {
    idx = (i + shots.length) % shots.length;
    img.src = shots[idx].getAttribute("data-full");
    img.alt = shots[idx].getAttribute("data-cap") || "";
    cap.textContent = shots[idx].getAttribute("data-cap") || "";
  }
  function open(i) {
    opener = shots[i];
    show(i);
    box.classList.add("open");
    document.body.style.overflow = "hidden";
    box.querySelector(".lb-close").focus();
  }
  function close() {
    box.classList.remove("open");
    document.body.style.overflow = "";
    if (opener) opener.focus();
  }

  shots.forEach(function (s, i) {
    s.addEventListener("click", function () { open(i); });
  });
  box.querySelector(".lb-close").addEventListener("click", close);
  box.querySelector(".lb-prev").addEventListener("click", function () { show(idx - 1); });
  box.querySelector(".lb-next").addEventListener("click", function () { show(idx + 1); });
  box.addEventListener("click", function (e) { if (e.target === box) close(); });
  document.addEventListener("keydown", function (e) {
    if (!box.classList.contains("open")) return;
    if (e.key === "Escape") close();
    // در RTL فلش چپ = بعدی
    else if (e.key === "ArrowLeft") show(idx + 1);
    else if (e.key === "ArrowRight") show(idx - 1);
    else if (e.key === "Tab") {
      var f = box.querySelectorAll("button");
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });
})();
