<?php namespace Owl\Http\Controllers;

use Owl\Services\ItemService;
use Owl\Repositories\StockRepositoryInterface;
use Owl\Repositories\TemplateRepositoryInterface;

class IndexController extends Controller
{
    protected $itemService;
    protected $stockRepo;
    protected $templateRepo;

    public function __construct(
        ItemService $itemService,
        StockRepositoryInterface $stockRepo,
        TemplateRepositoryInterface $templateRepo
    ) {
        $this->itemService = $itemService;
        $this->stockRepo = $stockRepo;
        $this->templateRepo = $templateRepo;
    }

    public function index()
    {
        $kind = (\Input::get('kind')) ?: 'stock';

        if ($kind === 'all') {
            $items = $this->itemService->getAllPublished();
        } elseif ($kind === 'flow') {
            $items = $this->itemService->getAllFlowPublished();
        } else {
            $kind = 'stock';
            $items = $this->itemService->getAllStockPublished();
        }

        $templates = $this->templateRepo->getAll();
        $ranking_stock = $this->stockRepo->getRankingWithCache(5);
        return \View::make('index.index', compact('kind', 'items', 'templates', 'ranking_stock'));
    }
}
