<?php

class ItemController extends BaseController{

    public function create($templateId = null){
        if(!Input::get('t')) {
            return View::make('items.create', compact('template'));
        }

        $templateId = Input::get('t');
        $template = Template::where('id',$templateId)->first();
        return View::make('items.create', compact('template'));
    }

    public function store(){
        // $B%P%j%G!<%7%g%s%k!<%k$N:n@.(B
        $valid_rule = array(
            'title' => 'required|max:255',
            'body' => 'required',
            'published' => 'required|numeric'
        );
        $validator = Validator::make(Input::all(), $valid_rule);

        // $B<:GT$N>l9g(B
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $user = Sentry::getUser();
        $item = new Item;
        $item->fill(array(
            'user_id'=>$user->id,
            'open_item_id' => Item::createOpenItemId(),
            'title'=>Input::get('title'),
            'body'=>str_replace('<', '&lt;', Input::get('body')),
            'published'=>Input::get('published')
        ));
        $item->save();
        return Redirect::to('/'); 
    }

    public function index(){
        $items = Item::with('user')
                    ->where('published', '2')
                    ->orderBy('id','desc')
                    ->paginate(10);
        $templates = Template::all();
        return View::make('items.index', compact('items', 'templates'));
    }

    public function show($openItemId){
        $user = Sentry::getUser();

        $item = Item::with('comment.user')->where('open_item_id',$openItemId)->first();

        if ($item->published === '0' && $item->user_id !== $user->id){
            App::abort(404);
        }

        // Markdown Parse
        $parser = new CustomMarkdown;
        $parser->enableNewlines = true;
        $item->body = $parser->parse($item->body);

        $templates = Template::all();

        $stock = Stock::whereRaw('user_id = ? and item_id = ?', array($user->id, $item->id))
                      ->get();

        $like = Like::whereRaw('user_id = ? and item_id = ?', array($user->id, $item->id))
                      ->get();

        return View::make('items.show', compact('item', 'templates', 'stock', 'like'));
    }

    public function edit($openItemId){
        $user = Sentry::getUser();
        $item = Item::where('open_item_id',$openItemId)->first();

        if ($item->user_id !== $user->id){
            App::abort(404);
        }

        if ($item == null){
            App::abort(404);
        }
        $templates = Template::all();
        return View::make('items.edit', compact('item', 'templates'));
    }

    public function update($openItemId){
        // $B%P%j%G!<%7%g%s%k!<%k$N:n@.(B
        $valid_rule = array(
            'title' => 'required|max:255',
            'body' => 'required',
            'published' => 'required|numeric'
        );
        $validator = Validator::make(Input::all(), $valid_rule);

        // $B<:GT$N>l9g(B
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $user = Sentry::getUser();
        $item = Item::where('open_item_id',$openItemId)->first();;

        if ($item->user_id !== $user->id){
            App::abort(404);
        }

        if ($item == null){
            App::abort(404);
        }
        $item->fill(array(
            'user_id'=>$user->id,
            'title'=>Input::get('title'),
            'body'=>str_replace('<', '&lt;', Input::get('body')),
            'published'=>Input::get('published')
        ));
        $item->save();
        return Redirect::route('items.show',[$openItemId]);
    }

    public function destroy($openItemId){
        $user = Sentry::getUser();
        $item = Item::where('open_item_id',$openItemId)->first();;

        if ($item->user_id !== $user->id){
            App::abort(404);
        }

        if ($item == null){
            App::abort(404);
        }
        Item::where('open_item_id',$openItemId)->delete();
        return Redirect::route('items.index');
    }

}
