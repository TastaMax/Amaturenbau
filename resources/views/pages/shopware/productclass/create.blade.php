@extends('layouts.app')

@section('content')

    @include('partials.navigation', [
        'items' => [
            [
                'name' => 'Übersicht',
                'url' => '/shopware/produktklasse',
                'active' => 0
            ],
            [
                'name' => 'Erstellen',
                'url' => '/shopware/produktklasse/erstellen',
                'active' => 1
            ]
        ]
    ])

    <section class="my-5" id="category">
        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>Produktklasse</h1>
                </div>
            </div>
        </div>
        <div class="container mb-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="card px-5 py-5">
                        <div class="card-header">
                            <h2>Neue Produktklasse erstellen</h2>
                        </div>
                        <form action="{{ route('swCategoryCreate') }}" method="POST">
                            <div class="card-body">
                                @csrf
                                <div class="row">
                                    <h3>Allgemein</h3>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="title_de" name="title_de" required/>
                                            <label for="title_de" class="form-label">Titel Deutsch @include('partials.required') </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="title_en" name="title_en"/>
                                            <label for="title_en" class="form-label">Titel Englisch</label>
                                        </div>
                                    </div>
                                    <hr>
                                    <h3>SEO</h3>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="meta_title" name="meta_title"/>
                                            <label for="meta_title" class="form-label">Meta Titel</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="meta_description" name="meta_description" />
                                            <label for="meta_description" class="form-label">Meta Beschreibung</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" />
                                            <label for="meta_keywords" class="form-label">Meta Keywords (Mit Leerzeichen trennen)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary float-end">Speichern</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
