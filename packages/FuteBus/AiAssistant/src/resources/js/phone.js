import {
    getCountries,
    getCountryCallingCode,
    parsePhoneNumberFromString,
} from "libphonenumber-js/max";

export function phoneCountries(locale) {
    const names = new Intl.DisplayNames([locale], { type: "region" });
    const englishNames = new Intl.DisplayNames(["en"], { type: "region" });
    return getCountries()
        .map((code) => ({
            code,
            name: names.of(code),
            englishName: englishNames.of(code),
            prefix: `+${getCountryCallingCode(code)}`,
        }))
        .sort((a, b) => a.name.localeCompare(b.name, locale));
}

export function parseCustomerPhone(value, country) {
    if (!value.trim() || !getCountries().includes(country)) return null;
    const phone = parsePhoneNumberFromString(value, {
        defaultCountry: country,
        extract: false,
    });
    return phone?.isValid() && phone.country === country && !phone.ext
        ? phone
        : null;
}
