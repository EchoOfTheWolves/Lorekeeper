@extends('shops.layout')

@section('shops-title') Shop Index @endsection

@section('shops-content')
{!! breadcrumbs(['Shops' => 'shops']) !!}

<h1>
    Shops
</h1>

<div class="row shops-row">
    @foreach($shops as $shop)
        @if($shop->visible_only == 1)
            <div class="col-md-3 col-6 mb-3 text-center collectionnotunlocked">
                <div class="shop-image">
                    <a href="{{ $shop->url }}"><img src="{{ $shop->shopImageUrl }}" alt="{{ $shop->name }}" /></a>
                </div>
                <div class="shop-name mt-1">
                    <a href="{{ $shop->url }}" class="h5 mb-0">{{ $shop->name }} <i class="fas fa-eye" data-toggle="tooltip" title="View-only"></i> </a> 
                </div>
            </div>
        @else
            <div class="col-md-3 col-6 mb-3 text-center">
                <div class="shop-image">
                    <a href="{{ $shop->url }}"><img src="{{ $shop->shopImageUrl }}" alt="{{ $shop->name }}" /></a>
                </div>
                <div class="shop-name mt-1">
                    <a href="{{ $shop->url }}" class="h5 mb-0">{{ $shop->name }}</a>
                </div>
            </div>
        @endif
    @endforeach
@if($shops->count())
@foreach($shops as $categoryId=>$categoryshops)
<div class="col-md-12">
    <div class="card mb-2 text-center">
        <div class="card-header d-flex flex-wrap no-gutters">
            <h1 class="col-12">
                {!! isset($shopcategories[$categoryId]) ? ''.'<img src="'.$shopcategories[$categoryId]->categoryImageUrl.'" style="margin-right: 10px">'.'' : ' ' !!} {!! isset($shopcategories[$categoryId]) ? ''.$shopcategories[$categoryId]->name.'' : 'Miscellaneous' !!} {!! isset($shopcategories[$categoryId]) ? ''.'<img src="'.$shopcategories[$categoryId]->categoryImageUrl.'" style="margin-left: 10px">'.'' : ' ' !!}
            </h1>
            <div class="col-12 text-center">
                {!! isset($shopcategories[$categoryId]) ? ''.$shopcategories[$categoryId]->description.'' : ' ' !!}

            </div>
        </div>
    </div>
</div>

        <div class="card-body" id="{!! isset($shopcategories[$categoryId]) ? str_replace(' ', '', $shopcategories[$categoryId]->name) : 'miscellaneous' !!}">
            @foreach($categoryshops->chunk(4) as $chunk)
                <div class="row mb-3">
                    @foreach($chunk as $shopId=>$shop)
                        <div class="col-md-3 col-6 mb-3 text-center">
                            <div class="shop-image">
                                <a href="{{ $shop->url }}"><img src="{{ $shop->shopImageUrl }}" alt="{{ $shop->name }}" /></a>
                            </div>
                            <div class="shop-name mt-1">
                                <a href="{{ $shop->url }}" class="h5 mb-0">{{ $shop->name }}</a>
                            </div>
                         </div>
                    @endforeach
                </div>
            @endforeach
        </div>
@endforeach
@endif
</div>

@endsection

