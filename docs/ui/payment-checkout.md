# Payment checkout exit

The active web payment preview is a locked checkout. The header and original footer remain visible but inert; the support widget is omitted,
payment method buttons are disabled, and secondary detail controls are replaced with read-only information.
BookingManagement's shared CSS styles the header, route heading and Back control.

Back opens the existing `FutaConfirm` dialog. Declining retains the hold. Accepting sets `confirm_exit=1` and submits the CSRF-protected POST cancellation.
The server requires this accepted value and the matching session preview. The Back button remains disabled until its confirmation handler is ready.
Browser Back invokes the same confirmation through a guarded history entry. Browser-controlled navigation or closing a tab cannot be prohibited by a website;
the native leave warning applies, and the existing ten-minute hold timeout remains the recovery mechanism. Reloading does not cancel the hold.

`PaymentHoldLifecycle` locks the payment intent for cancellation and expiry. Only pending intents become expired.
Both round-trip legs use that one intent, so both holds are released when it expires. No seat ID, issued ticket or collected payment is deleted.
Paid and needs-review intents are retained and show their terminal pages. Transfers arriving after exit remain subject to the existing reconciliation flow.
The countdown uses the stored intent deadline; polling cannot overwrite a concurrently completed payment with expired status.

PHP tests cover consent, reload, both-leg release, late transfer and completed-payment protection.
JavaScript tests cover accepting/declining confirmation, Browser Back and terminal redirects. These tests do not certify browser layout or real bank settlement.
No migration or new environment key is required for this flow.

After a verified payment, the same checkout renders its completed state behind a native modal dialog.
Polling and the exit cancellation guard are disabled for that completed checkout.
Ticket cards wrap and remain centered: one card in the middle, two or three alongside when space permits,
and four in two rows on desktop. The modal scrolls vertically on shorter screens.
The close icon and Escape return to the homepage; neither modifies a paid booking.
Reloading the payment URL reconstructs the modal from the persisted intent and issued tickets.
