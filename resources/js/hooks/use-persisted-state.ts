import { useCallback, useEffect, useState } from 'react';
import type { Dispatch, SetStateAction } from 'react';

function readStorage<T>(key: string, fallback: T): T {
    if (typeof window === 'undefined') {
        return fallback;
    }
    try {
        const raw = window.localStorage.getItem(key);
        return raw === null ? fallback : (JSON.parse(raw) as T);
    } catch {
        return fallback;
    }
}

function writeStorage<T>(key: string, value: T): void {
    if (typeof window === 'undefined') {
        return;
    }
    try {
        window.localStorage.setItem(key, JSON.stringify(value));
    } catch {
        // Storage full or disabled — fail silently.
    }
}

export function usePersistedState<T>(
    key: string,
    fallback: T,
): readonly [T, Dispatch<SetStateAction<T>>] {
    const [value, setInnerValue] = useState<T>(() => readStorage(key, fallback));

    useEffect(() => {
        writeStorage(key, value);
    }, [key, value]);

    const setValue = useCallback<Dispatch<SetStateAction<T>>>((next) => {
        setInnerValue(next);
    }, []);

    return [value, setValue] as const;
}
