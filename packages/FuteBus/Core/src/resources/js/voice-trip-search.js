import { parseVoiceTripSearch } from './voice-trip-search-parser';

const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
let recognition = null;
let listening = false;

const emit = (name, detail = {}) => window.dispatchEvent(new CustomEvent(`hero-voice-${name}`, { detail }));

const stop = () => {
    if (recognition && listening) recognition.stop();
};

const start = () => {
    if (!Recognition) {
        emit('error', { code: 'unsupported' });
        return;
    }
    if (listening) {
        stop();
        return;
    }

    recognition = new Recognition();
    recognition.lang = document.documentElement.lang === 'en' ? 'en-US' : 'vi-VN';
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.maxAlternatives = 1;
    recognition.onstart = () => {
        listening = true;
        emit('state', { listening: true });
    };
    recognition.onresult = (event) => {
        const transcript = Array.from(event.results, (result) => result[0].transcript).join(' ').trim();
        const final = Array.from(event.results).every((result) => result.isFinal);
        emit('result', { transcript, final });
    };
    recognition.onerror = (event) => emit('error', { code: event.error });
    recognition.onend = () => {
        listening = false;
        recognition = null;
        emit('state', { listening: false });
    };

    try {
        recognition.start();
    } catch {
        emit('error', { code: 'unavailable' });
        recognition = null;
    }
};

window.FutaVoiceSearch = {
    supported: Boolean(Recognition),
    start,
    stop,
    parse: parseVoiceTripSearch,
};
