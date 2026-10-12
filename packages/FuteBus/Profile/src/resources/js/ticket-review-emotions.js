import { Alignment, Fit, Layout, Rive, RuntimeLoader } from '@rive-app/canvas';
import riveWasmUrl from '@rive-app/canvas/rive.wasm?url';
import { emotionCropFrame } from './ticket-review-emotion-layout.js';

const machineName = 'State Machine 1';
const stateInput = 'Rate';
const canvasStates = new WeakMap();
const mounted = new Map();
let activeCanvas = null;
let observer;

RuntimeLoader.setWasmUrl(riveWasmUrl);
RuntimeLoader.setWasmFallbackUrl(null);

function ratingFor(canvas) {
    const rating = Number(canvas.dataset.reviewRive);
    return Number.isInteger(rating) && rating >= 1 && rating <= 5 ? rating : null;
}

function setLayout(rive, canvas, rating) {
    rive.resizeDrawingSurfaceToCanvas();
    const frame = emotionCropFrame(rating, canvas.width, canvas.height);
    rive.layout = new Layout({ fit: Fit.Fill, alignment: Alignment.TopLeft, ...frame });
}

function destroyCanvas(canvas) {
    const state = canvasStates.get(canvas);
    if (!state) return;
    state.token++;
    window.clearTimeout(state.timer);
    state.rive?.cleanup();
    canvasStates.delete(canvas);
    if (activeCanvas === canvas) activeCanvas = null;
    canvas.parentElement?.removeAttribute('data-rive-ready');
}

function clearPrevious(canvas) {
    if (!activeCanvas || activeCanvas === canvas) return;
    destroyCanvas(activeCanvas);
}

function animateEmotion(canvas, rating, duration = 700) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const expectedRating = ratingFor(canvas);
    if (!expectedRating || rating !== expectedRating) return;
    clearPrevious(canvas);
    activeCanvas = canvas;
    let state = canvasStates.get(canvas);
    if (!state) {
        state = { rive: null, input: null, token: 0, timer: null };
        canvasStates.set(canvas, state);
        const token = ++state.token;
        const source = canvas.dataset.reviewRiveSrc;
        if (!source) return destroyCanvas(canvas);
        const rive = new Rive({
            src: source,
            canvas,
            artboard: 'Artboard',
            stateMachine: machineName,
            autoplay: false,
            autoBind: false,
            enableRiveAssetCDN: false,
            shouldDisableRiveListeners: true,
            onLoad() {
                if (!canvas.isConnected || canvasStates.get(canvas) !== state || state.token !== token) {
                    rive.cleanup();
                    return;
                }
                const input = rive.stateMachineInputs(machineName)?.find((item) => item.name === stateInput && item.type === 56);
                if (!input) return destroyCanvas(canvas);
                state.rive = rive;
                state.input = input;
                setLayout(rive, canvas, expectedRating);
                canvas.parentElement?.setAttribute('data-rive-ready', 'true');
                input.value = rating;
                rive.play(machineName);
            },
            onLoadError() { destroyCanvas(canvas); },
        });
        state.rive = rive;
    } else if (state.rive && state.input) {
        state.token++;
        state.input.value = rating;
        state.rive.play(machineName);
    }
    window.clearTimeout(state.timer);
    const token = state.token;
    state.timer = window.setTimeout(() => {
        if (canvasStates.get(canvas) === state && state.token === token) destroyCanvas(canvas);
    }, duration);
}

function buttonCanvas(button) {
    return button.querySelector('[data-review-rive]');
}

document.addEventListener('pointerover', (event) => {
    if (event.pointerType === 'touch' || !(event.target instanceof Element)) return;
    const button = event.target.closest('[data-review-rating]');
    if (button && !button.contains(event.relatedTarget)) animateEmotion(buttonCanvas(button), Number(button.dataset.reviewRating));
});

document.addEventListener('pointerout', (event) => {
    if (!(event.target instanceof Element)) return;
    const button = event.target.closest('[data-review-rating]');
    if (!button || button.contains(event.relatedTarget)) return;
    const selected = Number(button.closest('[data-history-review]')?.elements.rating.value);
    const canvas = buttonCanvas(button);
    if (selected === Number(button.dataset.reviewRating)) animateEmotion(canvas, selected, 220);
    else if (canvas) destroyCanvas(canvas);
});

document.addEventListener('click', (event) => {
    const button = event.target instanceof Element ? event.target.closest('[data-review-rating]') : null;
    if (!button) return;
    const rating = Number(button.closest('[data-history-review]')?.elements.rating.value);
    const canvas = buttonCanvas(button);
    if (rating === Number(button.dataset.reviewRating)) animateEmotion(canvas, rating, 1100);
    else if (canvas) destroyCanvas(canvas);
});

document.addEventListener('focusin', (event) => {
    const button = event.target instanceof Element ? event.target.closest('[data-review-rating]') : null;
    if (button) animateEmotion(buttonCanvas(button), Number(button.dataset.reviewRating));
});

document.addEventListener('focusout', (event) => {
    const button = event.target instanceof Element ? event.target.closest('[data-review-rating]') : null;
    if (button && !button.contains(event.relatedTarget)) {
        const canvas = buttonCanvas(button);
        if (canvas) window.setTimeout(() => { if (!button.matches(':hover')) destroyCanvas(canvas); }, 0);
    }
});

function refreshCanvases() {
    document.querySelectorAll('[data-review-rive-autoplay="true"]').forEach((canvas) => {
        if (!mounted.has(canvas) && canvas.isConnected) {
            mounted.set(canvas, true);
            animateEmotion(canvas, ratingFor(canvas), 1500);
        }
    });
    for (const canvas of mounted.keys()) {
        if (!canvas.isConnected) {
            destroyCanvas(canvas);
            mounted.delete(canvas);
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', refreshCanvases, { once: true });
} else refreshCanvases();

observer = new MutationObserver(refreshCanvases);
observer.observe(document.body, { childList: true, subtree: true });
window.addEventListener('pagehide', () => {
    observer.disconnect();
    [...mounted.keys()].forEach(destroyCanvas);
    mounted.clear();
    if (activeCanvas) destroyCanvas(activeCanvas);
});
