import { useEffect, useState } from 'react';

const MOBILE_BREAKPOINT = 768;

export function useIsMobile() {
    const [isMobile, setIsMobile] = useState<boolean>(() => (typeof window !== 'undefined' ? window.innerWidth < MOBILE_BREAKPOINT : false));

    useEffect(() => {
        const mql = window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`);

        const onChange = () => {
            setIsMobile(window.innerWidth < MOBILE_BREAKPOINT);
        };

        mql.addEventListener('change', onChange);

        return () => mql.removeEventListener('change', onChange);
    }, []);

    return isMobile;
}

const DESKTOP_BREAKPOINT = 1024;

const isTabletWidth = () => typeof window !== 'undefined' && window.innerWidth >= MOBILE_BREAKPOINT && window.innerWidth < DESKTOP_BREAKPOINT;

/** True between the mobile and desktop breakpoints, where the navigation drawer shows as a rail. */
export function useIsTablet() {
    const [isTablet, setIsTablet] = useState<boolean>(isTabletWidth);

    useEffect(() => {
        const onResize = () => setIsTablet(isTabletWidth());
        window.addEventListener('resize', onResize);
        return () => window.removeEventListener('resize', onResize);
    }, []);

    return isTablet;
}
