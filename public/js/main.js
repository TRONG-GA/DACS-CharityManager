document.addEventListener("DOMContentLoaded", function () {
  // Hero Slider
  if (document.querySelector(".hero-swiper")) {
    new Swiper(".hero-swiper", {
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    });
  }

  // Event Cards Slider
  if (document.querySelector(".events-swiper")) {
    new Swiper(".events-swiper", {
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      autoplay: {
        delay: 4000,
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
        },
        992: {
          slidesPerView: 3,
        },
      },
    });
  }
});

// Amount Selection
document.querySelectorAll(".amount-option").forEach((option) => {
  option.addEventListener("click", function () {
    document
      .querySelectorAll(".amount-option")
      .forEach((o) => o.classList.remove("active"));
    this.classList.add("active");

    const amount = this.dataset.amount;
    if (amount !== "custom") {
      document.getElementById("donationAmount").value = amount;
    } else {
      document.getElementById("donationAmount").value = "";
      document.getElementById("donationAmount").focus();
    }
  });
});

// Form Validation
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return true;

  const inputs = form.querySelectorAll("[required]");
  let isValid = true;

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      isValid = false;
      input.classList.add("is-invalid");
    } else {
      input.classList.remove("is-invalid");
    }
  });

  return isValid;
}

// Preview Image
function previewImage(input, previewId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById(previewId).src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Auto-dismiss alerts
document.querySelectorAll(".alert").forEach((alert) => {
  setTimeout(() => {
    const bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  }, 5000);
});

// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// Format currency input
function formatCurrency(input) {
  let value = input.value.replace(/\D/g, "");
  value = new Intl.NumberFormat("vi-VN").format(value);
  input.value = value;
}
// Duplicate breaking news for continuous scroll
const ticker = document.querySelector(".breaking-ticker");
if (ticker) {
  const items = ticker.innerHTML;
  ticker.innerHTML = items + items; // Duplicate for seamless loop
}

// Initialize Hero News Swiper with auto-rotate (3 seconds)
const heroSwiper = new Swiper(".hero-news-swiper", {
  loop: true,
  autoplay: {
    delay: 3000, // 3 giây
    disableOnInteraction: false,
  },
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
  effect: "fade",
  fadeEffect: {
    crossFade: true,
  },
});

// Counter Animation
const animateCounters = () => {
  const counters = document.querySelectorAll(".stat-number[data-target]");
  counters.forEach((counter) => {
    const target = parseInt(counter.getAttribute("data-target"));
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;

    const updateCounter = () => {
      current += increment;
      if (current < target) {
        counter.textContent = Math.floor(current);
        requestAnimationFrame(updateCounter);
      } else {
        counter.textContent = target;
      }
    };
    updateCounter();
  });
};

// Trigger counter animation
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        animateCounters();
        observer.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.5 },
);

const statsSection = document.querySelector(".stats-section");
if (statsSection) observer.observe(statsSection);

// Animate progress bars
const animateProgressBars = () => {
  const progressBars = document.querySelectorAll(
    ".progress-bar[data-progress]",
  );
  progressBars.forEach((bar) => {
    const progress = bar.getAttribute("data-progress");
    setTimeout(() => {
      bar.style.width = Math.min(progress, 100) + "%";
    }, 100);
  });
};

animateProgressBars();
