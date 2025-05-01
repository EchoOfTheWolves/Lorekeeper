@extends('shops.layout')

@section('shops-title')
    Shop Index
@endsection

@section('shops-content')
    {!! breadcrumbs(['Shops' => 'shops']) !!}

    <h1>
        Shops
    </h1>

    <div class="row shops-row">
        @if ($shops->count())
            @foreach ($shops as $categoryId => $categoryshops)
                <div class="col-md-12">
                    <div class="card mb-2 text-center">
                        <div class="card-header d-flex flex-wrap no-gutters">
                            <h1 class="col-12">
                                {!! isset($shopcategories[$categoryId]) ? '' . '<img src="' . $shopcategories[$categoryId]->categoryImageUrl . '" style="margin-right: 10px">' . '' : ' ' !!} {!! isset($shopcategories[$categoryId]) ? '' . $shopcategories[$categoryId]->name . '' : 'Miscellaneous' !!} {!! isset($shopcategories[$categoryId]) ? '' . '<img src="' . $shopcategories[$categoryId]->categoryImageUrl . '" style="margin-left: 10px">' . '' : ' ' !!}
                            </h1>
                            <div class="col-12 text-center">
                                {!! isset($shopcategories[$categoryId]) ? '' . $shopcategories[$categoryId]->description . '' : ' ' !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body" id="{!! isset($shopcategories[$categoryId]) ? str_replace(' ', '', $shopcategories[$categoryId]->name) : 'miscellaneous' !!}">
                    @foreach ($categoryshops->chunk(4) as $chunk)
                        <div class="row mb-3">
                            @foreach ($chunk as $shopId => $shop)
                                @if ($shop->visible_only == 1)
                                    <div class="col-md-3 col-6 mb-3 text-center collectionnotunlocked">
                                        @if ($shop->is_staff)
                                            @if (Auth::check() && Auth::user()->isstaff)
                                                @include('shops._shop')
                                            @endif
                                        @else
                                            @include('shops._shop')
                                        @endif
                                    </div>
                                @else
                                    <div class="col-md-3 col-6 mb-3 text-center">
                                        @if ($shop->is_staff)
                                            @if (Auth::check() && Auth::user()->isstaff)
                                                @include('shops._shop')
                                            @endif
                                        @else
                                            @include('shops._shop')
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
@endsection
