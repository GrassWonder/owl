@section('contents-sidebar')
@if(isset($User))
    <a href="/items/create" class="btn btn-success btn-block">記事を投稿する</a>
    <div class="panel panel-default">
        <div class="panel-heading">テンプレートから作成 <a href="/templates">[編集]</a></div>
        <ul class="list-group">
            @foreach ($templates as $template)
            <li class="list-group-item">
                <a href="/items/create?t={{$template->id}}">{{{$template->display_title}}}</a>
            </li>
            @endforeach
        </ul>
    </div>

    @if(!empty($ranking_stock))
        <h4>総合ストック数ランキング</h4>
        <div class="sidebar-info-items">
            <ol>
            @for ($i = 0; $i < count($ranking_stock); $i++)
                <li><a href="{{ action('ItemController@show', $ranking_stock[$i]->open_item_id) }}">{{{ $ranking_stock[$i] -> title }}}</a></li>
            @endfor
            </ol>
        </div>
    @endif
@endif
@stop
