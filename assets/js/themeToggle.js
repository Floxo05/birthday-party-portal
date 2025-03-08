document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("theme-toggle");
    const htmlElement = document.documentElement;

    if (!toggleBtn) return; // Falls der Button nicht existiert

    // Prüfen, ob der Nutzer eine Einstellung gespeichert hat
    if (localStorage.getItem("theme") === "dark") {
        htmlElement.setAttribute("data-theme", "dark");
        toggleBtn.innerText = "☀ Light Mode";
    }

    toggleBtn.addEventListener("click", () => {
        if (htmlElement.getAttribute("data-theme") === "dark") {
            htmlElement.setAttribute("data-theme", "light");
            localStorage.setItem("theme", "light");
            toggleBtn.innerText = "🌙 Dark Mode";
        } else {
            htmlElement.setAttribute("data-theme", "dark");
            localStorage.setItem("theme", "dark");
            toggleBtn.innerText = "☀ Light Mode";
        }
    });
});
