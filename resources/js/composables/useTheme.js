import { ref, watch } from 'vue';

const isDark = ref(localStorage.getItem('saving-challenge-theme') !== 'light');

function applyTheme() {
    document.documentElement.classList.toggle('light', !isDark.value);
    localStorage.setItem('saving-challenge-theme', isDark.value ? 'dark' : 'light');
}

applyTheme();
watch(isDark, applyTheme);

export function useTheme() {
    function toggleTheme() {
        isDark.value = !isDark.value;
    }

    return { isDark, toggleTheme };
}
