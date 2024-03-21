@extends('layouts.app')

@section('content')

    @include('partials.navigation', [
        'items' => [
            [
                'name' => 'Übersicht',
                'url' => '/shopware/kategorie',
                'active' => 0
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
                    <h1>Kategorie {{ $category->id }}</h1>
                </div>
            </div>
        </div>
        <div class="container mb-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="card px-5 py-5">
                        <div class="card-header">
                            <h2>
                                {{ $category->title }}
                                <a href="/shopware/kategorie/" class="btn btn-primary float-end">Zurück</a>
                            </h2>
                        </div>
                        <form action="{{ route('swCategoryEdit') }}" method="POST">
                            <div class="card-body">
                                @csrf
                                <div class="row">
                                    <h3>Allgemein</h3>
                                    <input type="hidden" id="id" name="id" value="{{ $category->id }}"/>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="title_de" name="title_de" value="{{ $category->title }}" required/>
                                            <label for="title_de" class="form-label">Titel Deutsch @include('partials.required') </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="title_en" name="title_en" value="{{ $category->title_en }}"/>
                                            <label for="title_en" class="form-label">Titel Englisch</label>
                                        </div>
                                    </div>
                                    <hr>
                                    <h3>SEO</h3>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ $category->meta_title }}"/>
                                            <label for="meta_title" class="form-label">Meta Titel</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="meta_description" name="meta_description" value="{{ $category->meta_description }}" />
                                            <label for="meta_description" class="form-label">Meta Beschreibung</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ $category->meta_keywords }}" />
                                            <label for="meta_keywords" class="form-label">Meta Beschreibung</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success float-end mx-1">Speichern</button>
                                <a href="/shopware/kategorie/delete/{{ $category->id }}" class="btn btn-danger float-end mx-1">Löschen</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>Unterkategorien</h1>
                    <p>Überarbeiten Sie hier die Unterkategorien der Kategorie <b>{{ $category->title }}</b>.</p>
                </div>
            </div>
        </div>
        <div class="container mb-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="card pt-5">
                        <div class="card-header">
                            <h2>
                                Übersicht
                                <a href="/shopware/kategorie/" class="btn btn-primary float-end">Erstellen</a>
                            </h2>
                            <div class="row">
                                <div class="col-md-8 col-sm-12">
                                    <div class="form-outline mb-4" data-mdb-input-init="true">
                                        <input type="text" class="form-control" id="datatable-search-input-subcategory" />
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
