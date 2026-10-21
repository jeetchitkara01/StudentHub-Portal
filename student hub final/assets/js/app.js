let allEvents = [];          
let filteredEvents = [];     
let currentPage = 1;         
const itemsPerPage = 4;      

const fallbackEventsData = [
    { id: 1, title: "Annual Tech Fest 2026", category: "Technical", date: "2026-10-15", venue: "Main Auditorium", organizer: "CE Dept", description: "Inter-college technical symposium featuring robotics, project exhibitions, and coding challenges.", status: "Upcoming" },
    { id: 2, title: "24-Hour Coding Hackathon", category: "Technical", date: "2026-10-20", venue: "Computer Lab 3", organizer: "Coding Club", description: "Build innovative web and mobile apps within 24 hours. Cash prizes for top teams!", status: "Upcoming" },
    { id: 3, title: "Inter-Department Cricket Tournament", category: "Sports", date: "2026-10-25", venue: "University Ground", organizer: "Sports Council", description: "Annual cricket championship with teams from CE, IT, ME, EC, and Civil departments.", status: "Upcoming" },
    { id: 4, title: "Workshop on Cloud & Web Tech", category: "Workshops", date: "2026-11-02", venue: "Seminar Hall A", organizer: "IT Dept", description: "Hands-on session on frontend frameworks, REST APIs, and cloud deployments.", status: "Upcoming" },
    { id: 5, title: "Annual Cultural Night - Spandan", category: "Cultural", date: "2026-11-10", venue: "Open Air Theatre", organizer: "Cultural Committee", description: "Music performances, drama, dance shows, and art exhibitions by students.", status: "Upcoming" },
    { id: 6, title: "AI & Robotics Summit", category: "Technical", date: "2026-11-18", venue: "Conference Hall B", organizer: "Robotics Club", description: "Keynotes from industry leaders on Machine Learning, Autonomous Systems, and Automation.", status: "Upcoming" },
    { id: 7, title: "Badminton Championship", category: "Sports", date: "2026-11-22", venue: "Indoor Sports Complex", organizer: "Sports Council", description: "Singles and doubles tournaments for all students and faculty members.", status: "Upcoming" },
    { id: 8, title: "Cyber Security Workshop", category: "Workshops", date: "2026-11-28", venue: "Lab 5", organizer: "Cyber Cell", description: "Practical workshop covering network security, penetration testing basics, and OWASP standards.", status: "Upcoming" }
];

async function fetchEventsData() {
    const eventsContainer = document.getElementById("eventsContainer");
    if (!eventsContainer) return;

    eventsContainer.innerHTML = '<div style="text-align:center; padding:20px; color:#2563eb;">Fetching JSON data from server...</div>';

    try {
        const response = await fetch("data/events.json");
        if (!response.ok) throw new Error("HTTP error " + response.status);
        allEvents = await response.json();
    } catch (error) {
        allEvents = fallbackEventsData;
    }

    applySearchAndFilter();
}

function applySearchAndFilter() {
    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortBy = document.getElementById("sortBy");

    const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
    const selectedCategory = categoryFilter ? categoryFilter.value : "all";
    const sortValue = sortBy ? sortBy.value : "date-asc";

    filteredEvents = allEvents.filter(event => {
        const matchesSearch = (event.title || '').toLowerCase().includes(query) ||
                              (event.description || '').toLowerCase().includes(query) ||
                              (event.venue || event.location || '').toLowerCase().includes(query);
        
        const matchesCategory = (selectedCategory === "all") || ((event.category || '').toLowerCase() === selectedCategory.toLowerCase());
        return matchesSearch && matchesCategory;
    });

    filteredEvents.sort((a, b) => {
        if (sortValue === "date-asc") return new Date(a.date || a.event_date) - new Date(b.date || b.event_date);
        if (sortValue === "date-desc") return new Date(b.date || b.event_date) - new Date(a.date || a.event_date);
        if (sortValue === "title-asc") return (a.title || '').localeCompare(b.title || '');
        if (sortValue === "title-desc") return (b.title || '').localeCompare(a.title || '');
        return 0;
    });

    currentPage = 1;
    renderCurrentPage();
}

function renderCurrentPage() {
    const eventsContainer = document.getElementById("eventsContainer");
    const resultsCount = document.getElementById("resultsCount");
    if (!eventsContainer) return;

    if (resultsCount) {
        resultsCount.textContent = `Showing ${filteredEvents.length} event(s) found`;
    }

    if (filteredEvents.length === 0) {
        eventsContainer.innerHTML = '<div style="text-align:center; padding:30px; color:#64748b;"><h3>No matching events found</h3><p>Try clearing your search keyword.</p></div>';
        renderPagination(0);
        return;
    }

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedItems = filteredEvents.slice(startIndex, endIndex);

    eventsContainer.innerHTML = paginatedItems.map(event => {
        return `
            <article class="event-card">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <h3 style="margin:0;">${escapeHtml(event.title)}</h3>
                        <span class="badge badge-info">${escapeHtml(event.category)}</span>
                    </div>
                    <div style="font-size:13px; color:#64748b; line-height:1.6; margin-bottom:10px;">
                        <p style="margin:2px 0;"><strong>Date:</strong> ${escapeHtml(event.date || event.event_date)}</p>
                        <p style="margin:2px 0;"><strong>Venue:</strong> ${escapeHtml(event.venue || event.location || 'Campus')}</p>
                    </div>
                    <p style="font-size:14px; margin-bottom:15px;">${escapeHtml(event.description || '')}</p>
                </div>
                <div>
                    <a href="register.html" class="btn" style="width:100%; text-align:center; box-sizing:border-box;">Register for Event</a>
                </div>
            </article>
        `;
    }).join("");

    renderPagination(filteredEvents.length);
}

function renderPagination(totalItems) {
    const paginationContainer = document.getElementById("paginationContainer");
    if (!paginationContainer) return;

    const totalPages = Math.ceil(totalItems / itemsPerPage);
    if (totalPages <= 1) {
        paginationContainer.innerHTML = "";
        return;
    }

    let paginationHTML = `<button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;
    for (let i = 1; i <= totalPages; i++) {
        paginationHTML += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
    }
    paginationHTML += `<button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;

    paginationContainer.innerHTML = paginationHTML;
}

window.changePage = function(newPage) {
    const totalPages = Math.ceil(filteredEvents.length / itemsPerPage);
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        renderCurrentPage();
        window.scrollTo({ top: 150, behavior: "smooth" });
    }
};

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

let generatedCaptchaCode = "";

function generateCaptcha() {
    const canvas = document.getElementById("captchaCanvas");
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
    generatedCaptchaCode = "";
    for (let i = 0; i < 5; i++) {
        generatedCaptchaCode += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "#e2e8f0";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    for (let i = 0; i < 4; i++) {
        ctx.strokeStyle = "#94a3b8";
        ctx.beginPath();
        ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.stroke();
    }

    ctx.font = "bold 20px monospace";
    ctx.fillStyle = "#1e3a8a";
    for (let i = 0; i < generatedCaptchaCode.length; i++) {
        ctx.fillText(generatedCaptchaCode[i], 15 + (i * 20), 28);
    }
}

function checkPasswordStrength(password) {
    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    const bar = document.getElementById("pwdStrengthBar");
    const text = document.getElementById("pwdStrengthText");
    if (!bar || !text) return;

    if (score <= 1) {
        bar.style.width = "25%";
        bar.style.backgroundColor = "#ef4444";
        text.textContent = "Weak";
        text.style.color = "#ef4444";
    } else if (score === 2 || score === 3) {
        bar.style.width = "65%";
        bar.style.backgroundColor = "#f59e0b";
        text.textContent = "Moderate";
        text.style.color = "#f59e0b";
    } else {
        bar.style.width = "100%";
        bar.style.backgroundColor = "#10b981";
        text.textContent = "Strong";
        text.style.color = "#10b981";
    }
}

document.addEventListener("DOMContentLoaded", function() {
    fetchEventsData();

    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortBy = document.getElementById("sortBy");
    const resetBtn = document.getElementById("resetBtn");

    if (searchInput) searchInput.addEventListener("input", applySearchAndFilter);
    if (categoryFilter) categoryFilter.addEventListener("change", applySearchAndFilter);
    if (sortBy) sortBy.addEventListener("change", applySearchAndFilter);
    if (resetBtn) {
        resetBtn.addEventListener("click", function() {
            if (searchInput) searchInput.value = "";
            if (categoryFilter) categoryFilter.value = "all";
            if (sortBy) sortBy.value = "date-asc";
            applySearchAndFilter();
        });
    }

    document.querySelectorAll(".faq-question").forEach(button => {
        button.addEventListener("click", function() {
            const faqItem = this.parentElement;
            faqItem.classList.toggle("active");
            const icon = this.querySelector(".faq-icon");
            if (icon) {
                icon.textContent = faqItem.classList.contains("active") ? "-" : "+";
            }
        });
    });

    const closeBannerBtn = document.getElementById("closeBannerBtn");
    const noticeBanner = document.getElementById("noticeBanner");
    if (closeBannerBtn && noticeBanner) {
        closeBannerBtn.addEventListener("click", function() {
            noticeBanner.style.display = "none";
        });
    }

    const openModalBtn = document.getElementById("openModalBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const quickModal = document.getElementById("quickModal");

    if (openModalBtn && quickModal) {
        openModalBtn.addEventListener("click", function() {
            quickModal.style.display = "flex";
        });
    }
    if (closeModalBtn && quickModal) {
        closeModalBtn.addEventListener("click", function() {
            quickModal.style.display = "none";
        });
    }

    const pwdInput = document.getElementById("password");
    if (pwdInput) {
        pwdInput.addEventListener("input", function() {
            checkPasswordStrength(this.value);
        });
    }

    generateCaptcha();
    const refreshCaptchaBtn = document.getElementById("refreshCaptchaBtn");
    if (refreshCaptchaBtn) {
        refreshCaptchaBtn.addEventListener("click", generateCaptcha);
    }

    const regForm = document.getElementById("registrationForm");
    if (regForm) {
        regForm.addEventListener("submit", function(e) {
            const captchaInput = document.getElementById("captchaInput");
            if (captchaInput && captchaInput.value.toUpperCase() !== generatedCaptchaCode) {
                e.preventDefault();
                alert("Incorrect CAPTCHA code. Please enter the characters shown in the image.");
                generateCaptcha();
                captchaInput.focus();
                return false;
            }
        });
    }
});
