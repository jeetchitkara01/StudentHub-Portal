(function() {
    function applyTheme() {
        const savedTheme = localStorage.getItem("studentHubTheme");
        const themeBtn = document.getElementById("themeToggleBtn");

        if (savedTheme === "dark") {
            document.body.classList.add("dark-theme");
            if (themeBtn) themeBtn.textContent = "Light Mode";
        } else {
            document.body.classList.remove("dark-theme");
            if (themeBtn) themeBtn.textContent = "Dark Mode";
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        applyTheme();

        const themeBtn = document.getElementById("themeToggleBtn");
        const hamburgerBtn = document.getElementById("hamburgerBtn");
        const mainNav = document.getElementById("mainNav");

        if (themeBtn) {
            themeBtn.addEventListener("click", function(e) {
                e.preventDefault();
                document.body.classList.toggle("dark-theme");
                const isDark = document.body.classList.contains("dark-theme");
                localStorage.setItem("studentHubTheme", isDark ? "dark" : "light");
                themeBtn.textContent = isDark ? "Light Mode" : "Dark Mode";
            });
        }

        if (hamburgerBtn && mainNav) {
            hamburgerBtn.addEventListener("click", function(e) {
                e.preventDefault();
                if (window.innerWidth <= 768) {
                    mainNav.classList.toggle("show");
                } else {
                    mainNav.classList.toggle("collapsed");
                }
            });
        }
    });
})();