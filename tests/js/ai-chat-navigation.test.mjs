import test from "node:test";
import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import vm from "node:vm";

const source = (
    await readFile(
        new URL(
            "../../packages/FuteBus/AiAssistant/src/resources/js/app.js",
            import.meta.url,
        ),
        "utf8",
    )
).replace(/^import\b[\s\S]*?;\s*/gm, "");

function setup() {
    const navigations = [];
    let decide;
    let confirmations = 0;
    const window = {
        location: {
            origin: "https://futabus.test",
            assign(url) {
                navigations.push(url);
            },
        },
        FutaConfirm() {
            confirmations += 1;
            return new Promise((resolve) => {
                decide = resolve;
            });
        },
    };
    vm.runInNewContext(source, {
        window,
        URL,
        document: { documentElement: { lang: "vi" } },
        phoneCountries: () => [],
        parseCustomerPhone: (value) =>
            value === "0912345678" ? { number: "+84912345678" } : null,
    });
    return {
        chat: window.futaAiChat({ copy: {} }),
        navigations,
        decide: (value) => decide(value),
        confirmations: () => confirmations,
    };
}

for (const confirmed of [true, false]) {
    test(`ticket lookup navigation waits for confirmation and respects ${confirmed ? "acceptance" : "cancellation"}`, async () => {
        const state = setup();
        const pending = state.chat.confirmNavigation({
            url: "https://futabus.test/tra-cuu-ve",
            description: "Tra cứu vé đã mua",
        });
        assert.equal(state.confirmations(), 1);
        assert.deepEqual(state.navigations, []);
        state.decide(confirmed);
        await pending;
        assert.deepEqual(
            state.navigations,
            confirmed ? ["https://futabus.test/tra-cuu-ve"] : [],
        );
    });
}

test("chat cannot send the user to an external site", async () => {
    const state = setup();
    await state.chat.confirmNavigation({
        url: "https://external.test/tra-cuu-ve",
    });
    assert.equal(state.confirmations(), 0);
    assert.deepEqual(state.navigations, []);
});

function intake() {
    const { chat } = setup();
    const messages = {
        required_field: "Required",
        phone_incomplete: "Incomplete",
        phone_invalid: "Invalid phone",
        email_invalid: "Invalid email",
    };
    Object.defineProperty(chat, "copy", { value: messages });
    chat.$refs = {
        email: { checkValidity: () => false },
        intake: { checkValidity: () => true, querySelector: () => null },
    };
    chat.$nextTick = (callback) => callback();
    chat.customer = {
        name: "Test",
        email: "",
        phone: "0912345678",
        topic: "booking",
        content: "Help",
    };
    return chat;
}

test("intake accepts an empty optional email with all required fields complete", () => {
    const chat = intake();
    assert.equal(chat.validateIntake(), true);
    assert.equal(Object.keys(chat.errors).length, 0);
});

test("intake reports missing required fields and incomplete phone", () => {
    const chat = intake();
    chat.customer.name = " ";
    chat.customer.phone = "";
    chat.customer.topic = "";
    assert.equal(chat.validateIntake(), false);
    assert.equal(chat.errors.name, "Required");
    assert.equal(chat.errors.phone, "Incomplete");
    assert.equal(chat.errors.topic, "Required");
    assert.equal(chat.errors.email, undefined);
});

test("intake reports invalid phone and optional email on blur, clearing corrected errors", () => {
    const chat = intake();
    chat.customer.phone = "9999999999";
    chat.validateField("phone");
    assert.equal(chat.errors.phone, "Invalid phone");
    chat.customer.phone = "0912345678";
    chat.validateField("phone");
    assert.equal(chat.errors.phone, undefined);
    chat.customer.email = "invalid-email";
    chat.validateField("email");
    assert.equal(chat.errors.email, "Invalid email");
    chat.customer.email = "";
    chat.validateField("email");
    assert.equal(chat.errors.email, undefined);
});
