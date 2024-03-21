const themeStitcher = document.getElementById("themingSwitcher");

// Function to toggle theme
const toggleTheme = (isChecked) => {
    const theme = isChecked ? "dark" : "light";
    document.documentElement.dataset.mdbTheme = theme;
    localStorage.setItem("theme", theme); // Set the theme in localStorage
};

const getStoredTheme = localStorage.getItem("theme");

if (themeStitcher) {
    // Add listener to theme toggler
    themeStitcher.addEventListener("change", (e) => {
        toggleTheme(e.target.checked);
    });

    // Check and apply stored theme
    if (getStoredTheme === 'dark') {
        toggleTheme(true);
        themeStitcher.checked = true;
    }
} else if (getStoredTheme === 'dark') {
    // Apply stored theme if no themeStitcher is found
    toggleTheme(true);
}
