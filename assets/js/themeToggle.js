document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("theme-toggle");
    const htmlElement = document.documentElement;

    if (!toggleBtn) return;

    // 🎯 Theme aus den Cookies lesen
    function getCookie(name) {
        return document.cookie.split('; ').find(row => row.startsWith(name))?.split('=')[1];
    }

    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value}; expires=${expires.toUTCString()}; path=/`;
    }

    // Prüfen, ob ein Theme gespeichert ist
    const savedTheme = getCookie("theme") || "light";
    htmlElement.setAttribute("data-theme", savedTheme);
    toggleBtn.innerText = savedTheme === "dark" ? "☀ Light Mode" : "🌙 Dark Mode";

    toggleBtn.addEventListener("click", () => {
        const newTheme = htmlElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
        htmlElement.setAttribute("data-theme", newTheme);
        setCookie("theme", newTheme, 365); // Speichert Theme für 1 Jahr
        toggleBtn.innerText = newTheme === "dark" ? "☀ Light Mode" : "🌙 Dark Mode";
    });

    // 🎯 Erst nach dem Laden die Transition aktivieren!
    setTimeout(() => {
        document.body.classList.add("theme-transition");
    }, 100);
});
