@extends('layouts.app')

@section('content')
    <section class="my-5" id="dashboard">
        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>Hallo, {{ auth()->user()->name }}</h1>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h2>Status</h2>
                                    <p>Aktueller Status der Dienste</p>
                                </div>
                                <div class="col-md-4 d-flex align-items-center justify-content-center mb-3">
                                    <div class="empty-circle d-flex align-items-center justify-content-center" style="border-color: rgb(246,246,246);" id="status-indicator"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
                                </div>

                                <ul id="systemstatus" class="list-group"></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2>Informationen</h2>
                            <p>
                                PHP Version {{ $informations['phpversion'] }} <br>
                                Laravel Version {{ $informations['laravelversion'] }} <br>
                                OS Version {{ $informations['osversion'] }} <br>
                                Letztes Update {{ $informations['lastupdate'] }} <br>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body overflow-auto schedule">
                            <h2>Schedule <span class="float-end"><i class="fa-solid fa-rotate fa-spin"></i></span></h2>

                            <ul id="services" class="list-group list-group-light list-group-small">
                                <tr id="placeholderList" class="placeholder-glow">
                                    <td><span class="placeholder w-75"></span></td>
                                    <td><span class="placeholder w-50"></span></td>
                                    <td><span class="placeholder w-75"></span></td>
                                    <td><span class="placeholder w-50"></span></td>
                                </tr>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container mt-1">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <div class="card">
                        <div class="card-header p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h2>Logs</h2>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <select class="" data-mdb-select-init="true" id="selectLog">
                                        <option value="laravel">Laravel</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <a class="btn btn-primary float-end" href="/log/storage/clear/laravel" id="logClearButton">Log-Einträge
                                        löschen</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" data-mdb-perfect-scrollbar="true"
                                 style="position: relative; height: 400px">
                                <table class="table table-hover table-borderless table-striped text-nowrap">
                                    <thead>
                                    <tr>
                                        <th scope="col">Datum</th>
                                        <td>Schweregrad</td>
                                        <td>Ereignis</td>
                                    </tr>
                                    </thead>
                                    <tbody id="log-entries">
                                    <tr id="placeholder" class="placeholder-glow">
                                        <td><span class="placeholder col-5"></span></td>
                                        <td><span class="placeholder w-25"></span></td>
                                        <td><span class="placeholder w-75"></span></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2>Meldungen <a href="{{ url('/notifications') }}" class="float-end"><i class="fa-solid fa-sm fa-arrow-up-right-from-square"></i></a></h2>
                            <ul id="logs" class="list-group list-group-light list-group-small">
                                <tr id="placeholderList" class="placeholder-glow">
                                    <td><span class="placeholder w-100"></span></td>
                                    <td><span class="placeholder w-100"></span></td>
                                    <td><span class="placeholder w-100"></span></td>
                                    <td><span class="placeholder w-100"></span></td>
                                    <td><span class="placeholder w-100"></span></td>
                                    <td><span class="placeholder w-100"></span></td>
                                </tr>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
