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
</div>

@endsection
