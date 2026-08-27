import { ref } from 'vue';

const STORAGE_KEY = 'go-pharmacy-customer-theme';

const theme = ref(
    localStorage.getItem(STORAGE_KEY) || 'system',
);

const getSystemTheme = () => {
    if (typeof window === 'undefined') {
        return 'light';
    }

    return window.matchMedia(
        '(prefers-color-scheme: dark)',
    ).matches
        ? 'dark'
        : 'light';
};

const applyTheme = (selectedTheme) => {
    if (typeof document === 'undefined') {
        return;
    }

    const html = document.documentElement;

    const activeTheme =
        selectedTheme === 'system'
            ? getSystemTheme()
            : selectedTheme;

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER THEME ONLY
    |--------------------------------------------------------------------------
    |
    | Customer uses:
    |     html.dark
    |
    | Admin uses:
    |     html.admin-dark
    |
    */

    html.classList.toggle(
        'dark',
        activeTheme === 'dark',
    );

    html.style.colorScheme = activeTheme;
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

    const mediaQuery = window.matchMedia(
        '(prefers-color-scheme: dark)',
    );

    mediaQuery.addEventListener('change', () => {
        if (theme.value === 'system') {
            applyTheme('system');
        }
    });
}

export function useCustomerTheme() {
    return {
        theme,
        setTheme,
    };
}