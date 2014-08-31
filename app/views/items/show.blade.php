@extends('layouts.master')

@section ('addJs')
    {{HTML::script("/js/stock.change.js")}}
    {{HTML::script("/js/like.change.js")}}
    {{HTML::script("/js/comment.create.js")}}
@stop

@section('title')
    {{{$item->title}}} | Owl
@stop

@section('navbar-menu')
    @include('layouts.navbar-menu')
@stop

@section('contents-pagehead')
<div class="row">
    <div class="col-md-9">
    <div class="media">
        <a class="pull-left" href="#">
        {{ HTML::gravator($item->user->email, 60,'mm','g','true',array('class'=>'media-object')) }}
        </a>
        <div class="media-body">
            <p class="item-title">{{{ $item->title }}}</p>
            <p><a href="/{{{$item->user->username}}}" class="username">{{{$item->user->username}}}</a>さんが<?php echo date('Y/m/d', strtotime($item->updated_at)); ?>に投稿しました。</p>

            @if(isset($User) && $item->user->id == $User->id)
                {{Form::open(['route'=>['items.destroy', $item->open_item_id], 'method'=>'DELETE'])}}
                {{link_to_route('items.edit','編集',$item->open_item_id)}} 
                <a onclick="confirm('本当に削除しますか？'); if(ok) this.parentNode.submit();return false;" href="void()">削除</a>
                {{Form::close()}}
            @endif
        </div>
    </div>
    </div>
    <div class="col-md-3">
        @if(isset($User))
            @if (count($stock) > 0)
            <div class="media-sidebar">
                <a href="javascript:void(0)" class="btn btn-default btn-block" id="unstock_id">ストックを解除する</a>
                <input type="hidden" value="{{{ $item->open_item_id }}}" id='open_id' />
            </div>
            @else
            <div class="media-sidebar">
                <a href="javascript:void(0)" class="btn btn-success btn-block" id="stock_id">この記事をストックする</a>
                <input type="hidden" value="{{{ $item->open_item_id }}}" id='open_id' />
            </div>
            @endif
        @endif
    </div>
</div>
@stop


@section('contents-main')
<?php if ($item->published === '0') : ?>
    <div class="alert alert-warning" role="alert">この記事は非公開設定です。投稿者本人のみアクセスできます。</div>
<?php elseif ($item->published === '1') : ?>
    <div class="alert alert-warning" role="alert">この記事は限定公開です。URLを知っている人のみアクセスすることができます。</div>
<?php endif; ?>

<div class="page-body">{{ $item->body }}</div>

@if (isset($User))
    @if (count($like) > 0)
    <div class="like-area">
        <div class="like-area-button">
            <a href="javascript:void(0)" id="unlike_id" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-thumbs-up"></span> いいね！を取り消す</a><span id="like_count">{{ count($like_users->like) }}</span>人がいいね！と言っています。
        </div>
        <div class="like-area-icon">
            @foreach ($like_users->like as $like_user)
                {{ HTML::gravator($like_user->user->email, 20,'mm','g','true',array('class'=>'media-object', 'title' => $like_user->user->username)) }}
            @endforeach
        </div>
    </div>
    @else
    <div class="like-area">
        <div class="like-area-button">
            <a href="javascript:void(0)" id="like_id" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-thumbs-up"></span> いいね！</a><span id="like_count">{{ count($like_users->like) }}</span>人がいいね！と言っています。
        </div>
        <div class="like-area-icon">
            @foreach ($like_users->like as $like_user)
                {{ HTML::gravator($like_user->user->email, 20,'mm','g','true',array('class'=>'media-object', 'title' => $like_user->user->username)) }}
            @endforeach
        </div>
    </div>
    @endif
@endif
<div style='clear:both;'></div>

@if(isset($User))
    <div id="comment-container">
    <hr>
    @if (count($item->comment) >0)
        @foreach ($item->comment as $comment)
            @include('comment.body')
        @endforeach
    @endif
    </div>

    @include('comment.form')
@endif
@stop

@section('contents-sidebar')
    @include('layouts.items-show-sidebar')
@stop
