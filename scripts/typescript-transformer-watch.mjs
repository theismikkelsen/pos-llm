import { spawn } from 'node:child_process';
import { watch } from 'node:fs';
import { resolve } from 'node:path';

const debounceMs = Number(process.env.TS_TRANSFORM_DEBOUNCE_MS ?? 250);
const watchTargets = [
    resolve('app'),
    resolve('config/typescript-transformer.php'),
];

let debounceTimer = null;
let isRunning = false;
let isPending = false;

const runTransform = () => {
    if (isRunning) {
        isPending = true;
        return;
    }

    isRunning = true;

    const child = spawn('php', ['artisan', 'typescript:transform'], {
        stdio: 'inherit',
        shell: true,
    });

    child.on('exit', () => {
        isRunning = false;

        if (isPending) {
            isPending = false;
            runTransform();
        }
    });
};

const scheduleTransform = () => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(runTransform, debounceMs);
};

watchTargets.forEach((target) => {
    try {
        watch(
            target,
            { recursive: !target.endsWith('.php') },
            (eventType, filename) => {
                if (!filename || !filename.endsWith('.php')) {
                    return;
                }

                scheduleTransform();
            },
        );
    } catch (error) {
        console.error(`Failed to watch ${target}:`, error);
        process.exit(1);
    }
});

console.log('TypeScript transformer watch started.');
runTransform();
