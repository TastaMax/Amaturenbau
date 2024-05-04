@extends('layouts.app')

@section('content')

    @include('partials.navigation', [
        'items' => [
            [
                'name' => 'Übersicht',
                'url' => '/shopware/kategorie',
                'active' => 1
            ],
            [
                'name' => 'Erstellen',
                'url' => '/shopware/kategorie/erstellen',
                'active' => 0
            ]
        ]
    ])

    <section class="my-5" id="category">
        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>Unterkategorien</h1>
                </div>
            </div>
        </div>
        <div class="container mb-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="my-3">Übersicht</h2>
                            <div class="row">
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
                            <div id="subCategorysDatatable" data-mdb-loading="true" data-mdb-datatable-init="true"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
