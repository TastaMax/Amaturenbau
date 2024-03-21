@if (Session::has('success') || Session::has('error'))
    <div
        data-mdb-alert-init
        class="alert fade alert-dismissible text-white @if(Session::has('success')) bg-success @elseif(Session::has('error')) bg-danger @endif"
        role="alert"
        id="alert"
        data-mdb-color="@if(Session::has('success')) success @elseif(Session::has('error')) danger @endif"
        data-mdb-position="top-right"
        data-mdb-stacking="true"
        data-mdb-width="535px"
        data-mdb-append-to-body="true"
        data-mdb-hidden="true"
        data-mdb-autohide="true"
        @if(Session::has('success'))  data-mdb-delay="6000" @elseif(Session::has('error'))  data-mdb-delay="120000" @endif
    >
@if(Session::has('success')) <i class="fas fa-check-circle me-3 text-white"></i> @elseif(Session::has('error')) <i class="fas fa-times-circle me-3 text-white"></i> @endif
{{ Session::get('success') }}
{{ Session::get('error') }}
<button
    type="button"
    class="btn-close"
    data-mdb-dismiss="alert"
    aria-label="Schließen"
></button>
</div>
@endif
@if(Session::has('success'))
    @php
        session()->forget('success');
    @endphp
@elseif(Session::has('error'))
    @php
        session()->forget('error');
    @endphp
@endif
