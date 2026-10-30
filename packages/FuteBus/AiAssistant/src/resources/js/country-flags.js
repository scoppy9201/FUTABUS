const flags = import.meta.glob("/node_modules/flag-icons/flags/4x3/*.svg", {
    eager: true,
    query: "?url",
    import: "default",
});

export function countryFlag(code) {
    return (
        flags[`/node_modules/flag-icons/flags/4x3/${code.toLowerCase()}.svg`] ||
        ""
    );
}
