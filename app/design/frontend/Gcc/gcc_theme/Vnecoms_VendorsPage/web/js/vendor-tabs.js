document.querySelectorAll(".tab-link").forEach(link => {
      link.addEventListener("click", function(e) {
        e.preventDefault();
        document.querySelectorAll(".tab-link").forEach(t => t.classList.remove("active"));
        this.classList.add("active");
        document.querySelectorAll(".tab-content").forEach(c => c.classList.add("hidden"));
        document.getElementById(this.getAttribute("data-tab")).classList.remove("hidden");
      });
    });
