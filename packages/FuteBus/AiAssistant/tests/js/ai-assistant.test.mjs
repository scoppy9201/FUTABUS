import test from "node:test";
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import vm from "node:vm";
import {
    phoneCountries,
    parseCustomerPhone,
} from "../../src/resources/js/phone.js";

const source = readFileSync(
    new URL("../../src/resources/js/app.js", import.meta.url),
    "utf8",
).replace(/^import\b[\s\S]*?;\s*/gm, "");

function setup({
    valid = true,
    ok = true,
    accepted = false,
    collectCustomer = true,
} = {}) {
    const requests = [],
        navigations = [],
        confirmations = [];
    const window = {
        location: {
            origin: "https://futa.test",
            assign: (url) => navigations.push(url),
        },
        FutaConfirm: async (options) => {
            confirmations.push(options);
            return accepted;
        },
    };
    vm.runInNewContext(source, {
        countryFlag: (code) => `flag-${code}`,
        createPopup: () => ({}),
        phoneCountries,
        parseCustomerPhone,
        window,
        document: {
            documentElement: { lang: "en" },
            querySelectorAll: () => [],
        },
        Intl,
        URL,
        fetch: async (url, options) => {
            requests.push(JSON.parse(options.body));
            return {
                ok,
                json: async () =>
                    ok
                        ? {
                              session_id: "session-1",
                              message: "How can we help?",
                          }
                        : { message: "Please check your details" },
            };
        },
    });
    const chat = window.futaAiChat({
        endpoint: "/ask",
        csrf: "test",
        collectCustomer,
        copy: {
            greeting: "Welcome",
            topic_booking: "Book tickets",
            confirm_title: "Continue?",
            confirm_continue: "Confirm",
            booking_confirmation:
                "Review seats and final price on the booking page",
            required_field: "Required",
            phone_incomplete: "Incomplete phone",
            phone_invalid: "Invalid phone",
            email_invalid: "Invalid email",
        },
    });
    chat.$refs = {
        email: { checkValidity: () => true },
        intake: { checkValidity: () => valid, querySelector: () => null },
        messages: { scrollTop: 0, scrollHeight: 100 },
    };
    chat.$nextTick = (callback) => callback();
    return { chat, requests, navigations, confirmations };
}
const customer = {
    salutation: "ms",
    name: " Customer ",
    email: "customer@example.test",
    phone: "0912345678",
    topic: "booking",
    content: "Book tickets",
};

test("opening support and entering details does not send a request before confirmation", async () => {
    const { chat, requests } = setup();
    chat.open = true;
    chat.customer = { ...customer };
    chat.draft = "Hello";
    await chat.send();
    assert.equal(chat.started, false);
    assert.equal(requests.length, 0);
});

test("invalid intake stays on the form without a request", async () => {
    const { chat, requests } = setup({ valid: false });
    chat.customer = { ...customer };
    await chat.startChat();
    assert.equal(chat.started, false);
    assert.equal(requests.length, 0);
});

test("confirming intake sends normalized details and retains session across questions", async () => {
    const { chat, requests, navigations } = setup();
    chat.customer = { ...customer };
    await chat.startChat();
    assert.equal(chat.started, true);
    assert.equal(requests[0].customer.name, "Customer");
    assert.equal(requests[0].customer.phone, "+84912345678");
    assert.equal(requests[0].message, "Book tickets");
    chat.draft = "Tomorrow";
    await chat.send();
    assert.equal(requests[1].session_id, "session-1");
    assert.equal(navigations.length, 0);
});

test("failed intake request restores the form, preserves details and allows retry", async () => {
    const { chat, requests } = setup({ ok: false });
    chat.customer = { ...customer };
    await chat.startChat();
    assert.equal(chat.started, false);
    assert.equal(chat.loading, false);
    assert.equal(chat.customer.phone, customer.phone);
    await chat.startChat();
    assert.equal(requests.length, 2);
});

test("receiving an action does not navigate, and cancelling confirmation keeps the customer on screen", async () => {
    const { chat, navigations, confirmations } = setup();
    chat.messages.push({
        pending_action: { url: "https://futa.test/tickets" },
    });
    assert.equal(navigations.length, 0);
    await chat.confirmNavigation({
        url: "https://futa.test/tickets",
        description: "Review your ticket",
    });
    assert.equal(confirmations.length, 1);
    assert.equal(navigations.length, 0);
});

test("trip selection displays a summary and navigates only after explicit confirmation", async () => {
    const { chat, navigations, confirmations } = setup({ accepted: true });
    await chat.confirmTrip({
        url: "https://futa.test/trips/1",
        origin: "A",
        destination: "B",
        departure: "Tomorrow",
        type: "Limousine",
        price: 200000,
    });
    assert.match(confirmations[0].message, /A.*B/);
    assert.match(confirmations[0].message, /Tomorrow/);
    assert.match(confirmations[0].message, /200/);
    assert.deepEqual(navigations, ["https://futa.test/trips/1"]);
});

test("cancelling trip confirmation does not navigate", async () => {
    const { chat, navigations } = setup();
    await chat.confirmTrip({ url: "/trips/1", price: 200000 });
    assert.equal(navigations.length, 0);
});

test("external and script links cannot navigate even if confirmation would be accepted", async () => {
    const { chat, navigations, confirmations } = setup({ accepted: true });
    await chat.confirmNavigation({ url: "https://other.test/tickets" });
    await chat.confirmNavigation({ url: "javascript:alert(1)" });
    await chat.confirmNavigation({ url: "https://[" });
    assert.equal(navigations.length, 0);
    assert.equal(confirmations.length, 0);
});

test("staff support does not collect customer contact details", async () => {
    const { chat, requests } = setup({ collectCustomer: false });
    chat.draft = "Show revenue";
    await chat.send();
    assert.equal(chat.started, true);
    assert.equal("customer" in requests[0], false);
});

for (const [country, local, expected] of [
    ["VN", "0912345678", "+84912345678"],
    ["VN", "912345678", "+84912345678"],
    ["US", "202 555-0123", "+12025550123"],
    ["SG", "81234567", "+6581234567"],
    ["GB", "07400 123456", "+447400123456"],
    ["IT", "02 36618 300", "+390236618300"],
]) {
    test(`country selection submits ${local} as ${expected}`, async () => {
        const { chat, requests } = setup();
        chat.customer = { ...customer, phone: local };
        chat.phoneCountry = country;
        chat.formatPhone();
        await chat.startChat();
        assert.equal(requests[0].customer.phone, expected);
        assert.equal(requests[0].customer.phone_country, country);
    });
}

for (const [country, phone] of [
    ["VN", "123"],
    ["VN", "09123456789"],
    ["US", "abcdefghi"],
    ["US", "1234567890123456"],
    ["US", "1234567890"],
    ["SG", "12345678"],
    ["VN", "+12025550123"],
    ["CA", "+12025550123"],
    ["US", "+12025550123 ext. 4"],
]) {
    test(`invalid phone ${phone} for ${country} does not start support`, async () => {
        const { chat, requests } = setup();
        chat.customer = { ...customer, phone };
        chat.phoneCountry = country;
        await chat.startChat();
        assert.equal(chat.started, false);
        assert.equal(requests.length, 0);
        assert.equal(
            chat.errors.phone,
            phone.replace(/\D/g, "").length < 7
                ? "Incomplete phone"
                : "Invalid phone",
        );
    });
}

test("email is optional and required fields show inline errors before a request", async () => {
    const { chat, requests } = setup();
    chat.customer = { ...customer, name: "", email: "", content: "" };
    await chat.startChat();
    assert.equal(requests.length, 0);
    assert.deepEqual(Object.keys(chat.errors).sort(), ["content", "name"]);
    chat.customer = { ...customer, phone: "" };
    await chat.startChat();
    assert.equal(requests.length, 0);
    assert.equal(chat.errors.phone, "Incomplete phone");
    chat.customer = { ...customer, email: "" };
    await chat.startChat();
    assert.equal(requests.length, 1);
    assert.equal(requests[0].customer.email, "");
});

test("valid numbers are displayed in the national format on blur", () => {
    const { chat } = setup();
    chat.customer = { ...customer };
    chat.formatPhone();
    assert.equal(chat.customer.phone, "0912 345 678");
});

test("invalid email stays on the form with an inline error", async () => {
    const { chat, requests } = setup();
    chat.customer = { ...customer, email: "invalid" };
    chat.$refs.email.checkValidity = () => false;
    await chat.startChat();
    assert.equal(requests.length, 0);
    assert.equal(chat.errors.email, "Invalid email");
});

test("initialization tolerates pages without the legacy support link", () => {
    const { chat } = setup();
    assert.doesNotThrow(() => chat.init());
});

test("country search matches localized and English names, ISO codes and calling codes", () => {
    const { chat } = setup();
    for (const query of ["Vietnam", "VN", "+84"]) {
        chat.countrySearch = query;
        assert.ok(
            chat.filteredCountries.some((country) => country.code === "VN"),
        );
    }
    chat.countrySearch = "no-country-matches-this";
    assert.equal(chat.filteredCountries.length, 0);
    chat.countrySearch = "";
    assert.equal(chat.filteredCountries.length, chat.countries.length);
});

test("country selection updates the calling code and closes only the picker", () => {
    const { chat, requests } = setup();
    let closed = false;
    chat.open = true;
    chat.countryPopup = {
        close: () => {
            closed = true;
        },
    };
    chat.chooseCountry("US");
    assert.equal(chat.phoneCountry, "US");
    assert.equal(chat.selectedCountry.prefix, "+1");
    assert.equal(closed, true);
    assert.equal(chat.open, true);
    assert.equal(chat.started, false);
    assert.equal(requests.length, 0);
});

test("country picker keyboard focus wraps through results", () => {
    const { chat } = setup();
    const focused = [];
    chat.$refs.countryList = {
        querySelectorAll: () =>
            [0, 1, 2].map((index) => ({ focus: () => focused.push(index) })),
    };
    chat.focusCountry(-1);
    chat.focusCountry(3);
    assert.deepEqual(focused, [2, 0]);
});
