const normalize = (value) => String(value || '')
    .toLocaleLowerCase('vi')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/[^a-z0-9/\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const normalizePlace = (value) => normalize(value)
    .replace(/\b(?:tp|thanh pho|tinh)\s+/g, '')
    .replace(/\b(?:sai gon|saigon|tp hcm|hcm)\b/g, 'ho chi minh');

const isoDate = (date) => [
    date.getFullYear(),
    String(date.getMonth() + 1).padStart(2, '0'),
    String(date.getDate()).padStart(2, '0'),
].join('-');

const addDays = (date, days) => {
    const result = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    result.setDate(result.getDate() + days);
    return isoDate(result);
};

const catalogNames = (side) => [
    ...(side?.provinces || []),
    ...(side?.areas || []).map((area) => area.name),
    ...(side?.areas || []).flatMap((area) => (area.offices || []).map((office) => office.name)),
    ...(side?.directory_offices || []).map((office) => office.name),
];

const matchLocation = (spoken, side) => {
    const query = normalizePlace(spoken);
    if (!query) return null;
    const candidates = [...new Set(catalogNames(side))]
        .map((name) => ({ name, normalized: normalizePlace(name) }))
        .filter((candidate) => candidate.normalized.length >= 3);

    const exact = candidates.find((candidate) => candidate.normalized === query);
    if (exact) return exact.name;
    const contained = candidates
        .filter((candidate) => query.includes(candidate.normalized))
        .sort((a, b) => b.normalized.length - a.normalized.length)[0];
    return contained?.name || null;
};

const orderedLocations = (spoken, catalog) => {
    const query = normalizePlace(spoken);
    const find = (side, after = 0) => [...new Set(catalogNames(side))]
        .map((name) => ({ name, text: normalizePlace(name) }))
        .filter(({ text }) => text.length >= 3)
        .flatMap(({ name, text }) => {
            const index = query.indexOf(text, after);
            return index < 0 ? [] : [{ name, index, end: index + text.length, length: text.length }];
        })
        .sort((a, b) => a.index - b.index || b.length - a.length);
    const departure = find(catalog?.departure)[0];
    const destination = departure && find(catalog?.destination, departure.end)
        .find((item) => item.name !== departure.name);
    return [departure?.name || null, destination?.name || null];
};

const spokenDate = (day, month, year, today) => {
    const currentYear = today.getFullYear();
    const selectedYear = year ? Number(year) < 100 ? 2000 + Number(year) : Number(year) : currentYear;
    let date = new Date(selectedYear, Number(month) - 1, Number(day));
    if (date.getFullYear() !== selectedYear || date.getMonth() !== Number(month) - 1 || date.getDate() !== Number(day)) return null;
    if (!year && isoDate(date) < isoDate(today)) date = new Date(selectedYear + 1, Number(month) - 1, Number(day));
    return isoDate(date);
};

const numberWords = { khong: 0, mot: 1, hai: 2, ba: 3, bon: 4, tu: 4, nam: 5, lam: 5, sau: 6, bay: 7, tam: 8, chin: 9, muoi: 10 };

const spokenNumber = (value) => {
    if (/^\d+$/.test(value)) return Number(value);
    const words = value.trim().split(' ');
    if (words[0] === 'mong' || words[0] === 'mung') words.shift();
    if (words[0] === 'hai' || words[0] === 'ba') {
        if (words[1] === 'muoi') return numberWords[words[0]] * 10 + (numberWords[words[2]] || 0);
    }
    if (words[0] === 'muoi') return 10 + (numberWords[words[1]] || 0);
    return words.length === 1 ? numberWords[words[0]] ?? null : null;
};

const numberPhrase = '(?:\\d{1,2}|(?:mong|mung)\\s+(?:mot|hai|ba|bon|tu|nam|lam|sau|bay|tam|chin)|(?:hai|ba)\\s+muoi(?:\\s+(?:mot|hai|ba|bon|tu|nam|lam|sau|bay|tam|chin))?|muoi(?:\\s+(?:mot|hai|ba|bon|tu|nam|lam|sau|bay|tam|chin))?|mot|hai|ba|bon|tu|nam|lam|sau|bay|tam|chin)';

const findDates = (spoken, today) => {
    const matches = [];
    const occupied = [];
    const add = (index, end, date) => {
        if (occupied.some(([start, finish]) => index < finish && end > start)) return;
        occupied.push([index, end]);
        matches.push({ index, date });
    };
    const pattern = new RegExp(`\\b(?:ngay\\s+)?(${numberPhrase})\\s*(?:\\/|\\-|thang\\s+)(${numberPhrase})(?:\\s*(?:\\/|\\-|nam\\s+)(\\d{2,4}))?\\b`, 'g');
    for (const match of spoken.matchAll(pattern)) {
        add(match.index, match.index + match[0].length, spokenDate(spokenNumber(match[1]), spokenNumber(match[2]), match[3], today));
    }
    for (const match of spoken.matchAll(/\b(hom nay|ngay mai|mai|ngay kia|ngay mot|today|tomorrow)\b/g)) {
        const days = ['ngay mai', 'mai', 'tomorrow'].includes(match[1]) ? 1 : ['ngay kia', 'ngay mot'].includes(match[1]) ? 2 : 0;
        add(match.index, match.index + match[0].length, addDays(today, days));
    }
    for (const match of spoken.matchAll(/\b(?:ngay\s+)?(thu\s+(?:2|3|4|5|6|7|hai|ba|tu|nam|sau|bay)|chu nhat)(?:\s+(tuan sau|tuan toi|tuan nay))?\b/g)) {
        const weekday = match[1] === 'chu nhat' ? 0 : spokenNumber(match[1].slice(4));
        const target = weekday === 0 ? 6 : weekday - 2;
        const current = (today.getDay() + 6) % 7;
        let offset = (target - current + 7) % 7;
        if (match[2] === 'tuan sau' || match[2] === 'tuan toi') offset += offset === 0 ? 7 : target <= current ? 0 : 7;
        add(match.index, match.index + match[0].length, addDays(today, offset));
    }
    const dayOnly = new RegExp(`\\bngay\\s+(${numberPhrase})\\b`, 'g');
    for (const match of spoken.matchAll(dayOnly)) {
        const day = spokenNumber(match[1]);
        let month = today.getMonth() + 1;
        let year = today.getFullYear();
        if (day < today.getDate()) {
            month += 1;
            if (month === 13) { month = 1; year += 1; }
        }
        add(match.index, match.index + match[0].length, spokenDate(day, month, year, today));
    }
    return matches.sort((a, b) => a.index - b.index).map((match) => match.date);
};

export function parseVoiceTripSearch(transcript, catalog, todayIso) {
    const spoken = normalize(transcript);
    const today = new Date(`${todayIso}T12:00:00`);
    const direction = spoken.match(/(?:^|\s)(?:di|den|toi|to|ve)(?:\s|$)/);
    const left = direction ? spoken.slice(0, direction.index).replace(/^(?:tim\s+|search\s+)?(?:chuyen\s+|trip\s+)?(?:xe\s+)?(?:tu\s+|from\s+)?/, '').trim() : '';
    const right = direction ? spoken.slice(direction.index + direction[0].length).trim() : '';
    let departure = matchLocation(left, catalog?.departure);
    let destination = matchLocation(right, catalog?.destination);
    if (!departure || !destination) {
        [departure, destination] = orderedLocations(spoken, catalog);
    }
    const dates = findDates(spoken, today);
    const roundTrip = /\b(?:khu hoi|hai chieu|ve ngay|ngay ve|round trip|return on)\b/.test(spoken);
    const count = '(\\d+|khong|mot|hai|ba|bon|tu|nam|lam|sau|bay|tam|chin|muoi|one|two|three|four|five|six|seven|eight|nine|ten)';
    const quantityMatch = spoken.match(new RegExp(`\\b${count}\\s*(?:ve|nguoi|ghe|tickets|seats|people)\\b`))
        || spoken.match(new RegExp(`\\b(?:so\\s+luong\\s+ve|so\\s+ve)\\s*(?:la\\s+)?${count}\\b`))
        || spoken.match(new RegExp(`\\bve\\s+la\\s+${count}\\b`));
    const englishNumbers = { one: 1, two: 2, three: 3, four: 4, five: 5, six: 6, seven: 7, eight: 8, nine: 9, ten: 10 };
    const quantity = quantityMatch ? (spokenNumber(quantityMatch[1]) ?? englishNumbers[quantityMatch[1]] ?? null) : null;
    const departureDate = dates[0] || null;
    const returnDate = roundTrip ? dates[1] || null : null;

    let error = null;
    if (!departure || !destination || departure === destination) error = 'location';
    else if (dates.includes(null) || (!departureDate && /\b(?:ngay|thang|thu\s+[2-7]|chu nhat|tuan sau|tuan toi|tomorrow|today)\b/.test(spoken)) || (departureDate && departureDate < todayIso)
        || (returnDate && departureDate && returnDate < departureDate)) error = 'date';
    else if (roundTrip && !returnDate) error = 'return_date';
    else if (quantity !== null && (quantity < 1 || quantity > 5)) error = 'quantity';

    return { departure, destination, departureDate, returnDate, roundTrip, quantity, error };
}
