@extends('layouts.app')

@section('content')

    @include('partials.navigation', [
        'items' => [
            [
                'name' => 'Übersicht',
                'url' => '/shopware/unterkategorie',
                'active' => 0
            ],
            [
                'name' => 'Erstellen',
                'url' => '/shopware/unterkategorie/erstellen',
                'active' => 0
            ]
        ]
    ])

    <section class="my-5" id="category">
        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>Unterkategorie {{ $subcategory->id }}</h1>
                </div>
            </div>
        </div>
        <div class="container mb-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="card px-5 py-5">
                        <div class="card-header">
                            <h2>
                                {{ $subcategory->title }}

                                <a href="/shopware/unterkategorie/" class="btn btn-primary float-end m-1">Übersicht</a>
                                <a href="/shopware/kategorie/editieren/{{ $category->id }}" class="btn btn-primary float-end m-1">Kategorie</a>
                            </h2>
                        </div>
                        <form action="{{ route('swSubCategoryEdit') }}" method="POST">
                            <div class="card-body">
                                @csrf
                                <div class="row">
                                    <h3>Allgemein</h3>
                                    <input type="hidden" id="id" name="id" value="{{ $subcategory->id }}"/>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="title_de" name="title_de" value="{{ $subcategory->title }}" required/>
                                            <label for="title_de" class="form-label">Titel Deutsch @include('partials.required') </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="title_en" name="title_en" value="{{ $subcategory->title_en }}"/>
                                            <label for="title_en" class="form-label">Titel Englisch</label>
                                        </div>
                                    </div>
                                    <hr>
                                    <h3>Zuweisung</h3>
                                    <div class="col-md-12 mb-3">
                                        <select name="category" data-mdb-select-init>
                                            @foreach($selectCategory as $item)
                                                <option value="{{$item->id}}" @if($item->id == $category->id) selected @endif>{{$item->title}}</option>
                                            @endforeach
                                        </select>
                                        <label class="form-label select-label">Kategorie</label>
                                    </div>

                                    @if( config('app.app_seo') )
                                        <hr>
                                        <h3>SEO</h3>
                                        <div class="col-md-12">
                                            <div class="form-outline mb-3" data-mdb-input-init>
                                                <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ $subcategory->meta_title }}"/>
                                                <label for="meta_title" class="form-label">Meta Titel</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-outline mb-3" data-mdb-input-init>
                                                <input type="text" class="form-control" id="meta_description" name="meta_description" value="{{ $subcategory->meta_description }}" />
                                                <label for="meta_description" class="form-label">Meta Beschreibung</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-outline mb-3" data-mdb-input-init>
                                                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ $subcategory->meta_keywords }}" />
                                                <label for="meta_keywords" class="form-label">Meta Beschreibung</label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success float-end mx-1">Speichern</button>
                                <a href="/shopware/unterkategorie/delete/{{ $subcategory->id }}" class="btn btn-danger float-end mx-1">Löschen</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
