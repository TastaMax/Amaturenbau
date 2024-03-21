@extends('layouts.app')

@section('content')

    <section class="my-5" id="category">
        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>FAQ</h1>
                </div>
                <hr>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body mt-3 mb-2">
                            <h4>
                                <i class="fas fa-square color-blue me-2 mb-4"></i>Synchronisierung zu ShopWare 6
                            </h4>
                            <p class="mb-4">
                                Die Synchronisierung erfolgt nur zu ShopWare 6. <br>
                                Das heißt, wenn Änderungen manuell in ShopWare 6 angepasst werden, werden diese hier nicht synchronisiert.<br><br>
                                Bitte beachten Sie, dass Änderungen nur hier vorgenommen werden sollten.<br><br>
                                Weitere Informationen zum Status der Synchronisierung finden Sie in den FAQ zum Tabellenfeld Synchronisierung.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body mt-3 mb-2">
                            <h4>
                                <i class="fas fa-square color-blue me-2 mb-4"></i>Kategorien
                            </h4>
                            <p class="mb-4">
                                Im Bereich Kategorien, können Sie alle Ihre Kategorien sowie Unterkategorien sehen, erstellen und bearbeiten.<br>
                                Unterkategorien können Sie nur erstellen oder bearbeiten, wenn Sie auf eine Kategorie gehen.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body mt-3 mb-2">
                            <h4>
                                <i class="fas fa-square color-blue me-2 mb-4"></i>Tabellenfeld Synchronisierung
                            </h4>
                            <p class="mb-4">
                                Das Feld Sync zeigt Ihnen den aktuellen Status der Synchronisierung an.
                                <ul class="list-unstyled">
                                    <li class="mb-1"><i class="fa-solid fa-check"></i> Zeigt an das der Eintrag synchronisiert ist.</li>
                                    <li class="mb-1"><i class="fa-solid fa-clock"></i> Zeigt an das der Eintrag noch synchronisiert werden muss. Das passiert, wenn ein Eintrag erstellt oder bearbeitet wurde.</li>
                                    <li class="mb-1"><i class="fa-solid fa-trash"></i> Zeigt an das der Eintrag gelöscht wird.</li>
                                </ul>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body mt-3 mb-2">
                            <h4>
                                <i class="fas fa-square color-blue me-2 mb-4"></i>Sync Protokollierungen
                            </h4>
                            <p class="mb-4">
                                Wenn bei der Synchronisation ein Problem aufgetreten ist, wird dies direkt im Dashboard angezeigt.<br><br>
                                Unten rechts im Dashboard finden Sie Meldungen, die Sie öffnen können. Hier können Sie detaillierte Informationen über den Vorgang erhalten.<br><br>
                                Wenn der Statuscode ungleich 200 ist, war die Synchronisation in den meisten Fällen fehlerhaft.<br><br>
                                Hier finden Sie eine allgemeine Liste der <a target="_blank" href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status">Statuscodes</a>.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body mt-3 mb-2">
                            <h4>
                                <i class="fas fa-square color-blue me-2 mb-4"></i>System Konfigurationen
                            </h4>
                            <p class="mb-4">
                                Einstellungen, die das System betreffen, können in der Datei .env vorgenommen werden. <br><br>Hier können Dinge wie Datenbank, Name usw. geändert werden.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
