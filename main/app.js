(function () {
  const navLinks = document.querySelector(".nav-links");
  const menuButton = document.querySelector("[data-menu-button]");

  if (menuButton && navLinks) {
    menuButton.addEventListener("click", () => {
      const isOpen = navLinks.classList.toggle("active");
      menuButton.setAttribute("aria-expanded", String(isOpen));
    });
  }

  document.querySelectorAll(".nav-links a").forEach((link) => {
    link.addEventListener("click", () => {
      navLinks?.classList.remove("active");
      menuButton?.setAttribute("aria-expanded", "false");
    });
  });

  const sections = document.querySelectorAll("main section[id]");
  const hashLinks = document.querySelectorAll('.nav-links a[href^="#"]');

  if (sections.length && hashLinks.length) {
    const updateActiveLink = () => {
      let current = sections[0].id;

      sections.forEach((section) => {
        if (window.scrollY >= section.offsetTop - 150) {
          current = section.id;
        }
      });

      hashLinks.forEach((link) => {
        link.classList.toggle("active", link.getAttribute("href") === `#${current}`);
      });
    };

    updateActiveLink();
    window.addEventListener("scroll", updateActiveLink, { passive: true });
  }

  const page = document.body.dataset.page;

  if (page === "home") initHomePage();
  if (page === "appointment") initAppointmentPage();
  if (page === "assistant") initAssistantPage();
  if (page === "auth") initAuthPage();
})();

function initHomePage() {
  const searchInput = document.getElementById("searchInput");
  const specialtyFilter = document.getElementById("specialtyFilter");
  const cards = Array.from(document.querySelectorAll(".doctor-card"));
  const emptyState = document.getElementById("emptyState");

  const filterDoctors = () => {
    const query = (searchInput?.value || "").toLowerCase();
    const specialty = specialtyFilter?.value || "all";
    let visibleCount = 0;

    cards.forEach((card) => {
      const haystack = card.textContent.toLowerCase();
      const matchesQuery = !query || haystack.includes(query);
      const matchesSpecialty = specialty === "all" || card.dataset.specialty === specialty;
      const isVisible = matchesQuery && matchesSpecialty;
      card.style.display = isVisible ? "" : "none";
      if (isVisible) visibleCount += 1;
    });

    if (emptyState) {
      emptyState.style.display = visibleCount ? "none" : "block";
    }
  };

  searchInput?.addEventListener("input", filterDoctors);
  specialtyFilter?.addEventListener("change", filterDoctors);
}

function initAppointmentPage() {
  const dateInput = document.getElementById("date");
  const params = new URLSearchParams(window.location.search);
  const doctorName = params.get("doctor");

  if (dateInput) {
    dateInput.min = new Date().toISOString().split("T")[0];
  }

  if (doctorName) {
    const select = document.getElementById("doctor");
    Array.from(select?.options || []).forEach((option) => {
      if (option.text.includes(doctorName)) {
        select.value = option.value;
      }
    });
  }
}

function initAssistantPage() {
  const messages = document.getElementById("chatMessages");
  const input = document.getElementById("chatInput");
  const form = document.getElementById("chatForm");

  document.querySelectorAll("[data-prompt]").forEach((button) => {
    button.addEventListener("click", () => {
      if (!input) return;
      input.value = button.dataset.prompt || "";
      input.focus();
    });
  });

  const addMessage = (text, type) => {
    if (!messages) return;
    const message = document.createElement("div");
    message.className = `chat-msg ${type}`;
    message.textContent = text;
    messages.appendChild(message);
    messages.scrollTop = messages.scrollHeight;
  };

  const getAssistantReply = (text) => {
    const concern = text.toLowerCase();
    const emergencyWords = [
      "chest pain",
      "difficulty breathing",
      "severe bleeding",
      "fainting",
      "stroke",
      "unconscious"
    ];

    if (emergencyWords.some((word) => concern.includes(word))) {
      return "These symptoms can be urgent. Please seek emergency medical help immediately or contact local emergency services.";
    }

    if (concern.includes("fever") || concern.includes("cough") || concern.includes("throat")) {
      return "For fever, cough, or sore throat, rest, drink fluids, and monitor temperature. Book a doctor visit if symptoms are severe, last more than 2-3 days, or include breathing difficulty.";
    }

    if (concern.includes("rash") || concern.includes("itch")) {
      return "For rash or itching, avoid scratching and note any new food, medicine, or skin product. Book a dermatologist if it spreads, becomes painful, or comes with fever.";
    }

    if (concern.includes("headache") || concern.includes("migraine")) {
      return "For headache, rest in a quiet place, hydrate, and track triggers. Seek care quickly if it is sudden, severe, follows an injury, or affects vision, speech, or movement.";
    }

    return "Thanks for sharing. Please monitor your symptoms and book an appointment if they worsen, continue, or worry you. Add duration, severity, medicines taken, and existing conditions for better guidance.";
  };

  const saveAssistantLog = (message, reply) => {
    window
      .fetch("assistant_log.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message, reply })
      })
      .catch(() => {});
  };

  form?.addEventListener("submit", (event) => {
    event.preventDefault();
    const text = input?.value.trim();
    if (!text) return;

    addMessage(text, "user");
    input.value = "";

    window.setTimeout(() => {
      const reply = getAssistantReply(text);
      addMessage(reply, "bot");
      saveAssistantLog(text, reply);
    }, 350);
  });
}

function initAuthPage() {
  const alertBox = document.getElementById("formAlert");
  if (!alertBox) return;

  const params = new URLSearchParams(window.location.search);
  const state = params.get("status") || params.get("error");
  if (!state) return;

  const messages = {
    registered: "Account created. You can now sign in.",
    auth: "Please login before viewing bookings or creating appointments.",
    invalid: "Please check your details and try again.",
    missing: "Please complete all required fields.",
    mismatch: "Passwords do not match.",
    duplicate: "An account already exists for that email.",
    failed: "We could not complete that request. Please try again.",
    credentials: "Email or password is incorrect.",
    method: "Please submit the form from this page."
  };

  alertBox.textContent = messages[state] || messages.failed;
  alertBox.classList.toggle("success", state === "registered");
  alertBox.style.display = "block";
}
