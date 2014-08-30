<?php

class CommentController extends BaseController{

    private $_status = 400;

    public function create(){
        $item = Item::where('open_item_id',Input::get('open_item_id'))->first();
        $user = Sentry::getUser();
        if(preg_match("/^[\s　\t\r\n]*$/s", Input::get('body') || !$user || !$item)) return "";
        $comment = new Comment;
        $comment->item_id = $item->id;
        $comment->user_id = $user->id; 
        $comment->body = Input::get('body');
        $comment->save();
        $comment->user->username = $user->username;
        $comment->user->email = $user->email;
        return View::make('comment.body', compact('comment'));
    }

    public function update(){
        if(!$comment = $this->getComment()){
            return  Response::make("", $this->_status);
        }
        $comment->body = Input::get('body');
        $comment->save();

        $user = sentry::finduserbyid($comment->user_id);
        $comment->user->username = $user->username;
        $comment->user->email = $user->email;
        return View::make('comment.body', compact('comment'));

    }

    public function destroy(){
        if($comment = $this->getComment()){
            $comment->delete();
            $this->_status = 200;
        }
        return  Response::make("", $this->_status);
    }

    private function getComment(){
        $user = Sentry::getUser();
        $comment = Comment::find(Input::get('id'));
        if($user->id === $comment->user_id)
            return $comment;
        return false;
    }


}
