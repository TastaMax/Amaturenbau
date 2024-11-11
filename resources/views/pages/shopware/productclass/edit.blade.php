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
                'active' => 0
            ]
        ]
    ])

    <section class="my-5" id="category">
        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>Produktklasse {{ $productclass->id }}</h1>
                    <h2>Rubrik {{ $productclass->rubrik }}</h2>
                </div>
            </div>
        </div>

        <!-- Container for demo purpose -->
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body p-0">
                            <div id="ecommerce-gallery" class="ecommerce-gallery">
                                <div class="row">
                                    @if($hasPictures)
                                        @foreach($pictures as $key => $picture)
                                            @if($key === 1)
                                                <div class="col-12 mb-4">
                                                    <div class="lightbox shadow-4 rounded-5">
                                                        <img
                                                            src="http://data.shop.berndarmaturenbau.de.dedivirt3120.your-server.de/pictures/{{ $picture->file }}"
                                                            alt="Bild {{ $key + 1 }}"
                                                            class="ecommerce-gallery-main-img active w-100 h-auto"/>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-12">
                                                <img src="http://data.shop.berndarmaturenbau.de.dedivirt3120.your-server.de/pictures/{{ $picture->file }}"
                                                     data-mdb-img="http://data.shop.berndarmaturenbau.de.dedivirt3120.your-server.de/pictures/{{ $picture->file }}"
                                                     alt="Bild {{ $key + 1 }}"
                                                     class="{{ $key == 0 ? 'active' : '' }} w-100 shadow-4 rounded-5"/>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-12 mb-4">
                                            <div class="lightbox shadow-4 rounded-5">
                                                <img
                                                    src="{{ Vite::asset('resources/images/partials/picturenotfound.png') }}"
                                                    alt="no picture"
                                                    class="ecommerce-gallery-main-img active w-100 h-auto"/>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <img src="{{ Vite::asset('resources/images/partials/picturenotfound.png') }}"
                                                 data-mdb-img="{{ Vite::asset('resources/images/partials/picturenotfound.png') }}"
                                                 alt="no picture" class="active w-100 shadow-4 rounded-5"/>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="fw-bold mb-3">{{ $productclass->title }}</h2>
                            <form action="{{ route('swCategoryEdit') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <h3>Allgemein</h3>
                                    <input type="hidden" id="id" name="id" value="{{ $productclass->id }}"/>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="title_de" name="title_de"
                                                   value="{{ $productclass->title }}" required/>
                                            <label for="title_de" class="form-label">Titel
                                                Deutsch @include('partials.required') </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="title_en" name="title_en"
                                                   value="{{ $productclass->title_en }}"/>
                                            <label for="title_en" class="form-label">Titel Englisch @include('partials.required')</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="productnumber" name="productnumber"
                                                   value="{{ $productclass->productnumber }}" data-mdb-showcounter="true" maxlength="64" required/>
                                            <label for="productnumber" class="form-label">Produktnummer (max 64 Zeichen)
                                                @include('partials.required') </label>
                                            <div class="form-helper"></div>
                                        </div>
                                    </div>
                                    @if( config('app.app_seo') )
                                    <hr>
                                    <h3>SEO</h3>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="meta_title" name="meta_title"
                                                   value="{{ $productclass->meta_title }}"/>
                                            <label for="meta_title" class="form-label">Meta Titel</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="meta_description"
                                                   name="meta_description"
                                                   value="{{ $productclass->meta_description }}"/>
                                            <label for="meta_description" class="form-label">Meta Beschreibung</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="meta_keywords"
                                                   name="meta_keywords" value="{{ $productclass->meta_keywords }}"/>
                                            <label for="meta_keywords" class="form-label">Meta Beschreibung</label>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="card-footer mt-3">
                                    <button type="submit" class="btn btn-success float-end mx-1">Speichern</button>
                                    <a href="/shopware/produktklasse/delete/{{ $productclass->id }}"
                                       class="btn btn-danger float-end mx-1">Löschen</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h3>Produkttext</h3>
                        <ul class="nav nav-pills" id="ex1" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a
                                    data-mdb-pill-init
                                    class="nav-link active"
                                    id="ex1-tab-1"
                                    href="#ex1-pills-1"
                                    role="tab"
                                    aria-controls="ex1-pills-1"
                                    aria-selected="true">Deutsch</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a
                                    data-mdb-pill-init
                                    class="nav-link"
                                    id="ex1-tab-2"
                                    href="#ex1-pills-2"
                                    role="tab"
                                    aria-controls="ex1-pills-2"
                                    aria-selected="false">Englisch</a>
                            </li>
                        </ul>
                        <div class="tab-content mb-3" id="ex1-content">
                            <div
                                class="tab-pane fade show active"
                                id="ex1-pills-1"
                                role="tabpanel"
                                aria-labelledby="ex1-tab-1">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="wysiwygGerman border border-1" id="wysiwygGerman">
                                                {{ $productclass->description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="ex1-pills-2" role="tabpanel" aria-labelledby="ex1-tab-2">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="wysiwygEnglish border border-1" id="wysiwygEnglish">
                                                {{ $productclass->description_en }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
