# Customer landing background

Home and trip-search results use `core::landing-background`. It renders the original orange PNG as a background with a Laravel-generated asset URL.
Layout sizing remains in Core's `home-layout.css`. There is no fallback artwork or authentication-dependent background.

## Cause of the missing image

The original stylesheet used `/images/banners/Abstract Orange Gradient Waves.png`. When the browser loads that stylesheet from Vite on port 5173,
the CSS image URL resolves against port 5173. The installed Laravel Vite plugin defaults `publicDir` to `false`, so that server does not serve this public image.
The same image is served by the application on port 8000. Other displayed images use `asset(...)` in HTML and therefore request the application URL.
Reloading or navigating can expose this difference between the dev stylesheet and built assets. No recorded browser/network trace from the earlier working state exists.

Vite also returned an old CSS transform after the source had changed inside its container. Windows bind-mount events were not reliably invalidating it.
The Docker Vite service enables `VITE_USE_POLLING`; Vite excludes vendor, storage and build output from polling. A temporary source marker was observed in the dev response without restarting Vite.

The first repair covered only home. Trip-search results shared the CSS class but missed the image URL, leaving the search navigation broken.
The shared component now prevents that omission. Use it for any future page that needs the landing artwork.

## Verification

The application image endpoint returned 200 and the old Vite image endpoint returned 404 during diagnosis.
Both live home and search responses now contain the application image URL. Feature tests cover guest, authenticated, repeated search, logout and returning home.
These HTTP and feature checks do not certify rendered browser pixels; the browser connection was unavailable in this session.
