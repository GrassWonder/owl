<?php namespace Owl\Http\Controllers;

use Owl\Services\UserService;
use Owl\Models\Item;
use Owl\Models\ItemHistory;
use Owl\Models\Tag;
use Owl\Repositories\LikeRepositoryInterface;
use Owl\Repositories\StockRepositoryInterface;
use Owl\Repositories\TemplateRepositoryInterface;

class ItemController extends Controller
{
    protected $userService;
    protected $likeRepo;
    protected $stockRepo;
    protected $templateRepo;

    public function __construct(
        UserService $userService,
        LikeRepositoryInterface $likeRepo,
        StockRepositoryInterface $stockRepo,
        TemplateRepositoryInterface $templateRepo
    ) {
        $this->userService = $userService;
        $this->likeRepo = $likeRepo;
        $this->stockRepo = $stockRepo;
        $this->templateRepo = $templateRepo;
    }

    public function create($templateId = null)
    {
        $user = $this->userService->getCurrentUser();
        $user_items = Item::getRecentItemsByUserId($user->id);
        $template = null;
        if (\Input::get('t')) {
            $templateId = \Input::get('t');
            $template = $this->templateRepo->getById($templateId);
        }
        return \View::make('items.create', compact('template', 'user_items'));
    }

    public function store()
    {
        $valid_rule = array(
            'title' => 'required|max:255',
            'tags' => 'alpha_comma|max:64',
            'body' => 'required',
            'published' => 'required|numeric'
        );
        $validator = \Validator::make(\Input::all(), $valid_rule);
        if ($validator->fails()) {
            return \Redirect::back()->withErrors($validator)->withInput();
        }

        $user = $this->userService->getCurrentUser();
        $openItemId = Item::createOpenItemId();
        $item = new Item;
        $item->fill(array(
            'user_id' => $user->id,
            'open_item_id' => $openItemId,
            'title' => \Input::get('title'),
            'body' => \Input::get('body'),
            'published' => \Input::get('published')
        ));
        $item->save();

        $result = ItemHistory::insertHistory($item, $user);

        $tags = \Input::get('tags');
        if (!empty($tags)) {
            $tag_names = explode(",", $tags);
            $tag_ids = Tag::getTagIdsByTagNames($tag_names);
            $item = Item::find($item->id);
            $item->tag()->sync($tag_ids);
        }

        return \Redirect::route('items.show', [$openItemId]);
    }

    public function index()
    {
        $items = Item::getAllItems();
        $templates = $this->templateRepo->getAll();
        return \View::make('items.index', compact('items', 'templates'));
    }

    public function show($openItemId)
    {
        $item = Item::with('comment.user')->where('open_item_id', $openItemId)->first();
        if (empty($item)) {
            \App::abort(404);
        }

        $user = $this->userService->getCurrentUser();
        if ($item->published === "0") {
            if (empty($user)) {
                \App::abort(404);
            } elseif ($item->user_id !== $user->id) {
                \App::abort(404);
            }
        }

        $stock = null;
        $like = null;
        if (!empty($user)) {
            $stock = $this->stockRepo->getByUserIdAndItemId($user->id, $item->id);
            $like = $this->likeRepo->get($user->id, $item->id);
        }
        $stocks = $this->stockRepo->getByItemId($item->id);
        $recent_stocks = $this->stockRepo->getRecentRankingWithCache(5, 7);
        $user_items = Item::getRecentItemsByUserId($item->user_id);
        $like_users = Item::with('like.user')->where('id', $item->id)->first();
        return \View::make('items.show', compact('item', 'user_items', 'stock', 'like', 'like_users', 'stocks', 'recent_stocks'));
    }

    public function edit($openItemId)
    {
        $user = $this->userService->getCurrentUser();
        $item = Item::where('open_item_id', $openItemId)->first();
        if ($item === null) {
            \App::abort(404);
        }

        $templates = $this->templateRepo->getAll();
        $user_items = Item::getRecentItemsByUserId($user->id);
        return \View::make('items.edit', compact('item', 'templates', 'user_items'));
    }

    public function update($openItemId)
    {
        $valid_rule = array(
            'title' => 'required|max:255',
            'tags' => 'alpha_comma|max:64',
            'body' => 'required',
            'published' => 'required|numeric'
        );
        $validator = \Validator::make(\Input::all(), $valid_rule);
        if ($validator->fails()) {
            return \Redirect::back()->withErrors($validator)->withInput();
        }

        $user = $this->userService->getCurrentUser();
        $item = Item::where('open_item_id', $openItemId)->first();
        if ($item == null) {
            \App::abort(404);
        }

        $user_id = $user->id;
        if ($item->user_id !== $user->id) {
            $user_id = $item->user_id;
        }

        $item->fill(array(
            'user_id' => $user_id,
            'title' => \Input::get('title'),
            'body' => \Input::get('body'),
            'published' => \Input::get('published')
        ));
        $item->save();

        $result = ItemHistory::insertHistory($item, $user);

        $tags = \Input::get('tags');
        if (!empty($tags)) {
            $tag_names = explode(",", $tags);
            $tag_ids = Tag::getTagIdsByTagNames($tag_names);
            $item = Item::find($item->id);
            $item->tag()->sync($tag_ids);
        }

        return \Redirect::route('items.show', [$openItemId]);
    }

    public function destroy($openItemId)
    {
        $user = $this->userService->getCurrentUser();
        $item = Item::where('open_item_id', $openItemId)->first();
        if ($item == null || $item->user_id !== $user->id) {
            \App::abort(404);
        }
        Item::where('open_item_id', $openItemId)->delete();
        $no_tag = array();
        $item->tag()->sync($no_tag);

        return \Redirect::route('items.index');
    }

    public function history($openItemId)
    {
        $histories = ItemHistory::with('user')
            ->where('open_item_id', $openItemId)
            ->orderBy('updated_at', 'DESC')
            ->get();

        return \View::make('items.history', compact('histories'));
    }
}
