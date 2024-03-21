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
                </div>
            </div>
        </div>

        <!-- Container for demo purpose -->
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div id="ecommerce-gallery" class="ecommerce-gallery">
                                <div class="row">
                                    @if(is_array($pictures))
                                        <div class="col-12 mb-4">
                                            <div class="lightbox shadow-4 rounded-5">
                                                <img
                                                    src="https://mdbootstrap.com/img/Photos/new-design-blocks/ecommerce/29.jpg"
                                                    alt="Backpack 1"
                                                    class="ecommerce-gallery-main-img active w-100 h-auto"/>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <img src="https://mdbootstrap.com/img/Photos/new-design-blocks/ecommerce/29.jpg"
                                                 data-mdb-img="https://mdbootstrap.com/img/Photos/new-design-blocks/ecommerce/29.jpg"
                                                 alt="Backpack 1" class="active w-100 shadow-4 rounded-5"/>
                                        </div>
                                        <div class="col-4">
                                            <img src="https://mdbootstrap.com/img/Photos/new-templates/img28.jpg"
                                                 data-mdb-img="https://mdbootstrap.com/img/Photos/new-templates/img28.jpg"
                                                 alt="Backpack 2" class="w-100 shadow-4 rounded-5"/>
                                        </div>
                                        <div class="col-4">
                                            <img src="https://mdbootstrap.com/img/Photos/new-templates/img27.jpg"
                                                 data-mdb-img="https://mdbootstrap.com/img/Photos/new-templates/img27.jpg"
                                                 alt="Backpack 3" class="w-100 shadow-4 rounded-5"/>
                                        </div>
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

                <div class="col-md-8">
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
                                            <label for="title_en" class="form-label">Titel Englisch</label>
                                        </div>
                                    </div>
                                    <hr>
                                    <h3>Produkttext</h3>
                                    <!-- Pills navs -->
                                    <ul class="nav nav-pills mb-3" id="ex1" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a
                                                data-mdb-pill-init
                                                class="nav-link active"
                                                id="ex1-tab-1"
                                                href="#ex1-pills-1"
                                                role="tab"
                                                aria-controls="ex1-pills-1"
                                                aria-selected="true"
                                            >Deutsch</a
                                            >
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a
                                                data-mdb-pill-init
                                                class="nav-link"
                                                id="ex1-tab-2"
                                                href="#ex1-pills-2"
                                                role="tab"
                                                aria-controls="ex1-pills-2"
                                                aria-selected="false"
                                            >Englisch</a
                                            >
                                        </li>
                                    </ul>
                                    <!-- Pills navs -->

                                    <!-- Pills content -->
                                    <div class="tab-content" id="ex1-content">
                                        <div
                                            class="tab-pane fade show active"
                                            id="ex1-pills-1"
                                            role="tabpanel"
                                            aria-labelledby="ex1-tab-1"
                                        >
                                            <div class="container">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="wysiwyg" data-mdb-wysiwyg-init>
                                                            <br/>
                                                            <p style="text-align: center;"><img src="https://mdbootstrap.com/wp-content/uploads/2018/06/logo-mdb-jquery-small.webp" class="img-fluid"></p>
                                                            <h1 style="text-align: center;">MDBootstrap</h1>
                                                            <p style="text-align: center;">WYSIWYG Editor</p>
                                                            <p style="text-align: center;"><a href="https://mdbootstrap.com" target="_blank" contenteditable="false" style="font-size: 1rem; text-align: left;">MDBootstrap.com</a>&nbsp;© 2020</p>
                                                            <p style="text-align: left;"><b>Features:</b></p>
                                                            <ul>
                                                                <li>Changing block type</li>
                                                                <li>Text formatting (bold, italic, strikethrough, underline)</li>
                                                                <li>Setting text color</li>
                                                                <li>Setting color highlight</li>
                                                                <li>Text aligning</li>
                                                                <li>Creating a list (bulled or numbered)</li>
                                                                <li>Increase/Decrease indent</li>
                                                                <li>Inserting links</li>
                                                                <li>Inserting pictures</li>
                                                                <li>Insert horizontal line</li>
                                                                <li>show HTML code</li>
                                                                <li>Undo/Redo</li>
                                                            </ul>
                                                            <p><b>Options:</b></p>
                                                            <ul>
                                                                <li>Translations</li>
                                                                <li>Using your own color palette</li>
                                                                <li>Disabling/enabling sections</li>
                                                            </ul>
                                                            <p><b>Methods:</b></p>
                                                            <ul>
                                                                <li>Get HTML code from editor</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="ex1-pills-2" role="tabpanel" aria-labelledby="ex1-tab-2">
                                            <div class="container">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="wysiwyg" data-mdb-wysiwyg-init>
                                                            <br/>
                                                            <p style="text-align: center;"><img src="https://mdbootstrap.com/wp-content/uploads/2018/06/logo-mdb-jquery-small.webp" class="img-fluid"></p>
                                                            <h1 style="text-align: center;">MDBootstrap</h1>
                                                            <p style="text-align: center;">WYSIWYG Editor</p>
                                                            <p style="text-align: center;"><a href="https://mdbootstrap.com" target="_blank" contenteditable="false" style="font-size: 1rem; text-align: left;">MDBootstrap.com</a>&nbsp;© 2020</p>
                                                            <p style="text-align: left;"><b>Features:</b></p>
                                                            <ul>
                                                                <li>Changing block type</li>
                                                                <li>Text formatting (bold, italic, strikethrough, underline)</li>
                                                                <li>Setting text color</li>
                                                                <li>Setting color highlight</li>
                                                                <li>Text aligning</li>
                                                                <li>Creating a list (bulled or numbered)</li>
                                                                <li>Increase/Decrease indent</li>
                                                                <li>Inserting links</li>
                                                                <li>Inserting pictures</li>
                                                                <li>Insert horizontal line</li>
                                                                <li>show HTML code</li>
                                                                <li>Undo/Redo</li>
                                                            </ul>
                                                            <p><b>Options:</b></p>
                                                            <ul>
                                                                <li>Translations</li>
                                                                <li>Using your own color palette</li>
                                                                <li>Disabling/enabling sections</li>
                                                            </ul>
                                                            <p><b>Methods:</b></p>
                                                            <ul>
                                                                <li>Get HTML code from editor</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Pills content -->
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

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success float-end mx-1">Speichern</button>
                                    <a href="/shopware/produktklasse/delete/{{ $productclass->id }}"
                                       class="btn btn-danger float-end mx-1">Löschen</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- Container for demo purpose -->

    </section>

@endsection
