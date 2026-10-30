@include('StaffTicketProcessing::partials.exchange-selection')
@include('StaffTicketProcessing::partials.exchange-confirmation')
<section x-show="step === 4 && flow.result" x-cloak x-ref="exchangeSuccess" x-html="flow.result?.html || ''"></section>
