@extends('layouts.app')

@section('content')

    <style>
        .modal {
            overflow: hidden;
            /* Weitere Modal-Stile */
        }
        .text-element {
            max-height: 100%; /* Beispielwert, passen Sie dies an Ihre Anforderungen an */
            overflow-y: auto; /* Scrollbalken bei Bedarf anzeigen */
            /* Weitere Textelement-Stile */
        }
        .modal {
            position: fixed; /* oder absolute, je nach Bedarf */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-height: 80vh; /* Beispielwert, passen Sie dies an Ihre Anforderungen an */
            overflow-y: auto; /* Scrollbalken bei Bedarf anzeigen */
            /* Weitere Modal-Stile */
        }

    </style>
    <section class="my-5">
        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>Meldungen</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="container mb-3">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="my-3">Übersicht</h2>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <select id="addSelectGroup" name="addSelectGroup">
                                            <option value="">Keine Auswahl</option>
                                        @foreach($systems as $system)
                                            <option value="{{ $system }}">{{ $system }}</option>
                                        @endforeach

                                    </select>
                                    <label class="form-label select-label" for="addSelectGroup">Filter</label>
                                </div>
                            </div>
                            <div class="col-md-8"></div>
                            <div class="col-md-8 col-sm-12">
                                <div class="form-outline mb-4" data-mdb-input-init="true">
                                    <input type="text" class="form-control" id="datatable-search-input" />
                                    <label class="form-label" for="datatable-search-input">Suche</label>
                                </div>
                            </div>
                            <div class="col-md-4"></div>
                        </div>

                    </div>
                    <div class="card-body p-0">
                        <div id="notificationsDatatable" data-mdb-loading="true" data-mdb-datatable-init="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="logModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logModalLabel">Meldung #<span id="LogNumber"></span></h5>
                    <button type="button" class="btn-close" data-mdb-ripple-init data-mdb-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h2><span id="system">Laden...</span><span id="importance" class="float-end" data-mdb-tooltip-init title="Laden...">Laden...</span></h2>
                            <p>Zeitstempel <span id="LogDate"></span></p>
                        </div>
                        <div class="col-md-12">
                            <p id="notification">Systemmeldung Laden...</p>
                        </div>
                    </div>

                    <!-- Hier werden die Daten angezeigt -->
                    <div class="border border-2 px-2 pt-2">
                        <h5>Debug <button class="btn btn-outline-primary float-end" id="notificationJsonFormatter">Json Formatter</button></h5>

                        <p class="text-element w-100" id="logDetails">Laden...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-ripple-init data-mdb-dismiss="modal">Schließen</button>
                </div>
            </div>
        </div>
    </div>

@endsection
