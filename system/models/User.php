<?php
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'sys_users';

    public static function _info(){

        if(!isset($_SESSION['uid'])){
            echo 'You have logged out. <a href="'.U.'login/">Click Here to Login.</a>';
            exit;
        }

        $id = $_SESSION['uid'];

       // $d = ORM::for_table('sys_users')->find($id);



        return User::find($id);
    }

    public static function asArray()
    {
        return User::all()->keyBy('id')->toArray();
    }

    public static function admin()
    {
        if(isset($_SESSION['uid']))
        {
            return self::find($_SESSION['uid']);
        }
        return false;
    }

    public function login()
    {
        if($this->id)
        {
            $_SESSION['uid'] = $this->id;
            $_SESSION['language'] = $this->language;

            setcookie('ib_at', $this->autologin, time() + 86400 * 180, "/");

        }
    }

}
