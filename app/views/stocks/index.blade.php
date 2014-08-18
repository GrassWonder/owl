@extends('layouts.master')

@section('title')
ストック一覧 | Owl
@stop

@section('navbar-menu')
    @include('layouts.navbar-menu')
@stop

@section('contents-pagehead')
<p class="page-title">ストック一覧</p>
@stop

@section('contents-main')

<div class="page-header">
    <h5>最近のストック</h5>
</div>

<div class="stocks">
    @foreach ($stocks as $stock)
    <div class="stock">
        {{ HTML::gravator($stock->email, 40) }}
        <p><a href="/{{{$stock->username}}}" class="username">{{{$stock->username}}}</a>さんが<?php echo date('Y/m/d', strtotime($stock->updated_at)); ?>に投稿しました。</p>
        <p><a href="{{ action('ItemController@show', $stock->open_item_id) }}"><strong>{{{ $stock->title }}}</strong></a></p>
    </div>
    @endforeach
</div>
@stop

@section('contents-sidebar')
    @include('layouts.contents-sidebar')
@stop
