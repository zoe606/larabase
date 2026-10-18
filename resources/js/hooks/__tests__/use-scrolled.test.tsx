import { useScrolled } from '@/hooks/use-scrolled';
import { act, renderHook } from '@testing-library/react';
import { afterEach, describe, expect, it } from 'vitest';

function scrollTo(y: number) {
    Object.defineProperty(window, 'scrollY', { configurable: true, value: y });
    window.dispatchEvent(new Event('scroll'));
}

afterEach(() => {
    Object.defineProperty(window, 'scrollY', { configurable: true, value: 0 });
});

describe('useScrolled', () => {
    it('is false at the top of the page', () => {
        const { result } = renderHook(() => useScrolled());
        expect(result.current).toBe(false);
    });

    it('turns true once the window scrolls and false when back at the top', () => {
        const { result } = renderHook(() => useScrolled());
        act(() => scrollTo(24));
        expect(result.current).toBe(true);
        act(() => scrollTo(0));
        expect(result.current).toBe(false);
    });

    it('respects a threshold', () => {
        const { result } = renderHook(() => useScrolled(40));
        act(() => scrollTo(24));
        expect(result.current).toBe(false);
        act(() => scrollTo(41));
        expect(result.current).toBe(true);
    });
});
