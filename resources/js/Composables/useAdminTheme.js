import { ref } from 'vue';

const STORAGE_KEY = 'go-pharmacy-admin-theme';

const theme = ref(
    localStorage.getItem(STORAGE_KEY) || 'light',
);

const applyTheme = (selectedTheme) => {
    if (typeof document === 'undefined') {
        return;
    }

    const html = document.documentElement;

    /*
    |--------------------------------------------------------------------------
    | ADMIN THEME ONLY
    |--------------------------------------------------------------------------
    |
    | Admin uses:
    |     html.admin-dark
    |
    | Customer uses:
    |     html.dark
    |
    | These are completely independent.
    |
    */

    html.classList.toggle(
        'admin-dark',
        selectedTheme === 'dark',
    );
};

const setTheme = (selectedTheme) => {
    theme.value = selectedTheme;

    localStorage.setItem(
        STORAGE_KEY,
        selectedTheme,
    );

    applyTheme(selectedTheme);
};

if (typeof window !== 'undefined') {
    applyTheme(theme.value);
}

export function useAdminTheme() {
    return {
        theme,
        setTheme,
    };
}