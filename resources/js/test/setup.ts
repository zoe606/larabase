import '@testing-library/jest-dom/vitest';

// Node >= 25 defines global localStorage/sessionStorage getters that return undefined
// without --localstorage-file, shadowing jsdom's. Re-expose jsdom's storage.
const { jsdom } = globalThis as unknown as { jsdom?: { window: Window } };
if (jsdom) {
    for (const key of ['localStorage', 'sessionStorage'] as const) {
        if (typeof globalThis[key] === 'undefined') {
            Object.defineProperty(globalThis, key, { configurable: true, value: jsdom.window[key] });
        }
    }
}
