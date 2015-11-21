<?php namespace Owl\Http\Controllers;

use Owl\Services\UserService;
use Owl\Services\TagService;
use Owl\Services\ItemService;
use Owl\Services\LikeService;
use Owl\Services\StockService;
use Owl\Repositories\TemplateRepositoryInterface;
use Owl\Http\Requests\ItemStoreRequest;
use Owl\Http\Requests\ItemUpdateRequest;

class ItemController extends Controller
{
    protected $userService;
    protected $tagService;
    protected $itemService;
    protected $likeService;
    protected $stockService;
    protected $templateRepo;

    public function __construct(
        UserService $userService,
        TagService $tagService,
        ItemService $itemService,
        LikeService $likeService,
        StockService $stockService,
        TemplateRepositoryInterface $templateRepo
    ) {
        $this->userService = $userService;
        $this->tagService = $tagService;
        $this->itemService = $itemService;
        $this->likeService = $likeService;
        $this->stockService = $stockService;
        $this->templateRepo = $templateRepo;
    }

    public function create($templateId = null)
    {
        $user = $this->userService->getCurrentUser();
        $user_items = $this->itemService->getRecentsByUserId($user->id);
        $template = null;
        if (\Input::get('t')) {
            $templateId = \Input::get('t');
            $template = $this->templateRepo->getById($templateId);
        }
        return \View::make('items.create', compact('template', 'user_items'));
    }

    public function store(ItemStoreRequest $request)
    {
        $user = $this->userService->getCurrentUser();

        $object = app('stdClass');
        $object->user_id = $user->id;
        $object->open_item_id = $this->itemService->createOpenItemId();
        $object->title = \Input::get('title');
        $object->body = \Input::get('body');
        $object->published = \Input::get('published');
        $item = $this->itemService->create($object);

        $result = $this->itemService->createHistory($item, $user);

        $tags = \Input::get('tags');
        if (!empty($tags)) {
            $tag_names = explode(",", $tags);
            $tag_ids = $this->tagService->getTagIdsByTagNames($tag_names);
            $item = $this->itemService->getById($item->id);
            $item->tag()->sync($tag_ids);
        }

        return \Redirect::route('items.show', [$item->open_item_id]);
    }

    public function index()
    {
        $items = $this->itemService->getAllPublished();
        $templates = $this->templateRepo->getAll();
        return \View::make('items.index', compact('items', 'templates'));
    }

    public function show($openItemId)
    {
        $item = $this->itemService->getByOpenItemIdWithComment($openItemId);
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
            $stock = $this->stockService->getByUserIdAndItemId($user->id, $item->id);
            $like = $this->likeService->get($user->id, $item->id);
        }
        $stocks = $this->stockService->getByItemId($item->id);
        $recent_stocks = $this->stockService->getRecentRankingWithCache(5, 7);
        $user_items = $this->itemService->getRecentsByUserId($item->user_id);
        $like_users = $this->itemService->getLikeUsersById($item->id);
        return \View::make('items.show', compact('item', 'user_items', 'stock', 'like', 'like_users', 'stocks', 'recent_stocks'));
    }

    public function edit($openItemId)
    {
        $user = $this->userService->getCurrentUser();
        $item = $this->itemService->getByOpenItemId($openItemId);
        if ($item === null) {
            \App::abort(404);
        }

        $templates = $this->templateRepo->getAll();
        $user_items = $this->itemService->getRecentsByUserId($user->id);
        return \View::make('items.edit', compact('item', 'templates', 'user_items'));
    }

    public function update(ItemUpdateRequest $request, $openItemId)
    {
        $user = $this->userService->getCurrentUser();
        $item = $this->itemService->getByOpenItemId($openItemId);
        if ($item->updated_at != \Input::get('updated_at')) {
            return \Redirect::back()->with("updated_at", "コンフリクトの可能性があるため更新できませんでした。")->withInput();
        }
        if ($item == null) {
            \App::abort(404);
        }

        $user_id = $user->id;
        if ($item->user_id !== $user->id) {
            $user_id = $item->user_id;
        }

        $object = app('stdClass');
        $object->user_id = $user_id;
        $object->title = \Input::get('title');
        $object->body = \Input::get('body');
        $object->published = \Input::get('published');
        $item = $this->itemService->update($item->id, $object);

        $result = $this->itemService->createHistory($item, $user);

        $tags = \Input::get('tags');
        if (!empty($tags)) {
            $tag_names = explode(",", $tags);
            $tag_ids = $this->tagService->getTagIdsByTagNames($tag_names);
            $item = $this->itemService->getById($item->id);
            $item->tag()->sync($tag_ids);
        }

        return \Redirect::route('items.show', [$openItemId]);
    }

    public function destroy($openItemId)
    {
        $user = $this->userService->getCurrentUser();
        $item = $this->itemService->getByOpenItemId($openItemId);
        if ($item == null || $item->user_id !== $user->id) {
            \App::abort(404);
        }
        $this->itemService->delete($item->id);
        $no_tag = array();
        $item->tag()->sync($no_tag);

        return \Redirect::route('items.index');
    }

    public function history($openItemId)
    {
        $histories = $this->itemService->getHistoryByOpenItemId($openItemId);
        return \View::make('items.history', compact('histories'));
    }

    /**
     * POSTされたMarkdownをレンダリングし、json形式でreturn
     *
     * @return Response
     */
    public function parse()
    {
        $parsedMd = '';
        if(\Input::get('md')) {
            $parsedMd= \HTML::markdown(\Input::get('md'));
        }
        return response()->json(['html' => $parsedMd]);
    }
}
