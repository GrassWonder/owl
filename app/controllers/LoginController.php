<?php
class LoginController extends BaseController {

    public function __construct(){
    }

    // ログインフォームの表示
    public function getIndex(){
        if (Sentry::check()) {
            return Redirect::to('/');
        }
        return View::make('login/index');
    }

    public function postIndex(){
        try {
            // フォームからemailとpaswordの連想配列を取得
            $login = Input::only('username','password');
            $user = Sentry::authenticate($login, Input::get('remember'));
            Session::put("user", $user);
            return Redirect::to('/'); 
        }catch (Cartalyst\Sentry\Users\LoginRequiredException $e) {
            return Redirect::back()
                ->withErrors(array('warning' => 'ユーザ名とパスワードを入力してください。'))
                ->withInput();
        } catch (Cartalyst\Sentry\Users\PasswordRequiredException $e) {
            return Redirect::back()
                ->withErrors(array('warning' => 'パスワードを入力してください。'))
                ->withInput();
        } catch (Cartalyst\Sentry\Users\UserNotActivatedException $e) {
            return Redirect::back()
                ->withErrors(array('warning' => 'アクティベートされていません。'))
                ->withInput();
        } catch (Cartalyst\Sentry\Users\UserNotFoundException $e) {
            return Redirect::back()
                ->withErrors(array('warning' => 'ユーザ名又はパスワードが正しくありません'))
                ->withInput();
        }
    }
}
