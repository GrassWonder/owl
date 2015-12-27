<?php

/**
 * @copyright (c) owl
 */
namespace Owl\Handlers\Events;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Contracts\Mail\Mailer;
use Owl\Events\Item\CommentEvent;
use Owl\Events\Item\GoodEvent;
use Owl\Repositories\ItemRepositoryInterface as ItemRepository;
use Owl\Repositories\UserRepositoryInterface as UserRepository;

/**
 * Class EmailNotification
 *
 * @package Owl\Handlers\Events
 */
class EmailNotification {

    /** @var Mailer */
    protected $mail;

    /** @var ItemRepository */
    protected $item;

    /** @var UserRepository */
    protected $user;

    /**
     * @param Mailer          $mailer
     * @param ItemRepository  $itemRepository
     * @param UserRepository  $userRepository
     */
    public function __construct(
        Mailer         $mailer,
        ItemRepository $itemRepository,
        UserRepository $userRepository
    ) {
        $this->mail = $mailer;
        $this->item = $itemRepository;
        $this->user = $userRepository;
    }

    /**
     * 記事にコメントがついた時のメール通知
     *
     * @param CommentEvent  $event
     */
    public function onGetComment(CommentEvent $event)
    {
        $item      = $this->item->getByOpenItemId($event->getId());
        $recipient = $this->user->getById($item->user_id);
        $sender    = $this->user->getById($event->getUserId());

        $data = [
            'recipient' => $recipient->username,
            'sender'    => $sender->username,
            'itemId'    => $item->open_item_id,
            'itemTitle' => $item->title,
            'comment'   => $event->getComment(),
        ];
        $this->mail->send(
            'emails.action.comment', $data,
            function ($m) use ($recipient, $sender) {
                $m->to($recipient->email)
                    ->subject($sender->username.'さんからコメントがつきました - Owl');
            }
        );
    }

    /**
     * 記事にいいね！がついた時
     *
     * @param GoodEvent  $event
     */
    public function onGetGood(GoodEvent $event)
    {
        // TODO: いいねメール送信
    }

    /**
     * 記事がストックされた時
     *
     * @param mixed $event
     */
    public function onGetStock($event)
    {
        // TODO: ストックメール送信
    }

    /**
     * 記事が編集された時
     *
     * @param mixed $event
     */
    public function onItemEdited($event)
    {
        // TODO: 記事編集通知送信
    }

    /**
     * 各イベントにハンドラーメソッドを登録
     *
     * @param \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $subscriberName = '\Owl\Handlers\Events\EmailNotification';

        $events->listen(CommentEvent::class, $subscriberName.'@onGetComment');
        $events->listen(GoodEvent::class,    $subscriberName.'@onGetGood');
        $events->listen('event.item.stock',  $subscriberName.'@onGetStock');
        $events->listen('event.item.edit',   $subscriberName.'@onItemEdited');
    }
}
