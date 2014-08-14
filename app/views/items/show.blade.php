@extends('layouts.master')

@section ('addJs')
{{HTML::script("/js/stock.change.js")}}$
@stop

@section('title')
{{{$item->title}}} | Athena
@stop

@section('navbar-menu')
    @include('layouts.navbar-menu')
@stop

@section('contents-pagehead')

<div class="media">
    <a class="pull-left" href="#">
    {{ HTML::gravator($item->user->email, 60,'mm','g','true',array('class'=>'media-object')) }}
    </a>
    <div class="media-body">
        <p class="page-title">{{{ $item->title }}}</p>
        <p><a href="/{{{$item->user->username}}}" class="username">{{{$item->user->username}}}</a>さんが<?php echo date('Y/m/d', strtotime($item->updated_at)); ?>に投稿しました。</p>

        <?php if ($item->user->id == $User->id) : ?>
        {{Form::open(['route'=>['items.destroy', $item->open_item_id], 'method'=>'DELETE'])}}
        {{link_to_route('items.edit','編集',$item->open_item_id)}} 
        <a onclick="confirm('本当に削除しますか？'); this.parentNode.submit();return false;" href="void()">削除</a>
        {{Form::close()}}
        <?php endif; ?>
    </div>


    @if (count($stock) > 0)
    <div class="media-sidebar">
        <a href="javascript:void(0)" class="btn btn-success btn-block" id="unstock_id">ストックを解除する</a>
        <input type="hidden" value="{{{ $item->open_item_id }}}" id='open_id' />
    </div>
    @else
    <div class="media-sidebar">
        <a href="javascript:void(0)" class="btn btn-success btn-block" id="stock_id">この記事をストックする</a>
        <input type="hidden" value="{{{ $item->open_item_id }}}" id='open_id' />
    </div>
    @endif

</div>
@stop


@section('contents-main')
<p class="page-body">{{ $item->body }}</p>
@stop

@section('contents-sidebar')
    @include('layouts.contents-sidebar')
@stop
